<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Invoice;

use Illuminate\Support\Facades\Http;
use App\Traits\WhatsApp;

class EnviarRecordatorioWP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EnviarRecordatorioWP';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un recordatorio al usuario a whatsapp indicando que su factura vence mañama.';

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
                //$query->where('invoices.date_expiration','LIKE',$manana.'%');
            })
            ->orderByDesc('created_at')
            ->get();

            foreach ($facturas as $key => $invoice) {
                $cliente = $invoice->client_product->client->user;
                Log::info('=== EnviarRecordatorio Cliente', [$cliente->name.' '.$cliente->phone]);
                $configWP = whatsappConfig();
                $whatsappConexion = new WhatsApp($configWP->token, $configWP->number);
                try {
                    $cellphone = '502'.$cliente->phone;
                    $name = $cliente->name;
                    $nro_factura = $invoice->getMesInvoice();
                    $monto = $invoice->price_formatted;
                    $wp = $whatsappConexion->SendMsm($cellphone, $name, $nro_factura, $monto);
                    if(!$wp['respuesta']){
                        Log::info('EnviarRecordatorio No se envio recordatorio', $wp['error']);
                    }
                    
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
