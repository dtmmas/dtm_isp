<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ProductComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    public $description;
    public $velocidad;
    public $price;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $verTabla = true;
    private $pagination = 15;
    
    public function render()
    {
        if($this->search!=''){
            $list_products = Product::where(function($query){
                $query->orwhere('name','LIKE','%'.$this->search.'%');
            })
            ->paginate($this->pagination);
        }else{
            $list_products = Product::paginate($this->pagination);
        }
        return view('livewire.product-component', compact('list_products'));
    }
    
    public function updatingSearch()
    {
        //otra opcion seria $this->resetPage();
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->price = '';
        $this->velocidad = '';
        $this->selected_id = null;
        $this->createMode = true;
        $this->verTabla = true;
        $this->emit('verTablaForm', 'Listado de Planes');
    }

    public function store()
    {
        abort_if(!Auth::user()->can('products.create'), 401);
        //validar informacion
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'velocidad' => ['required', 'string', 'max:45'],
            'price' => ['required', 'numeric'],
        ]);
        
        $product = Product::create([
            'name' => $this->name,
            'price' => $this->price,
            'velocidad' => $this->velocidad,
        ]);
        
        $this->emit('productUpdateStore','Plan creado correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('products.edit'), 401);
        $this->product = $product = Product::findOrFail($id,['id','name','price','velocidad']);
        $this->name = $product->name;
        $this->price = $product->price;
        $this->velocidad = $product->velocidad;
        $this->selected_id = $product->id;

        $this->createMode = false;
        $this->verTabla = false;
        $this->emit('productEditCreate', 'Editar Plan: '.$product->name);
    }

    
    public function update()
    {  
        abort_if(!Auth::user()->can('products.edit'), 401);
        $validatedDate = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'velocidad' => ['required', 'string', 'max:45'],
            'price' => ['required', 'numeric'],
        ]);
      
        if ($this->selected_id) {
            $product = Product::findOrFail($this->selected_id);

            $product->update([
                'name' => $this->name,
                'price' => $this->price,
                'velocidad' => $this->velocidad,
            ]);
            
            $this->createMode = false;
            $this->emit('productUpdateStore','Plan actualizado correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('products.destroy'), 401);
        $product = Product::findOrFail($id);
        $product->delete();
        // $this->resetInputFields();
        $this->emit('productDelete');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
