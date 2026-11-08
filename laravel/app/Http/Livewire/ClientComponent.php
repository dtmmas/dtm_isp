<?php

namespace App\Http\Livewire;
use App\Models\Client;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\FunctionGeneral;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Zone;
use Illuminate\Support\Facades\Auth;

class ClientComponent extends Component
{
    use WithPagination, FunctionGeneral;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    public $lastname;
    public $dni;
    public $address;
    public $phone;
    public $email;
    public $ip;
    public $clave_wifi;
    public $reference;
    public $password;
    public $clave;
    public $client;
    public $zonesAll;
    public $zonesClient = [];

    public $clienteMora;
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    private $pagination = 15;
    public $editZoneMode = false;
    public $searchCollector = false;
    protected $queryString = ['search'=> ['except' => '']];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string',
            'lastname' => 'required|string',
            'dni' => 'required|string|unique:users',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'reference' => 'required|string',
            'password' => 'required|string|min:5', //confirmed recibe un dato: password_confirmation 
        ];

        if(!$this->createMode){ //esta editando
            $rules['dni'] =  'required|string|unique:users,dni,'.$this->client->user_id;
            $rules['email'] =  'required|string|email|unique:users,email,'.$this->client->user_id;
            $rules['password'] =  '';
            if($this->clave!=''){
                $rules['clave'] =  'string|min:5';
            }
        }

        return $rules;
    }

    public function mount($searchCollector)
    {
        $this->searchCollector = $searchCollector;
    }

    public function render()
    {   
        if(Auth::user()->hasRole('Cobrador')){
            if($this->searchCollector){
                    $list_clients = Client::when($this->search != '', function ($query) {
                        $query->where(function ($searchQuery) {
                            $searchQuery->whereHas('user', function ($userQuery) {
                                applyFlexibleSearch($userQuery, $this->search, userSearchExpressions('users'));
                            })->orWhere(function ($referenceQuery) {
                                applyFlexibleSearch($referenceQuery, $this->search, ['clients.reference']);
                            });
                        });
                    })
                    ->paginate($this->pagination);
            }else{
                $collector = Auth::user();
                $list_clients =  $collector->zones()
                ->has('nodes.client_products.client')
                ->with('nodes.client_products.client.user')
                ->whereHas('nodes.client_products.client.user', function($query){
                    if($this->search!=''){
                        applyFlexibleSearch($query, $this->search, userSearchExpressions('users'));
                    }
                    })
                ->whereHas('nodes.client_products.client',function($query){
                    if($this->search!=''){
                        applyFlexibleSearch($query, $this->search, ['clients.reference']);
                    }
                })
                ->get()
                ->pluck('nodes')->collapse()
                ->pluck('client_products')->collapse()
                ->pluck('client')->unique()->values();

                $list_clients = $list_clients->sortByDesc('created_at');
                $list_clients =$this->mypaginate($list_clients);
            }
            
        }else{
            $list_clients = Client::when($this->search != '', function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->whereHas('user', function ($userQuery) {
                        applyFlexibleSearch($userQuery, $this->search, userSearchExpressions('users'));
                    })->orWhere(function ($referenceQuery) {
                        applyFlexibleSearch($referenceQuery, $this->search, ['clients.reference']);
                    });
                });
            })
            ->paginate($this->pagination);
        }

        return view('livewire.client-component', compact('list_clients'));
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
        $this->ip = '';
        $this->clave_wifi = '';
        $this->reference = '';
        $this->dni = '';
        $this->address = '';
        $this->phone = '';
        $this->clave = '';
        $this->password = '';
        $this->selected_id = null;
        $this->createMode = true;
        $this->zonesClient = [];
        $this->clienteMora = null;
    }

    public function store()
    {
        abort_if(!Auth::user()->can('clients.create'), 401);
        //validar informacion
        $this->validate();
        
        $user = User::create([
            'name' => $this->name,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'dni' => $this->dni,
            'address' => $this->address,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
        ]);
        
        $user->client()->create([
            'ip' => $this->ip,
            'clave_wifi' => $this->clave_wifi,
            'reference' => $this->reference
        ]);
        
        $user->assignRole('Cliente');
        $user->syncLegacyCollectorFlag();

        $this->resetInputFields();
        $this->emit('clientUpdateStore','Cliente creado correctamente.'); // Close model to using to jquery
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('clients.edit'), 401);
        $this->client = $client = Client::with('user')->findOrFail($id);
        $this->name = $client->user->name;
        $this->lastname = $client->user->lastname;
        $this->email = $client->user->email;
        $this->ip = $client->ip;
        $this->clave_wifi = $client->clave_wifi;
        $this->reference = $client->reference;
        $this->dni = $client->user->dni;
        $this->address = $client->user->address;
        $this->phone = $client->user->phone;
        
        $this->selected_id = $client->id;
        $this->createMode = false;
        $this->emit('clientEdit');
    }

    public function cargarDataRecordatorio($id){
        abort_if(!Auth::user()->can('clients.send.recordatorio'), 401);
        $this->clienteMora = null;
        $this->selected_id = $id;
        $this->clienteMora = Client::with('client_products','client_products.invoices','client_products.product','user')
            ->where('id','=',$id)
            ->first();

        $this->emit('cargarDataRecordatorioShow');
        $this->dispatchBrowserEvent('open-recordatorio-modal');
    }

    public function cargarDataAvisoMora($id){
        abort_if(!Auth::user()->can('clients.send.recordatorio'), 401);
        $this->clienteMora = null;
        $this->selected_id = $id;
        $this->clienteMora = Client::with('client_products','client_products.invoices','client_products.product','user')
            ->where('id','=',$id)
            ->first();

        $this->emit('cargarDataAvisoMoraShow');
        $this->dispatchBrowserEvent('open-aviso-mora-modal');
    }

    public function update()
    {   
        abort_if(!Auth::user()->can('clients.edit'), 401);
        $this->validate();
      
        if ($this->selected_id) {
            $client = Client::findOrFail($this->selected_id);

            $user = User::findOrFail($client->user_id);
            $user->update([
                'name' => $this->name,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'dni' => $this->dni,
                'address' => $this->address,
                'phone' => $this->phone
            ]);

            if($this->clave!=''){
                $this->validate(['clave' => ['required', 'string', 'min:5', 'max:255']]);
                $user->update([
                    'password' => Hash::make($this->clave)
                ]);
            }

            $client->update([
                'ip' => $this->ip,
                'clave_wifi' => $this->clave_wifi,
                'reference' => $this->reference
            ]);

            $this->createMode = false;
            $this->emit('clientUpdateStore','Cliente actualizado correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('clients.destroy'), 401);
        $client = Client::findOrFail($id);
        $client->delete();
        $this->resetInputFields();
        $this->emit('clientDelete');
    }

    public function editZone($id)
    {   
        abort_if(!Auth::user()->can('clients.editzone'), 401);
        $this->editZoneMode = true;
        $this->selected_id = $id;
        $client = Client::findOrFail($id);
        $this->user = $client;

        $selectPermissions = $client->zones()->pluck('id')->toArray();
  
        foreach($selectPermissions as $selectPermission) {
            $this->zonesClient[$selectPermission] = $selectPermission;
        }

        $this->zonesAll = Zone::all();
        $this->emit('zoneClientEdit', 'Zones usuario: '.$client->name_complete);
    }

    public function updateZone()
    {
        abort_if(!Auth::user()->can('clients.editzone'), 401);
        $client = Client::findOrFail($this->selected_id);
        $this->zonesClient = array_filter($this->zonesClient);
        $client->zones()->sync($this->zonesClient);
        $this->resetInputFields();
        $this->emit('zoneClientUpdate','Zones asignados correctamente');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
