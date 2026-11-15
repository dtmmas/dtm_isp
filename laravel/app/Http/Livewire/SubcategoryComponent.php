<?php

namespace App\Http\Livewire;

use App\Models\Subcategory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class SubcategoryComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    public $description;
    public $category_id;
    public $categories;
    public $subcategory;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $verTabla = true;
    private $pagination = 15;
    
    public function mount($category_id)
    {
        $this->category_id = $category_id;
    }
    public function render()
    {
        if($this->search!=''){
            $list_subcategories = Subcategory::where('category_id', $this->category_id)
            ->where(function($query){
                $query->orwhere('name','LIKE','%'.$this->search.'%');
            })
            ->paginate($this->pagination);
        }else{
            $list_subcategories = Subcategory::where('category_id', $this->category_id)
            ->paginate($this->pagination);
        }
        return view('livewire.subcategory-component', compact('list_subcategories'));
    }
    
    public function updatingSearch()
    {
        //otra opcion seria $this->resetPage();
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->description = '';
        $this->selected_id = null;
        $this->createMode = true;
        $this->verTabla = true;
        $this->emit('verTablaForm', 'Listado de Subcategorias');
    }

    public function create()
    {
        abort_if(!Auth::user()->can('roles.create'), 401);
        // $this->categories = Category::all();
        $this->createMode = true;
    }

    public function store()
    {
        abort_if(!Auth::user()->can('categories.create'), 401);
        //validar informacion
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'int'],
        ]);
        
        $this->subcategory = Subcategory::create([
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
        ]);
        
        $this->emit('subcategoryUpdateStore','Subcategoria creada correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('categories.edit'), 401);
        $this->subcategory = $subcategory = Subcategory::findOrFail($id,['id','name','description','category_id']);
        $this->name = $subcategory->name;
        $this->description = $subcategory->description;
        $this->category_id = $subcategory->category_id;
        $this->selected_id = $subcategory->id;

        // $this->categories = Category::all();

        $this->createMode = false;
        $this->verTabla = false;
        $this->emit('subcategoryEditCreate', 'Editar Subcategoria: '.$subcategory->name);
    }

    
    public function update()
    {  
        abort_if(!Auth::user()->can('categories.edit'), 401);
        $validatedDate = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'int'],
        ]);
      
        if ($this->selected_id) {
            $subcategory = Subcategory::findOrFail($this->selected_id);

            $subcategory->update([
                'name' => $this->name,
                'description' => $this->description,
                'category_id' => $this->category_id,
            ]);
            
            $this->createMode = false;
            $this->emit('subcategoryUpdateStore','Subcategoria actualizada correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('categories.destroy'), 401);
        $subcategory = Subcategory::findOrFail($id);
        $subcategory->delete();
        // $this->resetInputFields();
        $this->emit('subcategoryDelete');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
