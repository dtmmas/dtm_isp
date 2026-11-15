<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\Invoice;
use App\Traits\FunctionGeneral;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InvoiceComponent extends Component
{
    use WithPagination, FunctionGeneral;

    protected $paginationTheme= 'bootstrap';

    public $pivot;
    public $client;
    
    //pagos
    public $invoice_id;
    public $list_payments=[];

    public $selected_id, $search;
    private $pagination = 15;

    public $mostrarFacturasPendientes = 'All';
    public $mostrarCliente = false;
    public $mostrarPlan = false;

    public function mount($pivot, $client)
    {
        if(!empty($pivot)){
            $this->pivot = $pivot;
        }

        if(!empty($client)){
            $this->client = $client;
        }else
        if(Auth::user()->hasRole('Cliente')){
            $this->client = Auth::user()->client;
        }

    }

    public function render()
    {

        if(!empty($this->pivot)){
            $list_invoices = Invoice::
            where('client_product_id',$this->pivot->id)
            ->where(function($query){
                if($this->search!=''){
                    $query->where('created_at','LIKE','%'.$this->search.'%');
                }
                
                if($this->mostrarFacturasPendientes!='All'){
                    $query->where('invoices.status',$this->mostrarFacturasPendientes);
                }
            })
            ->latest()
            ->paginate($this->pagination);
            
            $this->mostrarCliente(false);
            $this->mostrarPlan = false;

        }else if(!empty($this->client)){


            if($this->mostrarFacturasPendientes!='All'){
                $list_invoices = Client::with('client_products.invoices')->find($this->client->id)
                    ->client_products()
                    ->has('invoices')
                    ->latest()
                    ->get()
                    ->pluck('invoices')
                    ->collapse()
                    ->where('status',$this->mostrarFacturasPendientes);
            }else{
                $list_invoices = Client::with('client_products.invoices')->find($this->client->id)
                ->client_products()
                ->has('invoices')
                ->latest()
                ->get()
                ->pluck('invoices')
                ->collapse();
            }

            $list_invoices = $list_invoices->sortByDesc('created_at');
            $list_invoices =$this->mypaginate($list_invoices);
            
            $this->mostrarCliente(false);
            $this->mostrarPlan = true;
            
        }else{
            if(Auth::user()->hasRole('Cobrador')){
                $my_client_product = implode(',', Auth::user()->clientProductsArray());

                $list_invoices = Invoice::with('client_product')
                ->whereHas('client_product.client.user', function($query) use ($my_client_product){
                    if($this->search!=''){
                        $query->where(function ($searchQuery) {
                            applyFlexibleSearch($searchQuery, $this->search, userSearchExpressions('users'));
                            $searchQuery->orWhereHas('client', function ($clientQuery) {
                                applyFlexibleSearch($clientQuery, $this->search, ['clients.reference']);
                            });
                            $searchQuery->orWhereHas('client.client_products', function ($planQuery) {
                                applyFlexibleSearch($planQuery, $this->search, planSearchExpressions('client_product'));
                            });
                        });
                    }

                    if($this->mostrarFacturasPendientes!='All'){
                        $query->where('invoices.status',$this->mostrarFacturasPendientes);
                    }
                    if($my_client_product==""){
                        $my_client_product='0';
                    }
                    $query->whereRaw('client_product.id in ('.$my_client_product.')');

                })

                ->whereHas('client_product.client.user', function($query){
                   

                    if($this->mostrarFacturasPendientes!='All'){
                        $query->where('invoices.status',$this->mostrarFacturasPendientes);
                    }
                })
                ->latest()
                ->paginate($this->pagination);

            }else{
                $list_invoices = Invoice::with('client_product')
                ->whereHas('client_product.client.user', function($query){
                    if($this->search!=''){
                        $query->where(function($query){
                            applyFlexibleSearch($query, $this->search, userSearchExpressions('users'));
                            $query->orWhereHas('client', function ($clientQuery) {
                                applyFlexibleSearch($clientQuery, $this->search, ['clients.reference']);
                            });
                            $query->orWhereHas('client.client_products', function ($planQuery) {
                                applyFlexibleSearch($planQuery, $this->search, planSearchExpressions('client_product'));
                            });
                        });
                    }

                    if($this->mostrarFacturasPendientes!='All'){
                        $query->where('invoices.status',$this->mostrarFacturasPendientes);
                    }
                })
                ->latest()
                ->paginate($this->pagination);
            }
            $this->mostrarCliente(true);
            $this->mostrarPlan = true;
        }
        
        return view('livewire.invoice-component', compact('list_invoices'));
    }

    public function mostrarCliente($valor)
    {
        if(Auth::user()->hasRole('Cliente')){
            $this->mostrarCliente = false;
        }else{
            $this->mostrarCliente = $valor;
        }
    }

    public function updatedInvoiceId()
    {
        $this->emit('PaymentComponent:actInvoice', $this->invoice_id);
    }

    public function updatedmostrarFacturasPendientes()
    {
         $this->gotoPage(1);
        // if(!$this->mostrarFacturasPendientes){
        //     return redirect()->route('invoices.index');
        // }
    }
    
    public function PagoPaypal($captura,  $id)
    {
        abort_if(!Auth::user()->can('payments.create'), 401);
        
        if($captura[0]['payee']['email_address']==config('payment.paypal.email_address')
        && $captura[0]['payments']['captures'][0]['status']=="COMPLETED"){
            $invoice = Invoice::findOrFail($this->invoice_id=$id);
            $payement = $invoice->payments()->create([
                'no_voucher' => $captura[0]['payments']['captures'][0]['id'],
                'price' => $invoice->priceConversion('Q', $captura[0]['amount']['value']),
                'observation' => 'PAGO CON PAYPAL',
                'method_payment' => '2'
            ]);
            
            $payement->status = '1';
            $payement->save(); 
            $this->gotoPage(1);  
        }else{
            dd($captura);
        }
    }

    protected $listeners = [
        'onRefresh' =>'$refresh',
        'onPagoPaypal' =>'PagoPaypal'
    ];
}
