<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Client;

use Illuminate\Support\Facades\Http;
use App\Traits\FunctionGeneral;
use App\Traits\WhatsApp;

class EnviarRecordatorioMora extends Command
{
    use FunctionGeneral;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EnviarRecordatorioMora';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un recordatorio por WhatsApp al usuario indicando su mora. La idea seria ejecutarlo el 20 de cada mes.';

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
            $clientes = Client::with('client_products','client_products.invoices','user')
            ->whereHas('user', function($query){
                $query->where('users.phone','!=','');
                $query->WhereRaw('(SELECT count(invoices.id) FROM invoices WHERE status="-1" and client_product_id IN (SELECT id FROM client_product WHERE client_id = clients.id)) != ?',['0']);
            })
            ->get();
            $total_envio = 0;
            foreach ($clientes as $key => $client) {
                Log::info('=== EnviarRecordatorioMora Cliente', [$client->user->name.' '.$client->user->phone]);
                $configWP = whatsappConfig();
                $whatsAppConexion = new WhatsApp($configWP->token, $configWP->number);
                try {
                    foreach ($client->client_products as $p => $plan) {
                        if($plan->status!='-1'){
                            $data = $client->getFirtsAndLastMonthDeudaAndPrice($plan->id);
                            if($data["Valor"]>0 && strlen($client->user->phone)>=8){
                                Log::info('EnviarRecordatorioMora ENVIAMOS', []);
                                $cellphone = normalizeWhatsAppPhone($client->user->phone);
                                $message = "Le informamos que tienen facturas pendientes de pago.\nMeses vencidos:   {$data["Meses"]}\nTotal deuda: {$this->formatearNumero($data["Valor"])}\nFavor depositar a esta cuenta BANRURAL 3089024910 a nombre de Adner Miguel.\nSI YA REALIZO EL PAGO FAVOR DE ENVIAR FOTO DE LA BOLETA AL WATSAP 33024105.\n!!!Evitemos cortes o suspension!!!";
                                    //echo "<pre>";print_r($message);echo "</pre><br>";
                                if ($cellphone === '') {
                                    Log::warning('EnviarRecordatorioMora numero invalido', [$client->user->name, $client->user->phone]);
                                    continue;
                                }
                                $template = whatsappTemplateConfig('moroso', [
                                    'name' => $client->user->name,
                                    'monthList' => $data["Meses"],
                                    'total_deuda' => $this->formatearNumero($data["Valor"]),
                                ]);
                                $wp = $template
                                    ? $whatsAppConexion->sendTemplateMessage($cellphone, $template['name'], $template['language'], $template['parameters'])
                                    : $whatsAppConexion->sendTextMessage($cellphone, $message);
                                if(!$wp['respuesta']){
                                    Log::info('EnviarRecordatorioMora No se envio recordatorio', [$wp['error']]);
                                }
                                else{
                                    Log::info('EnviarRecordatorioMora aceptado por Meta', [$wp['message_id'], $wp['wa_id']]);
                                    $total_envio++;
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::error('EnviarRecordatorioMora No se envio recordatorio', [$client->user->name.' '.$client->user->phone]);
                }
            }
            
            Log::info('EnviarRecordatorioMora Total', [$total_envio]);
            echo json_encode(['rps' => true, 'total' => $total_envio]);
            
        } catch (\Throwable $th) {
            Log::error('ERROR EnviarRecordatorioMora', [$th]);
            echo json_encode(['rps' => false, 'msj' => 'Ocurrio un error al intentar enviar los mensajes de WhatsApp']);
        }
    }
}
