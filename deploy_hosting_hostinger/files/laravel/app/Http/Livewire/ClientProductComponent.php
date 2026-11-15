<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Payment;
use App\Models\Node;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Zone;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientProductComponent extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme= 'bootstrap';

    public $client;
    public $pivot;
    public $product;

    public $products;
    public $zones;
    public $nodes = [];
    public $product_id;
    public $zone_id;
    public $node_id;
    public $filter_zone_id;

    public $address;
    public $reference;
    public $latitude;
    public $longitude;
    public $date_instalation;
    public $amount_installation;
    public $advance='0';
    public $status;
    public $plan_photos = [];
    public $existing_plan_photos = [];
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $modeResume = false;
    private $pagination = 15;
    public $cp = '';

    //create invoice
    public $fecha_factura;
    public $estado_factura;

    //pagos delantados
    public $monto_advancement;
    public $meses_advancement;

    protected $queryString = ['modeResume'=> ['except' => ''],'cp'=> ['except' => '']];

    public function mount($client)
    {
        $this->client = $client;
        $this->zones = Zone::select(DB::raw("CONCAT(id,' / ',name) AS name_code"), 'id')->pluck('name_code','id');

        if($this->modeResume){
            $this->detail($this->cp);
        }
    }

    public function render()
    {
        $list_products = $this->client->products()
        ->when($this->search != '', function ($query) {
            $query->where('address', 'LIKE', '%' . $this->search . '%');
        })
        ->when($this->filter_zone_id != '', function ($query) {
            $nodeIds = Node::where('zone_id', $this->filter_zone_id)->pluck('id');
            $query->wherePivotIn('node_id', $nodeIds);
        })
        ->paginate($this->pagination);

        $this->products = Product::select(DB::raw("CONCAT(id,' / ',name) AS name_code"), 'id')->pluck('name_code','id');

        if(!empty($this->zone_id)){
            $this->nodes = Node::select(DB::raw("CONCAT(id,' / ',name) AS name_code"), 'id')
            ->where('zone_id', $this->zone_id)
            ->pluck('name_code','id');
        }

        return view('livewire.client-product-component', compact('list_products'));
    }
    
    public function updatingSearch()
    {
        //otra opcion seria $this->resetPage();
        $this->gotoPage(1);
    }

    public function updatingFilterZoneId()
    {
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->address = '';
        $this->reference = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->date_instalation = '';
        $this->amount_installation = '';
        $this->advance = '0';
        $this->plan_photos = [];
        $this->existing_plan_photos = [];
        
        $this->nodes='';
        $this->product_id='';
        $this->zone_id='';
        $this->node_id='';

        $this->product_id='';
        $this->selected_id = null;
        $this->createMode = true;
        $this->emit('verTablaForm', 'Listado de Zonas');
    }
    
    public function resetInputFieldsInvoice()
    {
        $this->fecha_factura='';
        $this->estado_factura='';
    }
    
    public function resetInputFieldsAdvancement()
    {
        $this->monto_advancement='';
        $this->meses_advancement='';
    }

    public function update()
    {  
        abort_if(!Auth::user()->can('clients.edit'), 401);

        $this->validate([
            'product_id' => ['required'],
            'address' => ['required', 'string'],
            'reference' => ['required', 'string'],
            'date_instalation' => ['required', 'date'],
            'amount_installation' => ['nullable'],
            'advance' => ['required', 'in:0,1'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'plan_photos.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $uploadedPhotos = $this->storePlanPhotos();

        if ($this->product_id && $this->createMode) {
            $this->client->products()->attach([
                $this->product_id => [
                    'address' => $this->address ,
                    'reference' => $this->reference ,
                    'latitude' => $this->normalizeCoordinate($this->latitude),
                    'longitude' => $this->normalizeCoordinate($this->longitude),
                    'date_instalation' => $this->date_instalation ,
                    'amount_installation' => ((is_numeric($this->amount_installation))?$this->amount_installation:0) ,
                    'advance' => $this->advance ,
                    'node_id' => $this->node_id ,
                    'installation_photos' => !empty($uploadedPhotos) ? json_encode($uploadedPhotos) : null,
                ]
            ]);
            $this->emit('productUpdate','Plan contratado correctamente.');
            $this->resetInputFields();
        } elseif ($this->product_id && !$this->createMode && $this->selected_id) {
            $clientProduct = ClientProduct::findOrFail($this->selected_id);
            $installationPhotos = array_values(array_filter(array_merge($this->existing_plan_photos, $uploadedPhotos)));

            $clientProduct->update([
                'product_id' => $this->product_id,
                'address' => $this->address,
                'reference' => $this->reference,
                'latitude' => $this->normalizeCoordinate($this->latitude),
                'longitude' => $this->normalizeCoordinate($this->longitude),
                'date_instalation' => $this->date_instalation,
                'amount_installation' => ((is_numeric($this->amount_installation)) ? $this->amount_installation : 0),
                'advance' => $this->advance,
                'node_id' => $this->node_id,
                'installation_photos' => !empty($installationPhotos) ? json_encode($installationPhotos) : null,
            ]);

            if ($this->modeResume && $this->cp == $clientProduct->id) {
                $this->detail($clientProduct->id);
            }

            $this->emit('productUpdate','Plan actualizado correctamente.');
            $this->resetInputFields();
        }
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('clients.edit'), 401);

        $clientProduct = ClientProduct::findOrFail($id);

        $this->selected_id = $clientProduct->id;
        $this->product_id = $clientProduct->product_id;
        $this->address = $clientProduct->address;
        $this->reference = $clientProduct->reference;
        $this->latitude = $clientProduct->latitude;
        $this->longitude = $clientProduct->longitude;
        $this->date_instalation = $clientProduct->date_instalation;
        $this->amount_installation = $clientProduct->amount_installation;
        $this->advance = (string) $clientProduct->advance;
        $this->node_id = $clientProduct->node_id;
        $this->plan_photos = [];
        $this->existing_plan_photos = is_array($clientProduct->installation_photos) ? $clientProduct->installation_photos : [];
        $this->zone_id = optional($clientProduct->node)->zone_id;
        $this->nodes = Node::select(DB::raw("CONCAT(id,' / ',name) AS name_code"), 'id')
        ->where('zone_id', $this->zone_id)
        ->pluck('name_code','id');
        $this->createMode = false;
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('clients.edit'), 401);
        $clientProduct = ClientProduct::findOrFail($id);

        if($clientProduct->invoices()->exists()){
            $this->emit('productAction', 'No se puede eliminar el plan porque tiene facturas asociadas.', 'warning');
            return;
        }

        $clientProduct->delete();

        if ($this->modeResume && $this->cp == $id) {
            $this->modeResume = false;
            $this->cp = '';
            $this->pivot = null;
            $this->product = null;
        }

        $this->emit('productAction', 'Plan eliminado correctamente.', 'success');
    }

    public function suspend($id)
    {
        abort_if(!Auth::user()->can('clients.edit'), 401);

        $clientProduct = ClientProduct::findOrFail($id);
        $clientProduct->update(['status' => '0']);

        if ($this->modeResume && $this->cp == $id) {
            $this->detail($id);
        }

        $this->emit('productAction', 'Plan suspendido correctamente.', 'success');
    }

    public function activate($id)
    {
        abort_if(!Auth::user()->can('clients.edit'), 401);

        $clientProduct = ClientProduct::findOrFail($id);
        $clientProduct->update(['status' => '1']);

        if ($this->modeResume && $this->cp == $id) {
            $this->detail($id);
        }

        $this->emit('productAction', 'Plan activado correctamente.', 'success');
    }

    public function cancel($id)
    {
        abort_if(!Auth::user()->can('clients.edit'), 401);

        $clientProduct = ClientProduct::findOrFail($id);
        $pendingPayments = Payment::where('status', '0')
        ->whereHas('invoice', function ($query) use ($id) {
            $query->where('client_product_id', $id);
        })->get();

        $pendingInvoices = Invoice::where('client_product_id', $id)
        ->whereIn('status', ['0', '-1'])
        ->get();

        $deletedPayments = $pendingPayments->count();

        foreach ($pendingPayments as $payment) {
            $payment->delete();
        }

        foreach ($pendingInvoices as $invoice) {
            $invoice->delete();
        }

        $clientProduct->update(['status' => '-1']);

        if ($this->modeResume && $this->cp == $id) {
            $this->detail($id);
        }

        $this->emit('onRefresh');
        $this->emit('onRefreshCP');
        $this->emit('productAction', 'Se eliminaron '.$deletedPayments.' pagos pendientes y luego se canceló el plan.', 'success');
    }

    public function detail($cp)
    {
        $this->cp = $cp;
        $this->pivot = ClientProduct::find($cp);
        $this->product = Product::findOrFail($this->pivot->product_id);
        $this->client = Client::with('user')->findOrFail($this->pivot->client_id);
        $this->modeResume = true;
    }

    public function updateMode($mode)
    {
        $this->modeResume = $mode;
    }

    public function storeInvoice(){
        abort_if(!Auth::user()->can('invoices.create'), 401);
        //validar informacion
        $this->validate([
            'fecha_factura' => ['required', 'string', ],
            'estado_factura' => ['required','in:1,-1,0'],
        ]);
        if(Invoice::where('created_at','LIKE',$this->fecha_factura.'%')->where('client_product_id',$this->pivot->id)->exists()){
            $this->emit('clientStoreInvoice','Ya existe factura para ese mes.',"warning");
        }else{
            $fecha_actual = new \Carbon\Carbon($this->fecha_factura.'-05');
            $fecha_anterior = $fecha_actual->subMonth();
            $mes_anterior = $fecha_anterior->format('Y-m-');

            if(!Invoice::where('created_at','LIKE',$mes_anterior.'%')->where('client_product_id',$this->pivot->id)->exists() && $fecha_actual->isFuture()){
                $nombre_mes = $fecha_anterior->monthName;
                $this->emit('clientStoreInvoice',"Se debe crear primero la factura del mes de {$nombre_mes}.","warning");
            }else{
                $fechaCreacion =  $this->obtenerFechaValida($this->fecha_factura, date('d'));
                $user = Invoice::create([
                    'user_id' => Auth::user()->id,
                    'client_product_id' =>$this->pivot->id,
                    'price' => $this->product->price,
                    'date_expiration' => ultimoDiaMesFecha($this->fecha_factura.'-05')." 23:59:59",
                    'created_at' => $fechaCreacion,
                    'status' => $this->estado_factura
                ]);

                $this->resetInputFieldsInvoice();
                $this->emit('clientStoreInvoice','Factura creada correctamente.',"success"); // Close model to using to jquery
            }
        }
    }

    public function storePaymentAdvancement(){
        abort_if(!Auth::user()->can('payments.advancement'), 401);
        //validar informacion
        $this->validate([
            'monto_advancement' => ['required', 'numeric', ],
            'meses_advancement' => ['required','numeric','min:1'],
        ]);
        
        $Contratacion = ClientProduct::find($this->pivot->id);
        $meses_total = $Contratacion->payments_advancement+$this->meses_advancement;
        $Contratacion->update([
            'payments_advancement' =>  $meses_total
        ]);

        $Invoice = Invoice::create([
                'client_product_id' =>$this->pivot->id,
                'price' => $this->monto_advancement,
                'date_expiration' => ultimoDiaMesSumado($meses_total-1),
                'status' => '1',
                'payments_advancement' => $this->meses_advancement
            ]);

        $paymentCreated = $Invoice->payments()->create([
            'no_voucher' => date('YmdHis'),
            'price' => $this->monto_advancement,
            'img_voucher' =>  null,
            'observation' => 'PAGO '.$this->meses_advancement.' MES(ES) POR ADELANTADO',
            'method_payment' => '3',
        ]);

        $payment = $paymentCreated;//Payment::findOrFail($paymentCreated->id);
        $payment->status = '1';
        $payment->save();

        $this->resetInputFieldsAdvancement();
        $this->emit('storePaymentAdvancement','Adelanto asociado correctamente.',"success"); // Close model to using to jquery
    }


    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy',
        'onSuspendRow' => 'suspend',
        'onActivateRow' => 'activate',
        'onCancelRow' => 'cancel',
        'setPlanCoordinates' => 'setPlanCoordinates',
        'onupdateMode' => 'updateMode',
        'onRefreshCP' =>'$refresh',
    ];

    public function setPlanCoordinates($latitude, $longitude)
    {
        $this->latitude = $this->normalizeCoordinate($latitude);
        $this->longitude = $this->normalizeCoordinate($longitude);
    }

    private function storePlanPhotos()
    {
        $photos = [];

        if (empty($this->plan_photos)) {
            return $photos;
        }

        foreach ($this->plan_photos as $photo) {
            if (!$photo) {
                continue;
            }

            $photos[] = $photo->store('client-products', 'public');
        }

        return $photos;
    }

    private function normalizeCoordinate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 7);
    }

    function obtenerFechaValida($anioMes, $diaActual)
    {
        // Obtener el año y mes de la fecha
        list($anio, $mes) = explode('-', $anioMes);

        // Validar si el año y mes son válidos
        if (!checkdate($mes, 1, $anio)) {
            return false; // Año o mes inválidos
        }

        // Obtener la cantidad de días en el mes
        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

        // Ajustar el día si excede los límites del mes
        if ($diaActual < 1) {
            $diaActual = 1;
        } elseif ($diaActual > $diasEnMes) {
            $diaActual = $diasEnMes;
        }

        // Generar la fecha válida
        $fechaValida = sprintf('%04d-%02d-%02d', $anio, $mes, $diaActual);

        return $fechaValida;
    }

}
