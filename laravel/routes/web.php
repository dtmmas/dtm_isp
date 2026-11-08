<?php

use App\Http\Controllers\PdfController;
use App\Http\Controllers\BillingCycleController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ThermalPrintController;
use Illuminate\Support\Facades\Route;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\Log;
use App\Models\Payment;
use App\Models\User;
use App\Models\Zone;
use App\Models\Invoice;
use App\Support\RoleCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


use App\Traits\Twilio;
use App\Traits\WhatsApp;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('recordatorio/{tipo}', function($tipo){

    // Artisan::call('EnviarRecordatorioMora');
    //Artisan::call('EnviarRecordatorio');
    if($tipo==1){
        $manana = fecha_tomorrow();
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
                echo ('<br>=== Recordatorio Para Cliente'.$cliente->name.' '.$cliente->phone);
                $cellphone = '+502'.$cliente->phone;
                $name = $cliente->name;
                $mesFactura = $invoice->getMesInvoice();
                $monto = $invoice->price_formatted;
                $message = "Le informamos que su factura de $mesFactura por valor de $monto, vence mañana.\nFavor depositar a esta cuenta BANRURAL 3089024910 a nombre de Adner Miguel.\nENVIAR FOTO DE LA BOLETA AL WATSAP 33024105.\n!!!Evitemos cortes, suspension o moras!!!";
                echo "<pre>";print_r($message);echo "</pre><br>";
            }
            echo 'Recordatorio Total'.count($facturas);
    }else{
        $clientes = Client::with('client_products','client_products.invoices','user')
        ->whereHas('user', function($query){
            $query->where('users.phone','!=','');
            $query->WhereRaw('(SELECT count(invoices.id) FROM invoices WHERE status="-1" and client_product_id IN (SELECT id FROM client_product WHERE client_id = clients.id)) != ?',['0']);
        })
        ->get();
        $cont=0;
        foreach ($clientes as $key => $client) {
            echo ('<br>=== Msj Mora para Cliente'.$client->user->name.' '.$client->user->phone);
            foreach ($client->client_products as $p => $plan) {
                if($plan->status!='-1'){
                    $data = $client->getFirtsAndLastMonthDeudaAndPrice($plan->id);
                    if($data["Valor"]>0){$cont++;
                        $cellphone = '+502'.$client->user->phone;
                        $message = "Le informamos que tienen facturas pendientes de pago.\nMeses vencidos:   {$data["Meses"]}\nTotal deuda: {$data["Valor"]}\nFavor depositar a esta cuenta BANRURAL 3089024910 a nombre de Adner Miguel.\nSI YA REALIZO EL PAGO FAVOR DE ENVIAR FOTO DE LA BOLETA AL WATSAP 33024105.\n!!!Evitemos cortes, suspension o moras!!!";
                            echo "<pre>";print_r($message);echo "</pre><br>";
                    }
                }
            }
        }
        echo 'Msj Mora Total'.$cont;
    }
});

Route::get('enviarWhatsAppMora', function(){
    Artisan::call('EnviarRecordatorioMora');
})->name('recordatorioMora');

Route::post('enviarWhatsAppMoraMoroso', function(Request $request) {

    $configWP = whatsappConfig();
    $whatsAppConexion = new WhatsApp($configWP->token, $configWP->number);

    $name = trim((string) $request->input('name'));
    $monthList = $request->input('monthList');
    $total_deuda = formatWhatsAppCurrency($request->input('total_deuda'));
    $cellphone = normalizeWhatsAppPhone($request->input('cellphone'));

    if ($cellphone === '') {
        return response()->json([
            'respuesta' => false,
            'error' => ['message' => 'El numero de WhatsApp no es valido.'],
        ], 422);
    }

    $message = "Hola $name\n\nLe informamos que tiene facturas pendientes de pago.\nMeses vencidos: {$monthList}\nTotal deuda: {$total_deuda}\n\nFavor depositar a esta cuenta BANRURAL 3089024910 a nombre de Adner Miguel.\nSi ya realizo el pago, favor de enviar foto de la boleta al WhatsApp 33024105.\n\nEvitemos cortes, suspension o moras.";
    $template = whatsappTemplateConfig('moroso', compact('name', 'monthList', 'total_deuda'));

    if ($template) {
        return $whatsAppConexion->sendTemplateMessage(
            $cellphone,
            $template['name'],
            $template['language'],
            $template['parameters']
        );
    }

    return $whatsAppConexion->sendTextMessage($cellphone, $message);

})->name('recordatorioMoraMoroso');

