<?php

namespace App\Http\Livewire;

use App\Models\Node;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class NodeComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    public $zone_id;
    public $zones;
    public $node;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $verTabla = true;
    private $pagination = 15;
    
    public function mount($zone_id)
    {
        $this->zone_id = $zone_id;
    }
    public function render()
    {
        if($this->search!=''){
            $list_nodes = Node::where('zone_id', $this->zone_id)
            ->where(function($query){
                $query->orwhere('name','LIKE','%'.$this->search.'%');
            })
            ->paginate($this->pagination);
        }else{
            $list_nodes = Node::where('zone_id', $this->zone_id)
            ->paginate($this->pagination);
        }
        return view('livewire.node-component', compact('list_nodes'));
    }
    
    public function updatingSearch()
    {
        //otra opcion seria $this->resetPage();
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->selected_id = null;
        $this->createMode = true;
        $this->verTabla = true;
        $this->emit('verTablaForm', 'Listado de Nodos');
    }

    public function create()
    {
        abort_if(!Auth::user()->can('roles.create'), 401);
        $this->createMode = true;
    }

    public function store()
    {
        abort_if(!Auth::user()->can('zones.create'), 401);
        //validar informacion
        $this->validate([
            'name' => [ 'string', 'max:255'],
            'zone_id' => ['required', 'int'],
        ]);
        
        $this->node = Node::create([
            'name' => $this->name,
            'zone_id' => $this->zone_id,
        ]);
        
        $this->emit('nodeUpdateStore','Nodo creado correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('zones.edit'), 401);
        $this->node = $node = Node::findOrFail($id,['id','name','zone_id']);
        $this->name = $node->name;
        $this->zone_id = $node->zone_id;
        $this->selected_id = $node->id;

        // $this->zones = Category::all();

        $this->createMode = false;
        $this->verTabla = false;
        $this->emit('nodeEditCreate', 'Editar Nodo: '.$node->name);
    }

    
    public function update()
    {  
        abort_if(!Auth::user()->can('zones.edit'), 401);
        $validatedDate = $this->validate([
            'name' => [ 'string', 'max:255'],
            'zone_id' => ['required', 'int'],
        ]);
      
        if ($this->selected_id) {
            $node = Node::findOrFail($this->selected_id);

            $node->update([
                'name' => $this->name,
                'zone_id' => $this->zone_id,
            ]);
            
            $this->createMode = false;
            $this->emit('nodeUpdateStore','Nodo actualizado correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('zones.destroy'), 401);
        $node = Node::findOrFail($id);
        $node->delete();
        // $this->resetInputFields();
        $this->emit('nodeDelete');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
