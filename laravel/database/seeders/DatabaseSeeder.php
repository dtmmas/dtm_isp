<?php

namespace Database\Seeders;

use App\Support\RoleCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $tables = [
            'model_has_roles',
            'permissions',
            'roles',
            'role_has_permissions',
            'users',
            'products',
            'categories',
            'zones',
            'nodes',
            'clients',
            'collector_zone',
            'invoices',
            'payments',
            "settings"
        ];
        
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        \App\Models\Setting::create(['name' => 'nombre_empresa','value' =>'', 'type'=>'empresa']);
        \App\Models\Setting::create(['name' => 'direccion_empresa','value' =>'', 'type'=>'empresa']);
        \App\Models\Setting::create(['name' => 'telefono_empresa','value' =>'', 'type'=>'empresa']);
        \App\Models\Setting::create(['name' => 'nit_empresa','value' =>'', 'type'=>'empresa']);
        \App\Models\Setting::create(['name' => 'slogan_empresa','value' =>'', 'type'=>'empresa']);
        \App\Models\Setting::create(['name' => 'logo_empresa','value' =>'', 'type'=>'empresa']);

        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'lastname' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345')
        ]);
        
        \App\Models\Product::factory(2)->create();
        \App\Models\Zone::factory(5)->create()->each(function($zone){
            $zone->nodes()->save(\App\Models\Node::factory()->make());
        });        

        // \App\Models\Category::factory(25)->create();
        // \App\Models\Subcategory::factory(25)->create();
        
         //INICIO ROLES
        $roleAdmin = Role::create(['name' => 'Admin',
                            'description' =>'Tiene acceso a todo el sistema']);

        $roleCobrador = Role::create(['name' => 'Cobrador',
                            'description' =>'Solo tiene acceso a su perfil, listado de clientes y el historial de pagos del cliente']);
        
        $roleCliente = Role::create(['name' => 'Cliente',
                            'description' =>'Solo tiene acceso a su perfil y a sus facturas y pagos']);

        \App\Models\User::find(1)->assignRole(RoleCatalog::ROLE_ADMIN);
        \App\Models\User::find(1)->syncLegacyCollectorFlag();
        
        //CREACION DE CLIENTES
        \App\Models\User::factory(5)->create()->each(function($user){
            \App\Models\Client::factory()->create(['user_id'=>$user->id]);
            $user->assignRole(RoleCatalog::ROLE_CLIENTE);
            $user->syncLegacyCollectorFlag();
        });
        
        //CREACION DE COBRADORES 
        \App\Models\User::factory(5)->create(['name'=>'Cobrador '.rand(1,300)])->each(function($user){
            $user->assignRole(RoleCatalog::ROLE_COBRADOR);
            $user->syncLegacyCollectorFlag();
        });

        //PERMISOS
        Permission::create(['name' => 'users.index',
                            'description' =>'Ver listado de usuarios'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'users.create',
                            'description' =>'Crear nuevos usuarios'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'users.edit',
                            'description' =>'Editar usuarios'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'users.destroy',
                            'description' =>'Eliminar usuarios'])->syncRoles([$roleAdmin]);

        //ASIGNACION DE ROLES
        Permission::create(['name' => 'users.editrole',
                            'description' =>'Modificar y asignar roles a un usuario'])->syncRoles([$roleAdmin]);
        //GESTION DE ROLES
        Permission::create(['name' => 'roles.index',
                            'description' =>'Ver listado de roles'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'roles.create',
                            'description' =>'Crear nuevos roles'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'roles.edit',
                            'description' =>'Editar Rol'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'roles.destroy',
                            'description' =>'Eliminar Rol'])->syncRoles([$roleAdmin]);

        //GESTION DE PRODUCTOS
        Permission::create(['name' => 'products.index',
                            'description' =>'Ver listado de productos'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'products.create',
                            'description' =>'Crear nuevos productos'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'products.edit',
                            'description' =>'Editar Producto'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'products.destroy',
                            'description' =>'Eliminar Producto'])->syncRoles([$roleAdmin]);

        //MIS PLANES CONTRATADOS
        Permission::create(['name' => 'products.mine',
                            'description' =>'Ver listado de planes contratados'])->syncRoles([$roleCliente]);

        // Permission::create(['name' => 'products.asigne',
        //                     'description' =>'Ver listado de planes asignados'])->syncRoles([$roleCobrador]);

         //GESTION DE CATEGORIAS
        // Permission::create(['name' => 'categories.index',
        // 'description' =>'Ver listado de categorias y subcategorias'])->syncRoles([$roleAdmin]);

        // Permission::create(['name' => 'categories.create',
        //         'description' =>'Crear nuevos categorias y subcategorias'])->syncRoles([$roleAdmin]);

        // Permission::create(['name' => 'categories.edit',
        //         'description' =>'Editar Categora  y subcategori'])->syncRoles([$roleAdmin]);

        // Permission::create(['name' => 'categories.destroy',
        //         'description' =>'Eliminar Categora  y subcategori'])->syncRoles([$roleAdmin]);

        //GESTION DE ZONAS/NODOS
        Permission::create(['name' => 'zones.index',
                            'description' =>'Ver listado de zonas'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'zones.create',
                            'description' =>'Crear nuevas zonas'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'zones.edit',
                            'description' =>'Editar Zona'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'zones.destroy',
                            'description' =>'Eliminar Zona'])->syncRoles([$roleAdmin]);

        //GESTION DE CLIENTES
        Permission::create(['name' => 'clients.index',
                            'description' =>'Ver listado de clientes'])->syncRoles([$roleAdmin, $roleCobrador]);

        Permission::create(['name' => 'clients.create',
                            'description' =>'Crear nuevos clientes'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'clients.edit',
                            'description' =>'Editar clientes'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'clients.destroy',
                            'description' =>'Eliminar clientes'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'clients.search',
                            'description' =>'Buscar cliente'])->syncRoles([$roleCobrador]);

        Permission::create(['name' => 'clients.importar',
                            'description' =>'Importar clientes'])->syncRoles([$roleAdmin]);

        //GESTION DE COBRADORES
        Permission::create(['name' => 'collectors.index',
                            'description' =>'Ver listado de cobradores'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'collectors.create',
                            'description' =>'Crear nuevos cobradores'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'collectors.edit',
                            'description' =>'Editar cobradores'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'collectors.destroy',
                            'description' =>'Eliminar cobradores'])->syncRoles([$roleAdmin]);

        //GESTION DE FACTURAS
        Permission::create(['name' => 'invoices.index',
        'description' =>'Ver listado de facturas'])->syncRoles([$roleAdmin,$roleCobrador, $roleCliente]);

        Permission::create(['name' => 'invoices.create',
        'description' =>'Puede crear facturas '])->syncRoles([$roleAdmin]);
        
        //GESTION DE PAGOS
        Permission::create(['name' => 'payments.index',
            'description' =>'Ver listado de pagos'])->syncRoles([$roleAdmin, $roleCobrador, $roleCliente]);

        Permission::create(['name' => 'payments.create',
            'description' =>'Reportar pagos'])->syncRoles([$roleAdmin, $roleCobrador, $roleCliente]);

        Permission::create(['name' => 'payments.process',
            'description' =>'Aprobar o rechazar pagos'])->syncRoles([$roleAdmin]);
        
        Permission::create(['name' => 'payments.advancement',
            'description' =>'Puede agregar pagos adelantados'])->syncRoles([$roleAdmin]);

        //REPORTES
        Permission::create(['name' => 'report.index',
            'description' =>'Ver reportes'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'report.clients',
            'description' =>'Ver reportes de clientes'])->syncRoles([$roleAdmin]);

        Permission::create(['name' => 'report.invoices',
            'description' =>'Ver reportes de facturas'])->syncRoles([$roleAdmin]);


        //SETTINGS
        Permission::create(['name' => 'setting.index',
        'description' =>'Realizar las configuraciones del sistema'])->syncRoles([$roleAdmin]);

        RoleCatalog::sync();
    }
}
