<?php

namespace App\Http\Livewire;

use App\Models\Payment;
use App\Models\User;
use App\Support\RoleCatalog;
use App\Traits\FunctionGeneral;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class PaymentComponent extends Component
{
    use WithPagination, FunctionGeneral;

    protected $paginationTheme= 'bootstrap';

    public $invoice_id;


    public $mostrarPendientes = false;
    public $mostrarCliente = false;
    public $mostrarPlan = false;
    public $search;
    public $user_id = '';
    public $method_payment = '';
    public $tipo_fecha = 'rango';
    public $fecha_dia = '';
    public $fecha_mes = '';
    public $fecha_anio = '';
    public $desde='';
    public $hasta='';

    public function mount($invoice_id, $mostrarPendientes)
    {
        if(!empty($invoice_id)){
            $this->invoice_id = $invoice_id;
        }

        if(!empty($mostrarPendientes)){
            $this->mostrarPendientes = $mostrarPendientes;
        }
    }
    
    public function render()
    {
        $users = collect();
        $total_payments_received = 0;

        if(!Auth::user()->hasRole('Cliente')){
            $users = User::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', RoleCatalog::internalRoleNames());
                })
                ->orderBy('name')
                ->orderBy('lastname')
                ->get()
                ->mapWithKeys(function ($user) {
                    return [$user->id => $user->name_complete];
                });
        }

        if($this->invoice_id>0){
            $paymentsQuery = Payment::with('invoice.client_product')
            ->where('invoice_id',$this->invoice_id)
            ->when($this->user_id != '', function ($query) {
                $query->where('payments.user_id', $this->user_id);
            })
            ->when($this->method_payment != '', function ($query) {
                $query->where('payments.method_payment', $this->method_payment);
            })
            ->whereHas('invoice.client_product.client.user',function($query){
                if($this->search!=''){
                    $query->where(function($searchQuery){
                        applyFlexibleSearch($searchQuery, $this->search, userSearchExpressions('users'));
                        $searchQuery->orWhereHas('client', function ($clientQuery) {
                            applyFlexibleSearch($clientQuery, $this->search, ['clients.reference']);
                        });
                        $searchQuery->orWhereHas('client.client_products', function ($planQuery) {
                            applyFlexibleSearch($planQuery, $this->search, planSearchExpressions('client_product'));
                        });
                        $searchQuery->orWhereRaw(' LPAD(CONCAT(payments.id,DATE_FORMAT(payments.created_at,"%Y%m")),8,"0")  LIKE ?',['%'.$this->search.'%']);
                        $searchQuery->orWhereRaw(' payments.no_voucher LIKE ?',['%'.$this->search.'%']);
                    });
                }

                if(Auth::user()->hasRole('Cliente')){
                    $query->where('users.id',Auth::user()->id);
                }
            });

            $this->applyDateFilters($paymentsQuery);

            $paymentsQuery->latest();
        }else{
            if(Auth::user()->hasRole('Cobrador')){
                $paymentsQuery = Payment::with('invoice.client_product')
                ->when($this->user_id != '', function ($query) {
                    $query->where('payments.user_id', $this->user_id);
                })
                ->when($this->method_payment != '', function ($query) {
                    $query->where('payments.method_payment', $this->method_payment);
                })
                ->whereHas('invoice.client_product.client.user',function($query){
                    if($this->search!=''){
                        $query->where(function($searchQuery){
                            applyFlexibleSearch($searchQuery, $this->search, userSearchExpressions('users'));
                            $searchQuery->orWhereHas('client', function ($clientQuery) {
                                applyFlexibleSearch($clientQuery, $this->search, ['clients.reference']);
                            });
                            $searchQuery->orWhereHas('client.client_products', function ($planQuery) {
                                applyFlexibleSearch($planQuery, $this->search, planSearchExpressions('client_product'));
                            });
                            $searchQuery->orWhereRaw(' LPAD(CONCAT(payments.id,DATE_FORMAT(payments.created_at,"%Y%m")),8,"0")  LIKE ?',['%'.$this->search.'%']);
                            $searchQuery->orWhereRaw(' payments.no_voucher LIKE ?',['%'.$this->search.'%']);
                        });
                    }

                    if(Auth::user()->hasRole('Cliente')){
                        $query->where('users.id',Auth::user()->id);
                    }

                    if($this->mostrarPendientes){
                        $query->where('payments.status','0');
                    }
                })->where('user_id', Auth::id());

                $this->applyDateFilters($paymentsQuery);

                $paymentsQuery->latest();
            }else{
                $paymentsQuery = Payment::with('invoice.client_product')
                ->when($this->user_id != '', function ($query) {
                    $query->where('payments.user_id', $this->user_id);
                })
                ->when($this->method_payment != '', function ($query) {
                    $query->where('payments.method_payment', $this->method_payment);
                })
                ->whereHas('invoice.client_product.client.user',function($query){
                    if($this->search!=''){
                        $query->where(function($searchQuery){
                            applyFlexibleSearch($searchQuery, $this->search, userSearchExpressions('users'));
                            $searchQuery->orWhereHas('client', function ($clientQuery) {
                                applyFlexibleSearch($clientQuery, $this->search, ['clients.reference']);
                            });
                            $searchQuery->orWhereHas('client.client_products', function ($planQuery) {
                                applyFlexibleSearch($planQuery, $this->search, planSearchExpressions('client_product'));
                            });
                            $searchQuery->orWhereRaw(' LPAD(CONCAT(payments.id,DATE_FORMAT(payments.created_at,"%Y%m")),8,"0")  LIKE ?',['%'.$this->search.'%']);
                            $searchQuery->orWhereRaw(' payments.no_voucher LIKE ?',['%'.$this->search.'%']);
                        });
                    }

                    if(Auth::user()->hasRole('Cliente')){
                        $query->where('users.id',Auth::user()->id);
                    }

                    if($this->mostrarPendientes){
                        $query->where('payments.status','0');
                    }
                });

                $this->applyDateFilters($paymentsQuery);

                $paymentsQuery->latest();
            }

            if(Auth::user()->hasRole('Cliente')){
                $this->mostrarCliente = false;
            }else{
                $this->mostrarCliente = true;
            }

            $this->mostrarPlan = true;
        }

        $total_payments_received = (clone $paymentsQuery)->sum('price');
        $list_payments = $paymentsQuery->paginate();
       
        return view('livewire.payment-component', compact('list_payments', 'users', 'total_payments_received'));
    }

    public function processPayment($id, $tipo)
    {
        abort_if(!Auth::user()->can('payments.process'), 401);
        $payment = Payment::findOrFail($id);
        $payment->status = (($tipo=='1')?'1':'-1');
        $payment->save();
        $this->emit('ProcessPayment');
    }

    public function deletePayment($id)
    {
        abort_if(!Auth::user()->can('payments.destroy'), 401);

        $payment = Payment::findOrFail($id);
        $invoice = $payment->invoice;

        $payment->delete();

        // If a payment is removed, the invoice must go back to "Pendiente"
        // (even if it was previously marked as "Pagada").
        if ($invoice) {
            $invoice->status = '0';
            $invoice->save();
        }

        if ($this->invoice_id == $payment->invoice_id) {
            $this->emit('onRefresh');
        }

        $this->emit('PaymentDeleted', 'Pago eliminado correctamente.');
    }

    public function updatingSearch()
    {
        //otra opcion seria $this->resetPage();
        $this->gotoPage(1);
    }

    public function updatingTipoFecha()
    {
        $this->resetDateFilters();
        $this->gotoPage(1);
    }

    public function consultar()
    {
        $this->gotoPage(1);
    }

    public function limpiarFiltrosFecha()
    {
        $this->tipo_fecha = 'rango';
        $this->resetDateFilters();
        $this->gotoPage(1);
    }

    public function actInvoices($id)
    {
        $this->invoice_id =  $id;
        $this->emit('$refresh');
    }
    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onProcessPaymentRow' =>'processPayment'
        ,'onDeletePaymentRow' => 'deletePayment'
        ,'PaymentComponent:actInvoice' =>'actInvoices'
    ];

    protected function applyDateFilters($query)
    {
        switch ($this->tipo_fecha) {
            case 'dia':
                if ($this->fecha_dia != '') {
                    $query->whereDate('payments.created_at', $this->fecha_dia);
                }
                break;

            case 'mes':
                if ($this->fecha_mes != '' && preg_match('/^\d{4}-\d{2}$/', $this->fecha_mes)) {
                    [$year, $month] = explode('-', $this->fecha_mes);
                    $query->whereYear('payments.created_at', (int) $year)
                        ->whereMonth('payments.created_at', (int) $month);
                }
                break;

            case 'anio':
                if ($this->fecha_anio != '' && is_numeric($this->fecha_anio)) {
                    $query->whereYear('payments.created_at', (int) $this->fecha_anio);
                }
                break;

            default:
                if($this->desde!=''){
                    $query->whereRaw('payments.created_at >= ?',[$this->desde.' 00:00:00']);
                }

                if($this->hasta!=''){
                    $query->whereRaw('payments.created_at <= ?',[ $this->hasta.' 23:59:59']);
                }
                break;
        }
    }

    protected function resetDateFilters()
    {
        $this->fecha_dia = '';
        $this->fecha_mes = '';
        $this->fecha_anio = '';
        $this->desde = '';
        $this->hasta = '';
    }
}
