<?php

namespace App\Http\Livewire;

use \PDF;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Zone;
use App\Models\Client;
use App\Models\ClientProduct;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportClientMorosoComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $desde='';
    public $hasta='';
    public $status='';

    public $zones;
    public $zone_id;
    //variables para fucnionalidades
    public $search;

    private $pagination = 25;

    public function mount()
    {
        $this->zones = Zone::select(DB::raw("CONCAT(id,' / ',name) AS name_code"), 'id')->pluck('name_code','id');
    }

    public function render()
    {   
        $list_client_products = ClientProduct::with(['client.user', 'invoices' => function ($query) {
            $query->where('status', "-1");
            if($this->desde!=''){
                $query->where('invoices.created_at','>=',$this->desde." 00:00:00");
            }
            if($this->hasta!=''){
                $query->where('invoices.created_at','<=',$this->hasta." 23:59:59");
            }
        }, 'node', 'product'])
        ->whereHas('client')
        ->whereHas('node',function($query){
            if($this->zone_id!=''){
                $query->where('nodes.zone_id','=',$this->zone_id);
            }
        })
        ->whereHas('invoices', function ($query) {
            $query->where('status', '-1');
            if ($this->desde != '') {
                $query->where('created_at', '>=', $this->desde . " 00:00:00");
            }
            if ($this->hasta != '') {
                $query->where('created_at', '<=', $this->hasta . " 23:59:59");
            }})   
         ->withCount(['invoices' => function ($query) {
            $query->where('status', '-1');
            if ($this->desde != '') {
                $query->where('created_at', '>=', $this->desde . " 00:00:00");
            }
            if ($this->hasta != '') {
                $query->where('created_at', '<=', $this->hasta . " 23:59:59");
            }
        }])         
        ->orderByDesc('invoices_count') 
        ->orderBy('client_product.client_id', 'asc')
        ->paginate($this->pagination);

        return view('livewire.report-client-moroso-component', compact('list_client_products'));
    }

    public function Filtrar($desde, $hasta)
    {
        if($desde!=''){
            $this->desde = date('Y-m-d', strtotime($desde));
        }
        if($hasta!=''){
            $this->hasta = date('Y-m-d', strtotime($hasta));
        }
        
        $this->gotoPage(1);
    }

    public function exportExcel() {
        
        $list_client_products = $this->ListClients();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClientMorosoExport($list_client_products), 'reporte_clientes_morosos_'.$this->desde.'.xlsx');
    }

    public function exportPdf()
    {
        $list_client_products = $this->ListClients();
        $nombre = 'Reporte_Clientes_Morosos_'.str_replace(" ", "_", fecha_actual()).'.pdf';
        $pdf = PDF::loadView('pdfs.report-client-morosos', compact('list_client_products'))
        ->setPaper('a4', 'landscape')
        ->output();
        return response()->streamDownload(
             fn () => print($pdf),
             $nombre
        );
    }

    public function ListClients(){
        return ClientProduct::with(['client.user', 'invoices' => function ($query) {
            $query->where('status', "-1");
            if($this->desde!=''){
                $query->where('invoices.created_at','>=',$this->desde." 00:00:00");
            }
            if($this->hasta!=''){
                $query->where('invoices.created_at','<=',$this->hasta." 23:59:59");
            }
        }, 'node', 'product'])
        ->whereHas('client')
        ->whereHas('node',function($query){
            if($this->zone_id!=''){
                $query->where('nodes.zone_id','=',$this->zone_id);
            }
        })
        ->whereHas('invoices', function ($query) {
            $query->where('status', '-1');
            if ($this->desde != '') {
                $query->where('created_at', '>=', $this->desde . " 00:00:00");
            }
            if ($this->hasta != '') {
                $query->where('created_at', '<=', $this->hasta . " 23:59:59");
            }})   
         ->withCount(['invoices' => function ($query) {
            $query->where('status', '-1');
            if ($this->desde != '') {
                $query->where('created_at', '>=', $this->desde . " 00:00:00");
            }
            if ($this->hasta != '') {
                $query->where('created_at', '<=', $this->hasta . " 23:59:59");
            }
        }])         
        ->orderByDesc('invoices_count') 
        ->orderBy('client_product.client_id', 'asc')
        ->get();
    }
    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onFiltrar' =>'Filtrar'
    ];
}
