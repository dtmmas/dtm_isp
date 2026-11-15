<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleCatalog
{
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_SECRETARIA = 'Secretaria';
    public const ROLE_TECNICO = 'Tecnico';
    public const ROLE_COBRADOR = 'Cobrador';
    public const ROLE_CLIENTE = 'Cliente';

    public static function roleDefinitions(): array
    {
        return [
            self::ROLE_ADMIN => 'Acceso total a la administracion del sistema.',
            self::ROLE_SECRETARIA => 'Gestion operativa de clientes, facturas, pagos y reportes.',
            self::ROLE_TECNICO => 'Seguimiento tecnico mediante mapa operativo y gestion de tickets.',
            self::ROLE_COBRADOR => 'Cobro, consulta de clientes asignados, facturas y pagos.',
            self::ROLE_CLIENTE => 'Consulta de sus planes, facturas y pagos.',
        ];
    }

    public static function protectedRoleNames(): array
    {
        return array_keys(self::roleDefinitions());
    }

    public static function internalRoleNames(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SECRETARIA,
            self::ROLE_TECNICO,
            self::ROLE_COBRADOR,
        ];
    }

    public static function rolePriority(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SECRETARIA,
            self::ROLE_TECNICO,
            self::ROLE_COBRADOR,
            self::ROLE_CLIENTE,
        ];
    }

    public static function roleOrderSql(string $column = 'name'): string
    {
        $priority = self::rolePriority();
        $cases = collect($priority)
            ->values()
            ->map(function ($roleName, $index) use ($column) {
                $escapedRoleName = str_replace("'", "''", $roleName);
                return "WHEN {$column} = '{$escapedRoleName}' THEN ".($index + 1);
            })
            ->implode(' ');

        return "CASE {$cases} ELSE 999 END";
    }

    public static function permissionDefinitions(): array
    {
        return [
            'users.index' => 'Ver listado de usuarios',
            'users.create' => 'Crear nuevos usuarios',
            'users.edit' => 'Editar usuarios',
            'users.destroy' => 'Eliminar usuarios',
            'users.editrole' => 'Modificar y asignar roles a un usuario',
            'roles.index' => 'Ver catalogo de roles',
            'roles.create' => 'Crear nuevos roles',
            'roles.edit' => 'Editar roles',
            'roles.destroy' => 'Eliminar roles',
            'products.index' => 'Ver listado de productos',
            'products.create' => 'Crear nuevos productos',
            'products.edit' => 'Editar productos',
            'products.destroy' => 'Eliminar productos',
            'products.mine' => 'Ver listado de planes contratados',
            'products.asigne' => 'Ver listado de planes asignados',
            'zones.index' => 'Ver listado de zonas',
            'zones.create' => 'Crear nuevas zonas',
            'zones.edit' => 'Editar zonas y nodos',
            'zones.destroy' => 'Eliminar zonas y nodos',
            'categories.index' => 'Ver listado de categorias',
            'categories.create' => 'Crear categorias',
            'categories.edit' => 'Editar categorias',
            'categories.destroy' => 'Eliminar categorias',
            'clients.index' => 'Ver listado de clientes',
            'clients.create' => 'Crear nuevos clientes',
            'clients.edit' => 'Editar clientes',
            'clients.destroy' => 'Eliminar clientes',
            'clients.search' => 'Buscar clientes',
            'clients.map' => 'Ver mapa de clientes',
            'map.assets' => 'Gestionar activos de red del mapa',
            'map.fiber' => 'Gestionar rutas de fibra del mapa',
            'map.coverage' => 'Gestionar coberturas del mapa',
            'clients.importar' => 'Importar clientes',
            'clients.editzone' => 'Asignar zonas a clientes',
            'clients.send.recordatorio' => 'Enviar recordatorios por WhatsApp a clientes',
            'collectors.index' => 'Ver listado de cobradores',
            'collectors.create' => 'Crear nuevos cobradores',
            'collectors.edit' => 'Editar cobradores',
            'collectors.destroy' => 'Eliminar cobradores',
            'collectors.editzone' => 'Asignar zonas a cobradores',
            'invoices.index' => 'Ver listado de facturas',
            'invoices.create' => 'Crear facturas',
            'payments.index' => 'Ver listado de pagos',
            'payments.create' => 'Reportar pagos',
            'payments.process' => 'Aprobar o rechazar pagos',
            'payments.destroy' => 'Eliminar pagos',
            'payments.advancement' => 'Registrar pagos adelantados',
            'tickets.index' => 'Ver y gestionar tickets por plan',
            'report.index' => 'Ver modulo de reportes',
            'report.clients' => 'Ver reportes de clientes',
            'report.clients.moroso' => 'Ver reportes de clientes morosos',
            'report.invoices' => 'Ver reportes de facturas y planes por zona',
            'setting.index' => 'Realizar configuraciones del sistema',
        ];
    }

    public static function permissionBlockDefinitions(): array
    {
        return [
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'products' => 'Planes',
            'zones' => 'Zonas y Nodos',
            'categories' => 'Categorias',
            'clients' => 'Clientes',
            'map' => 'Mapa ISP',
            'collectors' => 'Cobradores',
            'invoices' => 'Facturacion',
            'payments' => 'Pagos',
            'tickets' => 'Tickets',
            'report' => 'Reportes',
            'setting' => 'Configuracion',
            'other' => 'Otros',
        ];
    }

    public static function permissionBlockKey(string $permissionName): string
    {
        $prefix = explode('.', $permissionName)[0] ?? 'other';

        return array_key_exists($prefix, self::permissionBlockDefinitions())
            ? $prefix
            : 'other';
    }

    public static function permissionBlockTitle(string $permissionName): string
    {
        return self::permissionBlockDefinitions()[self::permissionBlockKey($permissionName)] ?? self::permissionBlockDefinitions()['other'];
    }

    public static function permissionOrderSql(string $column = 'name'): string
    {
        $cases = collect(array_keys(self::permissionBlockDefinitions()))
            ->values()
            ->map(function ($prefix, $index) use ($column) {
                if ($prefix === 'other') {
                    return null;
                }

                $escapedPrefix = str_replace("'", "''", $prefix);
                return "WHEN {$column} LIKE '{$escapedPrefix}.%' THEN ".($index + 1);
            })
            ->filter()
            ->implode(' ');

        return "CASE {$cases} ELSE 999 END";
    }

    public static function permissionMatrix(): array
    {
        return [
            self::ROLE_ADMIN => array_keys(self::permissionDefinitions()),
            self::ROLE_SECRETARIA => [
                'products.index',
                'zones.index',
                'clients.index',
                'clients.create',
                'clients.edit',
                'clients.search',
                'clients.map',
                'map.assets',
                'map.fiber',
                'map.coverage',
                'clients.importar',
                'clients.send.recordatorio',
                'invoices.index',
                'invoices.create',
                'payments.index',
                'payments.create',
                'payments.destroy',
                'tickets.index',
                'report.index',
                'report.clients',
                'report.clients.moroso',
                'report.invoices',
            ],
            self::ROLE_TECNICO => [
                'clients.map',
                'map.assets',
                'map.fiber',
                'map.coverage',
                'tickets.index',
            ],
            self::ROLE_COBRADOR => [
                'clients.index',
                'clients.search',
                'clients.map',
                'invoices.index',
                'payments.index',
                'payments.create',
                'tickets.index',
                'report.index',
                'report.clients.moroso',
            ],
            self::ROLE_CLIENTE => [
                'products.mine',
                'invoices.index',
                'payments.index',
                'payments.create',
            ],
        ];
    }

    public static function sync(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [];
        foreach (self::roleDefinitions() as $name => $description) {
            $role = Role::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );

            if (blank($role->description)) {
                $role->description = $description;
                $role->save();
            }

            $roles[$name] = $role;
        }

        foreach (self::permissionDefinitions() as $name => $description) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );

            if ($permission->description !== $description) {
                $permission->description = $description;
                $permission->save();
            }
        }

        $permissionsByName = Permission::query()->get()->keyBy('name');

        foreach (self::permissionMatrix() as $roleName => $permissionNames) {
            if (!isset($roles[$roleName])) {
                continue;
            }

            $permissionIds = collect($permissionNames)
                ->filter(function ($permissionName) use ($permissionsByName) {
                    return $permissionsByName->has($permissionName);
                })
                ->map(function ($permissionName) use ($permissionsByName) {
                    return $permissionsByName->get($permissionName)->id;
                })
                ->values()
                ->all();

            if ($roles[$roleName]->permissions()->count() === 0) {
                $roles[$roleName]->permissions()->sync($permissionIds);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