Route::post('enviarWhatsAppMoraVencer', function(Request $request) {

    $configWP = whatsappConfig();
    $whatsAppConexion = new WhatsApp($configWP->token, $configWP->number);

    $name = trim((string) $request->input('name'));
    $monthList = $request->input('monthList');
    $total_deuda = formatWhatsAppCurrency($request->input('total_deuda'));
    $cellphone = normalizeWhatsAppPhone($request->input('cellphone'));
    $vencimiento = $request->input('vencimiento');

    if ($cellphone === '') {
        return response()->json([
            'respuesta' => false,
            'error' => ['message' => 'El numero de WhatsApp no es valido.'],
        ], 422);
    }

    $message = "Hola $name\n\nLe informamos que su factura de $monthList por valor de $total_deuda esta proxima a vencer, realizar el pago para mantenerte al dia.\n\nPuede depositar a cuenta BANRURAL 3089024910 a nombre de ADNER MIGUEL.\n\nEnviar foto de boleta con el nombre de quien esta registrado el servicio al 33024105.\n\nO realizar el pago en oficina central Jacaltenango.\n\nSi ya realizo el pago favor de reenviar una foto de boleta.\n\nEvitemos cortes suspensiones y moras.\n\nGRACIAS.\nATT. DTM Mas, Internet Residencial Jacaltenango.";
    $template = whatsappTemplateConfig('vencimiento', compact('name', 'monthList', 'total_deuda'));

    if ($template) {
        return $whatsAppConexion->sendTemplateMessage(
            $cellphone,
            $template['name'],
            $template['language'],
            $template['parameters']
        );
    }

    return $whatsAppConexion->sendTextMessage($cellphone, $message);

})->name('recordatorioMoraVencer');

Route::get('/esp', function () {
    $u = App\Models\User::all();
    foreach ($u as $key => $user) {
        $user->name = trim($user->name);
        $user->dni = trim($user->dni);
        $user->lastname = trim($user->lastname);
        $user->update();
    }
});

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {

    if(Auth::user()->hasRole('Cliente') || Auth::user()->hasRole('Cobrador')){
        return view('invoices.index');
    }
    $list_logs = Log::latest('id')->paginate(40);
    $payments =new Payment;
    $total_deposito = $payments->totalPayment('1');
    $total_tarjeta = $payments->totalPayment('2');
    $total_efectivo = $payments->totalPayment('3');
    $total_clientes = Client::count();
    $total_pagos_pendientes = $payments->totalPayment('0');
    $planes_activos_por_zona = Zone::query()
    ->leftJoin('nodes', 'nodes.zone_id', '=', 'zones.id')
    ->leftJoin('client_product', function ($join) {
        $join->on('client_product.node_id', '=', 'nodes.id')
            ->where('client_product.status', '=', '1');
    })
    ->select('zones.id', 'zones.name')
    ->selectRaw('COUNT(DISTINCT client_product.id) as total_planes_activos')
    ->groupBy('zones.id', 'zones.name')
    ->orderByDesc('total_planes_activos')
    ->orderBy('zones.name')
    ->get();

    return view('dashboard', compact('list_logs','total_deposito','total_tarjeta','total_efectivo','total_clientes','total_pagos_pendientes', 'planes_activos_por_zona'));
})->name('dashboard');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('dashboard/facturacion-mensual/preview', [BillingCycleController::class, 'preview'])
        ->name('dashboard.billing.preview');
    Route::post('dashboard/facturacion-mensual/generate', [BillingCycleController::class, 'generate'])
        ->name('dashboard.billing.generate');
    Route::get('configuracion/respaldo-completo', [BackupController::class, 'download'])
        ->name('settings.backup.download');
    Route::post('configuracion/restaurar-respaldo', [BackupController::class, 'restore'])
        ->name('settings.backup.restore');
});


