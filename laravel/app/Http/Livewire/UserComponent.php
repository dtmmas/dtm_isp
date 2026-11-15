<?php

namespace App\Http\Livewire;

use App\Support\RoleCatalog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserComponent extends Component
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
    public $user;
    public $selectedRole;

    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    private $pagination = 15;
    public $editRoleMode = false;

    public function mount()
    {
        RoleCatalog::sync();
    }

    public function render()
    {
        RoleCatalog::sync();

        if($this->search!=''){
            $list_users = User::with('roles')
            ->where('id','!=','1') 
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', RoleCatalog::ROLE_CLIENTE);
            })
            ->where(function($query){
                applyFlexibleSearch($query, $this->search, userSearchExpressions('users'));
            })
            ->paginate($this->pagination);
        }else{
            $list_users = User::with('roles')
            ->where('id','!=','1') 
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', RoleCatalog::ROLE_CLIENTE);
            })
            ->paginate($this->pagination);
        }
        $roleBlocks = $this->availableRoleBlocks();

        return view('livewire.user-component', compact('list_users', 'roleBlocks'));
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
        $this->selectedRole = '';
        $this->editRoleMode = false;
        $this->user = null;
    }

    protected function availableRoles()
    {
        return Role::where('name', '!=', RoleCatalog::ROLE_CLIENTE)
            ->orderByRaw(RoleCatalog::roleOrderSql('name'))
            ->orderBy('name')
            ->get();
    }

    protected function availableRoleBlocks()
    {
        return $this->availableRoles()->groupBy(function ($role) {
            return in_array($role->name, RoleCatalog::protectedRoleNames(), true)
                ? 'Roles predeterminados'
                : 'Roles personalizados';
        });
    }

    protected function validateSelectedRole($fieldName = 'selectedRole')
    {
        $assignableRoleNames = $this->availableRoles()->pluck('name')->values()->all();

        return $this->validate([
            $fieldName => ['required', Rule::in($assignableRoleNames)],
        ]);
    }

    protected function syncLegacyCollectorFlag(User $user)
    {
        $user->syncLegacyCollectorFlag();
    }

    protected function ensureDeletable(User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'No puedes eliminar tu propio usuario.');
        abort_if($user->id == 1, 403, 'No puedes eliminar el administrador principal.');

        if ($user->hasRole(RoleCatalog::ROLE_ADMIN)) {
            $admins = User::role(RoleCatalog::ROLE_ADMIN)
                ->whereNull('deleted_at')
                ->count();

            abort_if($admins <= 1, 403, 'Debe existir al menos un administrador activo.');
        }
    }

    public function store()
    {
        abort_if(!Auth::user()->can('users.create'), 401);
        //validar informacion
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'dni' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:5', 'max:255']
        ]);
        $this->validateSelectedRole();
        
        $user = User::create([
            'name' => $this->name,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'dni' => $this->dni,
            'address' => $this->address,
            'phone' => $this->phone,
            'password' => Hash::make($this->password)
        ]);
        $user->syncRoles([$this->selectedRole]);
        $this->syncLegacyCollectorFlag($user);

        $this->resetInputFields();
        $this->emit('userUpdateStore','Usuario creado correctamente.'); // Close model to using to jquery
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('users.edit'), 401);
        $user = User::findOrFail($id,['id','name','lastname','email','dni']);
        $this->name = $user->name;
        $this->lastname = $user->lastname;
        $this->email = $user->email;
        $this->dni = $user->dni;
        $this->address = $user->address;
        $this->phone = $user->phone;
        
        $this->selected_id = $user->id;
        $this->createMode = false;
        $this->emit('userEdit');
    }

    
    public function update()
    {   
        abort_if(!Auth::user()->can('users.edit'), 401);
        $validatedDate = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->selected_id)],
            'dni' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->selected_id)],
        ]);
      
        if ($this->selected_id) {
            $user = User::findOrFail($this->selected_id);

            $user->update([
                'name' => $this->name,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'dni' => $this->dni,
                'address' => $this->address,
                'phone' => $this->phone,
            ]);

            if($this->clave!=''){
                $this->validate(['clave' => ['required', 'string', 'min:5', 'max:255']]);
                $user->update([
                    'password' => Hash::make($this->clave)
                ]);
            }

            $this->createMode = false;
            $this->emit('userUpdateStore','Usuario actualizado correctamente.');
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('users.destroy'), 401);
        $user = User::findOrFail($id);
        $this->ensureDeletable($user);
        $user->delete();
        $this->resetInputFields();
        $this->emit('userDelete');
    }

    public function editRole($id)
    {   
        abort_if(!Auth::user()->can('users.editrole'), 401);
        $this->editRoleMode = true;
        $this->selected_id = $id;
        $user = User::with('roles')->findOrFail($id);
        $this->user = $user;
        abort_if($user->id === Auth::id(), 403, 'No puedes cambiar tu propio rol desde esta pantalla.');
        $this->selectedRole = optional($user->roles->first())->name;
        $this->emit('roleUserEdit', 'Roles usuario: '.$user->name_complete);
    }

    public function updateRole()
    {
        abort_if(!Auth::user()->can('users.editrole'), 401);
        $user = User::findOrFail($this->selected_id);
        abort_if($user->id === Auth::id(), 403, 'No puedes cambiar tu propio rol desde esta pantalla.');
        $this->validateSelectedRole();
        if ($user->hasRole(RoleCatalog::ROLE_ADMIN) && $this->selectedRole !== RoleCatalog::ROLE_ADMIN) {
            $admins = User::role(RoleCatalog::ROLE_ADMIN)
                ->whereNull('deleted_at')
                ->count();

            abort_if($admins <= 1, 403, 'Debe existir al menos un administrador activo.');
        }

        $user->syncRoles([$this->selectedRole]);
        $this->syncLegacyCollectorFlag($user);
        $this->resetInputFields();
        $this->emit('roleUserUpdate','Roles asignados correctamente');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

}
