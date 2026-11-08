<?php

namespace App\Http\Livewire\Collector;
use App\Models\User;
use App\Models\Zone;
use App\Support\RoleCatalog;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CollectorComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    public $lastname;
    public $dni;
    public $address;
    public $phone;
    public $email;
    public $password;
    public $clave;
    public $collector;
    public $zonesAll;
    public $zonesCollector = [];

    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    private $pagination = 15;
    public $editZoneMode = false;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string',
            'lastname' => 'required|string',
            'dni' => 'required|string|unique:users',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:5', //confirmed recibe un dato: password_confirmation 
        ];

        if(!$this->createMode){ ///esta editando
            $rules['dni'] =  'required|string|unique:users,dni,'.$this->selected_id;
            $rules['email'] =  'required|string|email|unique:users,email,'.$this->selected_id;
            $rules['password'] =  '';
            if($this->clave!=''){
                $rules['clave'] =  'string|min:5';
            }
        }

        return $rules;
    }

    public function render()
    {
        $list_collectors = User::collectors()
            ->when($this->search != '', function ($query) {
                $query->where(function ($searchQuery) {
                    applyFlexibleSearch($searchQuery, $this->search, userSearchExpressions('users'));
                });
            })
            ->paginate($this->pagination);

        return view('livewire.collector.collector-component', compact('list_collectors'));
    }
    
    public function updatingSearch()
    {
        //otra opcion seria $this->resetPage();
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->lastname = '';
        $this->email = '';
        $this->dni = '';
        $this->address = '';
        $this->phone = '';
        $this->clave = '';
        $this->password = '';
        $this->selected_id = null;
        $this->createMode = true;
        $this->zonesCollector = [];
    }

    public function store()
    {
        abort_if(!Auth::user()->can('collectors.create'), 401);
        //validar informacion
        $this->validate();
        
        $collector = User::create([
            'name' => $this->name,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'dni' => $this->dni,
            'address' => $this->address,
            'phone' => $this->phone,
            'password' => Hash::make($this->password)
        ]);
        
        $collector->assignRole(RoleCatalog::ROLE_COBRADOR);
        $collector->syncLegacyCollectorFlag();

        $this->resetInputFields();
        $this->emit('collectorUpdateStore','Cobrador creado correctamente.'); // Close model to using to jquery
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('collectors.edit'), 401);
        $collector = User::collectors()->findOrFail($id, ['id','name','lastname','email','dni','address','phone']);
        $this->name = $collector->name;
        $this->lastname = $collector->lastname;
        $this->email = $collector->email;
        $this->dni = $collector->dni;
        $this->address = $collector->address;
        $this->phone = $collector->phone;
        
        $this->selected_id = $collector->id;
        $this->createMode = false;
        $this->emit('collectorEdit');
    }

    
    public function update()
    {   
        abort_if(!Auth::user()->can('collectors.edit'), 401);
        $this->validate();
      
        if ($this->selected_id) {
            $collector = User::collectors()->findOrFail($this->selected_id);

            $collector->update([
                'name' => $this->name,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'dni' => $this->dni,
                'address' => $this->address,
                'phone' => $this->phone
            ]);

            if($this->clave!=''){
                $this->validate(['clave' => ['required', 'string', 'min:5', 'max:255']]);
                $collector->update([
                    'password' => Hash::make($this->clave)
                ]);
            }

            $this->createMode = false;
            $this->emit('collectorUpdateStore','Cobrador actualizado correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('collectors.destroy'), 401);
        $collector = User::collectors()->findOrFail($id);
        $collector->delete();
        $this->resetInputFields();
        $this->emit('collectorDelete');
    }

    public function editZone($id)
    {   
        abort_if(!Auth::user()->can('collectors.editzone'), 401);
        $this->editZoneMode = true;
        $this->selected_id = $id;
        $collector = User::collectors()->findOrFail($id);
        $this->user = $collector;

        $selectPermissions = $collector->zones()->pluck('id')->toArray();
  
        foreach($selectPermissions as $selectPermission) {
            $this->zonesCollector[$selectPermission] = $selectPermission;
        }

        $this->zonesAll = Zone::all();
        $this->emit('zoneCollectorEdit', 'Zones usuario: '.$collector->name_complete);
    }

    public function updateZone()
    {
        abort_if(!Auth::user()->can('collectors.editzone'), 401);
        $collector = User::collectors()->findOrFail($this->selected_id);
        $this->zonesCollector = array_filter($this->zonesCollector);
        $collector->zones()->sync($this->zonesCollector);
        $this->resetInputFields();
        $this->emit('zoneCollectorUpdate','Zones asignados correctamente');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
