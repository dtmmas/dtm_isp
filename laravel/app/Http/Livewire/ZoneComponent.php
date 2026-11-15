<?php

namespace App\Http\Livewire;

use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ZoneComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $verTabla = true;
    private $pagination = 15;
    
    public function render()
    {
        if($this->search!=''){
            $list_zones = Zone::where(function($query){
                $query->orwhere('name','LIKE','%'.$this->search.'%');
            })
            ->paginate($this->pagination);
        }else{
            $list_zones = Zone::paginate($this->pagination);
        }
        return view('livewire.zone-component', compact('list_zones'));
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
        $this->emit('verTablaForm', 'Listado de Zonas');
    }

    public function store()
    {
        abort_if(!Auth::user()->can('zones.create'), 401);
        //validar informacion
        $this->validate([
            'name' => ['string', 'max:100'],
        ]);
        
        $zone = Zone::create([
            'name' => $this->name,
        ]);
        
        $this->emit('zoneUpdateStore','Zona creada correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('zones.edit'), 401);
        $this->zone = $zone = Zone::findOrFail($id,['id','name']);
        $this->name = $zone->name;
        $this->selected_id = $zone->id;

        $this->createMode = false;
        $this->verTabla = false;
        $this->emit('zoneEditCreate', 'Editar Zona: '.$zone->name);
    }

    
    public function update()
    {  
        abort_if(!Auth::user()->can('zones.edit'), 401);
        $validatedDate = $this->validate([
            'name' => [ 'string', 'max:100'],
        ]);
      
        if ($this->selected_id) {
            $zone = Zone::findOrFail($this->selected_id);

            $zone->update([
                'name' => $this->name,
            ]);
            
            $this->createMode = false;
            $this->emit('zoneUpdateStore','Zona actualizada correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('zones.destroy'), 401);
        $zone = Zone::findOrFail($id);
        $zone->delete();
        // $this->resetInputFields();
        $this->emit('zoneDelete');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
