<div>   
    @if($verTabla)
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            @can('roles.create')
            <button type="button" wire:click="create" class="btn btn-primary btnCrearModal float-end">
                Crear Rol
            </button>
            @endcan
        </div>
    </div>

    <div class="alert alert-info">
        Los roles predeterminados del sistema pueden ajustarse en permisos y descripcion. Su nombre base se mantiene fijo para no romper la logica del sistema.
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Permisos</th>
                <th>Usuarios</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($list_roles as $role)
                <tr>
                    <td>{{$role->name}}</td>
                    <td>{{$role->description}}</td>
                    <td>{{$role->permissions_count}}</td>
                    <td>{{$role->users_count}}</td>
                    <td>
                        @if (in_array($role->name, \App\Support\RoleCatalog::protectedRoleNames(), true))
                            <span class="badge bg-secondary">Predeterminado</span>
                        @else
                            <span class="badge bg-success">Personalizado</span>
                        @endif
                    </td>
                    <td style="width: 10px">
                        <div class="btn-group" role="group">
                            @can('roles.edit')
                                <a href="#!" wire:loading.attr="disabled" wire:click="edit({{$role->id}})" type="button" class="btn btn-sm btn-info btn-secondary">
                                    Editar
                                </a>
                            @endcan
                            @if (!in_array($role->name, \App\Support\RoleCatalog::protectedRoleNames(), true))
                                @can('roles.destroy')
                                    <button style="opacity: .8" type="button" class="btn btn-info btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">
                                        <a onclick="EliminarRole({{$role->id}},'{{$role->name}}')" class="dropdown-item" href="#!">Eliminar</a>
                                    </div>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No hay resultados</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div>
        {{ $list_roles->links() }}
    </div>
    @else
    @if($createMode)
    {!! Form::open(['id' => 'FormRole', 'method' => 'POST']) !!}
    @else
    {!! Form::model($role,['id' => 'FormRole', 'method' => 'PUT']) !!}
    @endif

        <div class="alert alert-warning">
            @if (!$createMode && $role && in_array($role->name, \App\Support\RoleCatalog::protectedRoleNames(), true))
                Estas editando un rol predeterminado. Puedes cambiar descripcion y permisos, pero el nombre del rol permanece fijo.
            @else
                Configura el rol, su descripcion y los permisos que tendra disponibles.
            @endif
        </div>

        <div class="form-group">
            {!! Form::label('name', 'Nombre del rol'); !!}
            {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control', 'placeholder'=>'Ej. Supervisor', 'autocomplete'=>'off', 'readonly' => (!$createMode && $role && in_array($role->name, \App\Support\RoleCatalog::protectedRoleNames(), true))]); !!}
            @error('name') <span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            {!! Form::label('description', 'Descripcion'); !!}
            {!! Form::text('description', null, ['wire:model.defer'=> 'description', 'class' => 'form-control', 'placeholder'=>'Describe el alcance del rol', 'autocomplete'=>'off']); !!}
            @error('description') <span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <h2 class="mt-3">Permisos del rol</h2>
        <div class="alert alert-info">
            Los permisos estan organizados por bloques funcionales para facilitar la asignacion y revision del rol.
        </div>
        @forelse ($permissionBlocks as $blockTitle => $blockPermissions)
            <div class="card mt-3">
                <div class="card-header">
                    <strong>{{ $blockTitle }}</strong>
                    <span class="text-muted">({{ $blockPermissions->count() }})</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($blockPermissions as $permission)
                            <div class="col-md-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    {!! Form::checkbox('permissions[]', $permission->id, null, ['wire:model.defer'=>'permissionsRole.'.$permission->id,'class'=>'custom-control-input','id'=>'permission'.$permission->id]) !!}
                                    {!! Form::label('permission'.$permission->id, $permission->description, ['class'=>'custom-control-label']) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-muted">No hay permisos disponibles.</div>
        @endforelse

        <button type="button" wire:click="resetInputFields" class="btn btn-danger mt-2">Cancelar</button>
        @if ($createMode)
        <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary mt-2">Crear Rol</button>
        @else
        <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary mt-2">Guardar Cambios</button>
        @endif

    {!! Form::close() !!}
    @endif

</div>
