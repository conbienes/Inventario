<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AzureService
{
    public static function getAccessToken()
    {
        try {

            $client = new Client();
            $tenantId = config('services.azure.tenant_id');
            $clientId = config('services.azure.client_id');
            $clientSecret = config('services.azure.client_secret');
            $authority = config('services.azure.authority');
            $scope = config('services.azure.scope');

            $response = $client->post("$authority$tenantId/oauth2/v2.0/token", [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => $scope,
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error("Error obteniendo token de acceso: " . $e->getMessage());
            return null;
        }
    }

    public static function getSharePointSite()
    {
        try {
            $client = new Client();
            $accessToken = self::getAccessToken();

            if (!$accessToken) {
                return ['error' => 'No se pudo obtener el token de acceso'];
            }

            $domain = config('services.sharepoint.domain');
            $siteName = config('services.sharepoint.site_name');

            $siteUrl = "https://graph.microsoft.com/v1.0/sites/$domain:/sites/$siteName";

            $response = $client->get($siteUrl, [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error obteniendo el sitio de SharePoint: " . $e->getMessage());
            return ['error' => 'No se pudo obtener el sitio de SharePoint'];
        }
    }
}
