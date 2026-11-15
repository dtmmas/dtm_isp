<?php

namespace App\Http\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CategoryComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    public $description;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $verTabla = true;
    private $pagination = 15;
    
    public function render()
    {
        if($this->search!=''){
            $list_categories = Category::where(function($query){
                $query->orwhere('name','LIKE','%'.$this->search.'%');
            })
            ->paginate($this->pagination);
        }else{
            $list_categories = Category::paginate($this->pagination);
        }
        return view('livewire.category-component', compact('list_categories'));
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
        $this->emit('verTablaForm', 'Listado de Categorias');
    }

    public function store()
    {
        abort_if(!Auth::user()->can('categories.create'), 401);
        //validar informacion
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ]);
        
        $category = Category::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);
        
        $this->emit('categoryUpdateStore','Categoria creada correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('categories.edit'), 401);
        $this->category = $category = Category::findOrFail($id,['id','name','description']);
        $this->name = $category->name;
        $this->description = $category->description;
        $this->selected_id = $category->id;

        $this->createMode = false;
        $this->verTabla = false;
        $this->emit('categoryEditCreate', 'Editar Categoria: '.$category->name);
    }

    
    public function update()
    {  
        abort_if(!Auth::user()->can('categories.edit'), 401);
        $validatedDate = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ]);
      
        if ($this->selected_id) {
            $category = Category::findOrFail($this->selected_id);

            $category->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            
            $this->createMode = false;
            $this->emit('categoryUpdateStore','Categoria actualizada correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('categories.destroy'), 401);
        $category = Category::findOrFail($id);
        $category->delete();
        // $this->resetInputFields();
        $this->emit('categoryDelete');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
