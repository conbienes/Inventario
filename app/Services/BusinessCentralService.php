<?php
// app/Services/BusinessCentralService.php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class BusinessCentralService
{
    private Client $http;
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $environment;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 45]);
        $this->tenantId = config('services.azure.tenant_id');
        $this->clientId = config('services.azure.client_id');
        $this->clientSecret = config('services.azure.client_secret');
        $this->environment = config('services.bc.environment');
    }

    public function getAccessToken(): string
    {
        return Cache::remember('bc_token', now()->addMinutes(55), function () {
            $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
            $resp = $this->http->post($url, [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://api.businesscentral.dynamics.com/.default',
                    'grant_type' => 'client_credentials',
                ],
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            return $data['access_token'];
        });
    }

    private function prefix(): string
    {
        // Forma simple con solo environment (oficial para BC Online).
        return "https://api.businesscentral.dynamics.com/v2.0/{$this->environment}/api/v2.0";
    }

    private function headers(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'If-Match' => '*', // útil en PATCH/DELETE
        ];
    }

    private function requestWithRetry(string $method, string $url, array $opts = [], int $retries = 3)
    {
        attempt:
        try {
            return $this->http->request($method, $url, $opts);
        } catch (RequestException $e) {
            $code = $e->getResponse()?->getStatusCode();
            if ($retries > 0 && ($code === 429 || ($code >= 500 && $code <= 599))) {
                $delay = (int) (1000 * pow(2, 3 - $retries)); // backoff exponencial
                usleep($delay * 1000);
                $retries--;
                goto attempt;
            }
            throw $e;
        }
    }

    /** Listar compañías y devolver array PHP */
    public function listCompanies(): array
    {
        $token = $this->getAccessToken();
        $url = $this->prefix() . '/companies';
        $resp = $this->requestWithRetry('GET', $url, ['headers' => $this->headers($token)]);
        return json_decode((string) $resp->getBody(), true)['value'] ?? [];
    }

    /** Resolver companyId por nombre (si existe) */
    public function companyIdByName(string $name): ?string
    {
        foreach ($this->listCompanies() as $c) {
            if (strcasecmp($c['name'] ?? '', $name) === 0) {
                return $c['id'];
            }
        }
        return null;
    }

    /** Crear línea de diario general en un batch dado (ej. 'DEFAULT') */
    public function createGeneralJournalLine(string $companyId, string $batchName, array $payload): array
    {
        $token = $this->getAccessToken();
        $url = $this->prefix() . "/companies({$companyId})/generalJournalBatches('" . rawurlencode($batchName) . "')/generalJournalLines";

        $resp = $this->requestWithRetry('POST', $url, [
            'headers' => $this->headers($token),
            'json' => $payload,
        ]);

        return json_decode((string) $resp->getBody(), true);
    }

    /** (Opcional) Crear cliente estándar */
    public function createCustomer(string $companyId, array $payload): array
    {
        $token = $this->getAccessToken();
        $url = $this->prefix() . "/companies({$companyId})/customers";
        $resp = $this->requestWithRetry('POST', $url, [
            'headers' => $this->headers($token),
            'json' => $payload,
        ]);
        return json_decode((string) $resp->getBody(), true);
    }

    private function customApiBase(string $publisher = 'Administracion', string $group = 'BonoRegalo', string $version = 'v1.0'): string
    {
        // Base: https://api.businesscentral.dynamics.com/v2.0/{Environment}/api/{publisher}/{group}/{version}
        return "https://api.businesscentral.dynamics.com/v2.0/{$this->environment}/api/{$publisher}/{$group}/{$version}";
    }

    /** 1) Crear línea en TU API: page 50244 "Custom Journal API" (EntitySetName = DiariosGenerales) */
    public function createCustomJournalLine(string $companyId, array $payload): array
    {
        $token = $this->getAccessToken();
        $url = rtrim($this->customApiBase(), '/') . "/companies({$companyId})/DiariosGenerales";

        try {
            $resp = $this->requestWithRetry('POST', $url, [
                'headers' => $this->headers($token),
                'json' => $payload,
                'timeout' => 15,
            ]);

            $status = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            $json = $body !== '' ? json_decode($body, true) : null;

            if ($status >= 200 && $status < 300) {
                return [
                    'ok' => true,
                    'status' => $status,
                    'data' => $json,
                    // 'id'   => $json['id'] ?? null, // si tu API lo retorna
                ];
            }

            // No-2XX
            $msg = $json['error']['message'] ?? ($json['message'] ?? $body);
            return [
                'ok' => false,
                'status' => $status,
                'error' => $msg,
                'data' => $json,
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Si hubo respuesta, extrae info
            $resp = $e->getResponse();
            $status = $resp ? $resp->getStatusCode() : 0;
            $body = $resp ? (string) $resp->getBody() : $e->getMessage();
            $json = $resp ? json_decode((string) $resp->getBody(), true) : null;
            $msg = $json['error']['message'] ?? ($json['message'] ?? $body);

            return [
                'ok' => false,
                'status' => $status,
                'error' => $msg,
                'data' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'error' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /** 2) Crear cliente en TU API: page 50246 "Customer API Ext" (EntitySetName = customersExt) */
    public function createCustomerExt(string $companyId, array $payload): array
    {
        $token = $this->getAccessToken();
        $url = $this->customApiBase() . "/companies({$companyId})/customersExt";

        $headers = $this->headers($token);
        unset($headers['If-Match']); // <- clave en POST

        $resp = $this->requestWithRetry('POST', $url, [
            'headers' => $headers,
            'json' => $payload,
        ]);

        return json_decode((string) $resp->getBody(), true);
    }

    /** 3) Crear valor de dimensión en TU API: page 50247 "Dimension Value API" (EntitySetName = dimensionValues) */
    public function createDimensionValue(string $companyId, array $payload): array
    {
        $token = $this->getAccessToken();
        $url = $this->customApiBase() . "/companies({$companyId})/dimensionValues";

        $resp = $this->requestWithRetry('POST', $url, [
            'headers' => $this->headers($token),
            'json' => $payload,
        ]);

        return json_decode((string) $resp->getBody(), true);
    }

    /** (Sugerido) Buscar valor de dimensión para evitar duplicados */
    public function findDimensionValue(string $companyId, string $dimensionCode, string $code): ?array
    {
        $token = $this->getAccessToken();
        $base = $this->customApiBase() . "/companies({$companyId})/dimensionValues";
        $filter = sprintf("DimensionCode eq '%s' and Code eq '%s'", str_replace("'", "''", $dimensionCode), str_replace("'", "''", $code));

        $url = $base . '?$top=1&$filter=' . rawurlencode($filter);

        $resp = $this->requestWithRetry('GET', $url, ['headers' => $this->headers($token)]);
        $data = json_decode((string) $resp->getBody(), true);

        return $data['value'][0] ?? null ?: null;
    }

    /** (Sugerido) Upsert dimensión */
    public function upsertDimensionValue(string $companyId, array $payload): array
    {
        $existing = $this->findDimensionValue($companyId, $payload['DimensionCode'], $payload['Code']);
        if ($existing) {
            // Si quieres actualizar algo, haz PATCH con If-Match del etag (o usa systemId):
            // Aquí lo dejamos en no-op para no romper integridad si ya existe.
            return $existing;
        }
        return $this->createDimensionValue($companyId, $payload);
    }

    // app/Services/BusinessCentralService.php

    public function updateCustomerExt(string $companyId, string $systemId, array $payload): array
    {
        $token = $this->getAccessToken();
        $url = $this->customApiBase() . "/companies({$companyId})/customersExt({$systemId})";
        $headers = $this->headers($token);
        $headers['If-Match'] = '*'; // en PATCH sí

        $resp = $this->requestWithRetry('PATCH', $url, [
            'headers' => $headers,
            'json' => $payload,
        ]);

        // PATCH puede devolver 204 sin body
        return $resp->getStatusCode() === 204 ? ['ok' => true] : json_decode((string) $resp->getBody(), true);
    }
}
