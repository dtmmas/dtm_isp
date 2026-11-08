<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingCycleController extends Controller
{
    public function preview(): JsonResponse
    {
        $this->ensureAdmin();

        return response()->json([
            'fecha_actual' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function generate(): JsonResponse
    {
        $this->ensureAdmin();

        $now = now();
        $dateExpiration = $now->copy()->endOfMonth()->format('Y-m-d 23:59:59');
        $dateExpirationShort = $now->copy()->endOfMonth()->format('Y-m-d');
        $generatedInvoices = 0;

        $this->insertLog('GenerarFacturas', 'Inicio proceso para generar facturas', $now);
        $this->insertLog('marcarFacturasComoVencidas', 'Inicio proceso de vencimiento de facturas', $now);

        DB::table('invoices')
            ->where('date_expiration', '<=', $now->format('Y-m-d H:i:s'))
            ->where('status', '0')
            ->update([
                'status' => '-1',
                'updated_at' => $now,
            ]);

        $this->insertLog('marcarFacturasComoVencidas', 'Se marcaron como vencidas las facturas no pagadas', $now);

        $contracts = DB::table('client_product')
            ->leftJoin('products', 'products.id', '=', 'client_product.product_id')
            ->leftJoin('invoices', function ($join) use ($dateExpiration, $dateExpirationShort) {
                $join->on('client_product.id', '=', 'invoices.client_product_id')
                    ->where(function ($query) use ($dateExpiration, $dateExpirationShort) {
                        $query->where(function ($subQuery) use ($dateExpiration) {
                            $subQuery->where('invoices.date_expiration', '=', $dateExpiration)
                                ->where('invoices.payments_advancement', '=', '0');
                        })->orWhere('client_product.discounted_month', '=', $dateExpirationShort);
                    });
            })
            ->whereNull('invoices.client_product_id')
            ->where('client_product.status', '=', '1')
            ->select([
                'client_product.id',
                'products.price as price',
                'client_product.payments_advancement',
            ])
            ->get();

        foreach ($contracts as $contract) {
            if ((int) $contract->payments_advancement === 0) {
                DB::table('invoices')->insert([
                    'client_product_id' => $contract->id,
                    'price' => $contract->price,
                    'date_expiration' => $dateExpiration,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $generatedInvoices++;
                continue;
            }

            DB::table('client_product')
                ->where('id', $contract->id)
                ->update([
                    'payments_advancement' => max(0, (int) $contract->payments_advancement - 1),
                    'discounted_month' => $dateExpirationShort,
                    'updated_at' => $now,
                ]);

            $this->insertLog(
                'disminuirMesesAdelanto',
                'Se cambio los meses a '.max(0, (int) $contract->payments_advancement - 1).' de client_product '.$contract->id,
                $now
            );
        }

        $monthName = $this->spanishMonthName($now);
        $message = 'Se generaron las facturas para el mes de '.$monthName.' - Total:'.$generatedInvoices;
        $this->insertLog('GenerarFacturas', $message, $now);

        return response()->json([
            'message' => $message,
            'generated' => $generatedInvoices,
            'fecha_actual' => $now->format('Y-m-d H:i:s'),
        ]);
    }

    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('Admin'), 403);
    }

    protected function insertLog(string $type, string $detail, Carbon $timestamp): void
    {
        Log::create([
            'tipo' => $type,
            'detalle' => $detail,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    protected function spanishMonthName(Carbon $date): string
    {
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return $months[(int) $date->format('n')] ?? $date->format('F');
    }
}