Route::middleware(['auth:sanctum', 'verified'])->group(function() {
    Route::view('users','users.index')->middleware(['can:users.index'])->name('users.index');
    Route::view('roles','roles.index')->middleware(['can:roles.index'])->name('roles.index');

    //PLAES / PRODUCTOS
    Route::view('planes','products.index')->middleware(['can:products.index'])->name('products.index');
    Route::view('mis-planes','products.mine')->middleware(['can:products.mine'])->name('products.mine');
    Route::view('planes-asignados','products.asigne')->middleware(['can:products.asigne'])->name('products.asigne');

    //ZONAS
    Route::view('zones','zones.index')->middleware(['can:zones.index'])->name('zones.index');
    Route::get('zones/{zone}/nodes',function(Zone $zone)
    {
        return view('zones.nodes', compact('zone'));
    })->middleware(['can:zones.index'])->name('zones.nodes');


    //COLLECTORS
    Route::view('collectors','collectors.index')->middleware(['can:collectors.index'])->name('collectors.index');
    Route::get('collectors/{user}',function(User $user)
    {
        abort_unless($user->hasRole(RoleCatalog::ROLE_COBRADOR), 404);
        return view('collectors.show', compact('user'));
    })->middleware(['can:collectors.index'])->name('collectors.show');

    Route::get('collectors/{collector}/zones',function(User $collector)
    {
        abort_unless($collector->hasRole(RoleCatalog::ROLE_COBRADOR), 404);
        return view('collectors.zones', compact('collector'));
    })->middleware(['can:collectors.index'])->name('collectors.zones');

    //CLIENTES
    Route::view('clients','clients.index')->middleware(['can:clients.index'])->name('clients.index');
    Route::view('clients-search','clients.search')->middleware(['can:clients.search'])->name('clients.search');
    Route::view('clients-map','clients.map')->middleware(['can:clients.map'])->name('clients.map');
    Route::view('map-assets','map-assets.index')->middleware(['can:map.assets'])->name('map.assets');
    Route::view('map-fiber-routes','map-fiber-routes.index')->middleware(['can:map.fiber'])->name('map.fiber');
    Route::view('map-coverages','map-coverages.index')->middleware(['can:map.coverage'])->name('map.coverage');
    Route::get('clients/{client}',function(Client $client)
    {///clients/1/products?modeResume=true&cp=1 URL QUEMADA
        $user = $client->user;
        $user->reference = $client->reference;
        $user->ip = $client->ip;
        $user->clave_wifi = $client->clave_wifi;

        $list_products = $client->products()->paginate();

        return view('clients.show', compact('user','client','list_products'));
    })->middleware(['can:clients.index'])->name('clients.show');

    Route::get('clients/{client}/products',function(Client $client)
    {
        return view('clients.products', compact('client'));
    })->middleware(['can:clients.index'])->name('clients.products');
    Route::view('importar-clientes','clients.importar')->middleware(['can:clients.importar'])->name('clients.importar');

    //FACTURAS
    Route::view('invoices','invoices.index')->middleware(['can:invoices.index'])->name('invoices.index');

    //PAYMENTS
    Route::view('payments','payments.index',['mostrarPendientes'=>false])->middleware(['can:payments.index'])->name('payments.index');
    Route::view('payments/pending','payments.index',['mostrarPendientes'=>true])->middleware(['can:payments.index'])->name('payments.pending');
    Route::get('payments/pdf/{payment_id}', [PdfController::class,'payment'])->name('payments.pdf');
    Route::get('payments/print/{payment_id}', [PdfController::class,'paymentPrint'])->name('payments.print');
    Route::get('payments/thermal/{payment}', [ThermalPrintController::class, 'paymentReceipt'])->name('payments.thermal');
    Route::get('printing/qz/certificate', [ThermalPrintController::class, 'qzCertificate'])->name('printing.qz.certificate');
    Route::get('printing/qz/sign', [ThermalPrintController::class, 'qzSign'])->name('printing.qz.sign');

    //TICKETS
    Route::view('tickets', 'tickets.index')->middleware(['can:tickets.index'])->name('tickets.index');
    Route::view('ticket-types', 'tickets.types')->middleware(['can:tickets.index'])->name('tickets.types');
    Route::get('clients/{client}/tickets', function(Client $client)
    {
        return view('clients.tickets', compact('client'));
    })->middleware(['can:tickets.index'])->name('clients.tickets');

    //MI PERFIL
    Route::get('perfil',function()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    })->name('myprofile');

    // Route::view('categories','categories.index')->middleware(['can:categories.index'])->name('categories.index');
    // Route::get('subcategories/{category}', [CategoryController::class, 'subcategories'])->name('subcategories.index');

    //REPORTS
    Route::view('reportes-clientes','report.client')->middleware(['can:report.clients'])->name('report.clients');
    Route::view('reportes-clientes-morosos','report.client-moroso')->middleware(['can:report.clients.moroso'])->name('report.clients.moroso');
    Route::view('reportes-facturas','report.invoice')->middleware(['can:report.invoices'])->name('report.invoices');
    Route::view('reportes-planes-zona','report.plan-zone')->middleware(['can:report.invoices'])->name('report.plans.zone');

    //settings
    Route::view('configuracion','setting.index')->middleware(['can:setting.index'])->name('setting.index');
});
