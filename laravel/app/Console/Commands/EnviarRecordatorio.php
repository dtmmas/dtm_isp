<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Invoice;

use Illuminate\Support\Facades\Http;
use App\Traits\WhatsApp;

class EnviarRecordatorio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EnviarRecordatorio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un recordatorio por WhatsApp al usuario indicando que su factura vence mañana.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {     
        set_time_limit(0);   
        ini_set('memory_limit', '3024M');   
        try {
            $manana = fecha_tomorrow();
            Log::info('EnviarRecordatorio INICIO ', []);
            $facturas = Invoice::with('client_product.client.user')
            ->whereHas('client_product.client.user', function($query){
                $query->where('users.phone','!=','');
            })
            ->where(function($query) use ($manana){
                $query->where('invoices.status','0');
                $query->where('invoices.date_expiration','LIKE',$manana.'%');
            })
            ->orderByDesc('created_at')
            ->get();

            foreach ($facturas as $key => $invoice) {
                $cliente = $invoice->client_product->client->user;
                Log::info('=== EnviarRecordatorio Cliente', [$cliente->name.' '.$cliente->phone]);
                $configWP = whatsappConfig();
                $whatsAppConexion = new WhatsApp($configWP->token, $configWP->number);
                try {
                    $cellphone = normalizeWhatsAppPhone($cliente->phone);
                    $name = $cliente->name;
                    $mesFactura = $invoice->getMesInvoice();
                    $monto = formatWhatsAppCurrency($invoice->price);
                    $message = "Le informamos que su factura de $mesFactura por valor de $monto, vence mañana.\nFavor depositar a esta cuenta BANRURAL 3089024910 a nombre de Adner Miguel.\nENVIAR FOTO DE LA BOLETA AL WATSAP 33024105.\n!!!Evitemos cortes o suspension!!!";
                    //echo "<pre>";print_r($message);echo "</pre><br>";
                    if ($cellphone === '') {
                        Log::warning('EnviarRecordatorio numero invalido', [$cliente->name, $cliente->phone]);
                        continue;
                    }
                    $template = whatsappTemplateConfig('vencimiento', [
                        'name' => $name,
                        'monthList' => $mesFactura,
                        'total_deuda' => $monto,
                    ]);
                    $wp = $template
                        ? $whatsAppConexion->sendTemplateMessage($cellphone, $template['name'], $template['language'], $template['parameters'])
                        : $whatsAppConexion->sendTextMessage($cellphone, $message);
                    if(!$wp['respuesta']){
                        Log::info('EnviarRecordatorio No se envio recordatorio', $wp['error']);
                    } else {
                        Log::info('EnviarRecordatorio aceptado por Meta', [$wp['message_id'], $wp['wa_id']]);
                    }
                    //die('SOLO UNO');
                } catch (Exception $e) {
                    Log::error('EnviarRecordatorio No se envio recordatorio', [$cliente->name.' '.$cliente->phone]);
                }
            }
            
            Log::info('EnviarRecordatorio Total', [count($facturas)]);
            
        } catch (\Throwable $th) {
            Log::error('ERROR EnviarRecordatorio', [$th]);
        }
    }
}
