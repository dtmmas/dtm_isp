<?php

namespace App\Http\Livewire;

use \PDF;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportInvoiceComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $desde='';
    public $hasta='';
    public $status='';
    public $zone_id='';
    //variables para fucnionalidades
    public $search;

    private $pagination = 25;

    public function render()
    {
        $zones = Zone::orderBy('name')->pluck('name', 'id');
        $list_invoices = $this->queryInvoices()->paginate($this->pagination);

        return view('livewire.report-invoice-component', compact('list_invoices', 'zones'));
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
        $list_invoices = $this->ListInvoices();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InvoiceExport($list_invoices), 'reporte_facturas_'.$this->desde.'.xlsx');
    }

    public function exportPdf()
    {
        set_time_limit(0);   
        ini_set('memory_limit', '3024M');
        $invoices = $this->ListInvoices();
        $nombre = 'Reporte_Facturas_'.str_replace(" ", "_", fecha_actual()).'.pdf';
        $pdf = PDF::loadView('pdfs.report-invoices', compact('invoices'))
        ->setPaper('a4', 'landscape')
        ->output();
        return response()->streamDownload(
             fn () => print($pdf),
             $nombre
        );
    }

    public function ListInvoices()
    {
        return $this->queryInvoices()->get();
    }

    private function queryInvoices()
    {
        return Invoice::with('client_product', 'client_product.node.zone')
        ->when($this->status !== '', function ($query) {
            $query->where('status', $this->status);
        })
        ->when($this->desde !== '', function ($query) {
            $query->where('updated_at', '>=', $this->desde . " 00:00:00");
        })
        ->when($this->hasta !== '', function ($query) {
            $query->where('updated_at', '<=', $this->hasta . " 23:59:59");
        })
        ->when($this->zone_id !== '', function ($query) {
            $query->whereHas('client_product.node', function ($nodeQuery) {
                $nodeQuery->where('zone_id', $this->zone_id);
            });
        })
        ->orderByDesc('updated_at');
    }

    protected $listeners = [
        'onFiltrar' =>'Filtrar'
    ];
}
