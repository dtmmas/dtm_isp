<?php

namespace App\Http\Livewire;

use App\Support\RoleCatalog;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleComponent extends Component
{
    use WithPagination;

    protected $paginationTheme= 'bootstrap';

    //variables de nuestro modelo
    public $name;
    public $description;
    public $role;
    public $permissionsAll;
    public $permissionsRole=[];
    //variables para fucnionalidades
    public $selected_id, $search;
    public $createMode = true; //creando
    public $verTabla = true;
    private $pagination = 15;
    
    public function render()
    {
        RoleCatalog::sync();

        if($this->search!=''){
            $list_roles = Role::withCount('permissions')
            ->where(function($query){
                $query->orwhere('name','LIKE','%'.$this->search.'%');
            })
            ->orderByRaw(RoleCatalog::roleOrderSql('name'))
            ->orderBy('name')
            ->paginate($this->pagination);
        }else{
            $list_roles = Role::withCount('permissions')
            ->orderByRaw(RoleCatalog::roleOrderSql('name'))
            ->orderBy('name')
            ->paginate($this->pagination);
        }

        $this->hydrateUsersCount($list_roles->getCollection());

        $permissionBlocks = $this->availablePermissionBlocks();

        return view('livewire.role-component', compact('list_roles', 'permissionBlocks'));
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
        $this->permissionsRole = [];
        $this->selected_id = null;
        $this->role = null;
        $this->permissionsAll = $this->availablePermissions();
        $this->createMode = true;
        $this->verTabla = true;
        $this->emit('verTablaForm', 'Listado de Roles');
    }

    protected function availablePermissions()
    {
        return Permission::query()
            ->orderByRaw(RoleCatalog::permissionOrderSql('name'))
            ->orderBy('description')
            ->get();
    }

    protected function availablePermissionBlocks()
    {
        return $this->availablePermissions()->groupBy(function ($permission) {
            return RoleCatalog::permissionBlockTitle($permission->name);
        });
    }

    protected function roleRules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->selected_id),
            ],
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    protected function isProtectedRoleName($roleName)
    {
        return in_array($roleName, RoleCatalog::protectedRoleNames(), true);
    }

    protected function findRole($id)
    {
        return Role::with([
            'permissions' => function ($query) {
                $query->select('id');
            }
        ])->findOrFail($id, ['id', 'name', 'description']);
    }

    public function create()
    {
        abort_if(!Auth::user()->can('roles.create'), 401);
        $this->resetInputFields();
        $this->createMode = true;
        $this->verTabla = false;
        $this->emit('verTablaForm','Crear rol personalizado');
    }

    public function store()
    {
        abort_if(!Auth::user()->can('roles.create'), 401);
        $this->validate($this->roleRules());

        $role = Role::create([
            'name' => trim($this->name),
            'description' => trim($this->description),
        ]);

        $this->permissionsRole = array_filter($this->permissionsRole);
        $role->permissions()->sync($this->permissionsRole);
        $this->emit('roleUpdateStore','Rol personalizado creado correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('roles.edit'), 401);
        $this->permissionsAll = $this->availablePermissions();
        $this->role = $role = $this->findRole($id);
        $this->name = $role->name;
        $this->description = $role->description;
        $this->selected_id = $role->id;

        $selectPermissions = $role->permissions->pluck('id')->toArray();

        foreach($selectPermissions as $selectPermission) {
            $this->permissionsRole[$selectPermission] = $selectPermission;
        }

        $this->createMode = false;
        $this->verTabla = false;
        $this->emit('verTablaForm', 'Editar Rol: '.$role->name);
    }

    
    public function update()
    {  
        abort_if(!Auth::user()->can('roles.edit'), 401);
        $this->validate($this->roleRules());

        if ($this->selected_id) {
            $role = $this->findRole($this->selected_id);

            $payload = [
                'description' => trim($this->description),
            ];

            if (!$this->isProtectedRoleName($role->name)) {
                $payload['name'] = trim($this->name);
            }

            $role->update($payload);

            $this->permissionsRole = array_filter($this->permissionsRole);
            $role->permissions()->sync($this->permissionsRole);
            $message = $this->isProtectedRoleName($role->name)
                ? 'Rol predeterminado actualizado correctamente.'
                : 'Rol personalizado actualizado correctamente.';
            $this->emit('roleUpdateStore', $message);
            $this->resetInputFields();
        }
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('roles.destroy'), 401);
        $role = Role::findOrFail($id);
        $usersCount = $this->usersCountForRole($role->id);
        abort_if($this->isProtectedRoleName($role->name), 403, 'Los roles base son fijos y no se pueden eliminar.');
        abort_if($usersCount > 0, 403, 'No puedes eliminar un rol que tiene usuarios asignados.');

        $role->delete();
        $this->emit('roleDelete');
    }

    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onDeleteRow' =>'destroy'
    ];

    protected function hydrateUsersCount($roles)
    {
        $roleIds = $roles->pluck('id')->filter()->values();

        if ($roleIds->isEmpty()) {
            return;
        }

        $counts = DB::table('model_has_roles')
            ->select('role_id', DB::raw('COUNT(*) as total'))
            ->whereIn('role_id', $roleIds)
            ->where('model_type', \App\Models\User::class)
            ->groupBy('role_id')
            ->pluck('total', 'role_id');

        $roles->transform(function ($role) use ($counts) {
            $role->users_count = (int) ($counts[$role->id] ?? 0);
            return $role;
        });
    }

    protected function usersCountForRole($roleId)
    {
        return (int) DB::table('model_has_roles')
            ->where('role_id', $roleId)
            ->where('model_type', \App\Models\User::class)
            ->count();
    }

}
