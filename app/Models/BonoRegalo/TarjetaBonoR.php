<?php

namespace App\Models\BonoRegalo;

use Illuminate\Database\Eloquent\Model;

class TarjetaBonoR extends Model
{
    protected $table = 'tarjetasBonoR';

    protected $fillable = [
        'numero',
        'nit',
        'valor',
        'estado',
    ];

    public function itemsFactura()
    {
        return $this->hasMany(ItemFacturaBonoR::class, 'tarjeta_id');
    }

    // Enmascara números de tarjeta (13-19 dígitos) dentro de un texto: 4198190002092768 -> ************2768
    public static function enmascarar(string $texto): string
    {
        return preg_replace_callback('/\d{13,19}/', fn($m) => str_repeat('*', strlen($m[0]) - 4) . substr($m[0], -4), $texto);
    }

    /**
     * Convierte un valor de archivo (CSV/Excel) a número. Devuelve null si no es válido.
     * Acepta: 50000 | 50.000 | 50,000 | $ 50.000,00 | 50,000.00 | 50000.50 | 50000,5
     */
    public static function parsearValor($valor): ?float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }

        $v = preg_replace('/[\s$]/u', '', (string) $valor);
        if ($v === '') {
            return null;
        }

        $tienePunto = str_contains($v, '.');
        $tieneComa = str_contains($v, ',');

        if ($tienePunto && $tieneComa) {
            // El separador que aparece de último es el decimal
            $decimal = strrpos($v, ',') > strrpos($v, '.') ? ',' : '.';
            $miles = $decimal === ',' ? '.' : ',';
            $v = str_replace([$miles, $decimal], ['', '.'], $v);
        } elseif ($tienePunto || $tieneComa) {
            $sep = $tienePunto ? '.' : ',';
            // Separador de miles si se repite o si lo siguen exactamente 3 dígitos (50.000); si no, es decimal
            $esMiles = substr_count($v, $sep) > 1 || preg_match('/\\' . $sep . '\d{3}$/', $v);
            $v = $esMiles ? str_replace($sep, '', $v) : str_replace($sep, '.', $v);
        }

        return is_numeric($v) ? (float) $v : null;
    }

    // Una tarjeta facturada no se puede editar, reactivar ni eliminar
    public function estaVendida(): bool
    {
        return $this->itemsFactura()->exists();
    }
}
