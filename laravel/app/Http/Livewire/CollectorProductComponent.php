<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Node;
use App\Models\Product;
use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\FunctionGeneral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectorProductComponent extends Component
{
    use WithPagination, FunctionGeneral;

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

    public $address;
    public $reference;
    public $date_instalation;
    public $amount_installation;
    public $advance='0';
    public $status;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $modeResume = false;
    private $pagination = 15;
    public $cp = '';
    protected $queryString = ['modeResume'=> ['except' => ''],'cp'=> ['except' => '']];

    public function mount($client)
    {
        $this->client = $client;
        $this->zones = Zone::select(DB::raw("CONCAT(code,' / ',name) AS name_code"), 'id')->pluck('name_code','id');

        if($this->modeResume){
            $this->detail($this->cp);
        }
    }

    public function render()
    {
        if(Auth::user()->hasRole('Cobrador')){
            $collector = Auth::user();
            $list_products =  $collector->zones()
            ->has('nodes.client_products.product')
            ->with('nodes.client_products.product')
            ->whereHas('nodes.client_products.product', function($query){
                    if($this->search!=''){
                        $query->where('address','LIKE','%'.$this->search.'%')
                            ->orWhere('code','LIKE','%'.$this->search.'%');
                    }
                }
            )
            ->get()
            ->pluck('nodes')->collapse()
            ->pluck('client_products')->collapse()
            ->unique()->values()
            ;
            dd($list_products);
            $list_products = $list_products->sortByDesc('created_at');
            $list_products =$this->mypaginate($list_products);

        }else
        if($this->search!=''){
            $list_products = $this->client->products()->where(function($query){
                $query->orwhere('code','LIKE','%'.$this->search.'%');
                $query->orwhere('address','LIKE','%'.$this->search.'%');
            })
            ->paginate($this->pagination);
        }else{
            $list_products = $this->client->products()->paginate($this->pagination);
        }

        $this->products = Product::select(DB::raw("CONCAT(code,' / ',name) AS name_code"), 'id')->pluck('name_code','id');

        if(!empty($this->zone_id)){
            $this->nodes = Node::select(DB::raw("CONCAT(code,' / ',name) AS name_code"), 'id')
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

    public function resetInputFields()
    {
        $this->address = '';
        $this->reference = '';
        $this->date_instalation = '';
        $this->amount_installation = '';
        $this->advance = '0';
        
        $this->nodes='';
        $this->product_id='';
        $this->zone_id='';
        $this->node_id='';

        $this->product_id='';
        $this->selected_id = null;
        $this->createMode = true;
        $this->modeResume = false;
        $this->emit('verTablaForm', 'Listado de Zonas');
    }
    
    public function update()
    {  
        abort_if(!Auth::user()->can('clients.edit'), 401);

        if ($this->product_id) {
            $this->client->products()->attach([
                $this->product_id => [
                    'address' => $this->address ,
                    'reference' => $this->reference ,
                    'date_instalation' => $this->date_instalation ,
                    'amount_installation' => ((is_numeric($this->amount_installation))?$this->amount_installation:0) ,
                    'advance' => $this->advance ,
                    'node_id' => $this->node_id ,
                ]
            ]);
            $this->emit('productUpdate','Plan contratado correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('clients.edit'), 401);
        ClientProduct::find($id)->update([
            'status' =>'-1'
        ]);
        $this->pivot = ClientProduct::find($id);
        $this->emit('productDelete');
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
    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy',
        'onupdateMode' => 'updateMode'
    ];

}
