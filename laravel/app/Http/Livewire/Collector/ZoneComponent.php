<?php

namespace App\Http\Livewire\Collector;

use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ZoneComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    public $collector;
    public $zones;
    public $zone_id;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $verTabla = true;
    private $pagination = 15;
    
    public function mount($collector)
    {
        $this->collector = $collector;
    }

    public function render()
    {
        if($this->search!=''){
            $list_zones = $this->collector->zones()->where(function($query){
                $query->orwhere('name','LIKE','%'.$this->search.'%');
            })
            ->paginate($this->pagination);
        }else{
            $list_zones = $this->collector->zones()->paginate($this->pagination);
        }
        
        $this->zones = Zone::doesntHave('collectors')->select(DB::raw("CONCAT(id,' / ',name) AS name_code"), 'id')->pluck('name_code','id');
        // en caso de que se desee poder asociar la misma zona a varios cobradores mas no al mismo
        // $this->zones = Zone::whereDoesntHave('collectors', function (Builder $query)
        // {
        //     $query->where('collector_id', $this->collector->id);
        // })->pluck('name','id');

        return view('livewire.collector.zone-component', compact('list_zones'));
    }
    
    public function updatingSearch()
    {
        //otra opcion seria $this->resetPage();
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->zone_id='';
        $this->selected_id = null;
        $this->createMode = true;
        $this->verTabla = true;
        $this->emit('verTablaForm', 'Listado de Zonas');
    }
    
    public function update()
    {  
        abort_if(!Auth::user()->can('collectors.edit'), 401);

        if ($this->zone_id) {
            $this->collector->zones()->syncWithoutDetaching([$this->zone_id]);
            $this->emit('zoneUpdate','Zona asociada correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('collectors.edit'), 401);
        $this->collector->zones()->detach($id);
        $this->emit('zoneDelete');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
