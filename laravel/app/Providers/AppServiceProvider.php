<?php

namespace App\Providers;

use App\Models\Payment;
use App\Observers\PaymentObserver;
use App\Support\RoleCatalog;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->bind('path.public', function() {
        //     return realpath(base_path().'/../');
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //OBSERVEERS
        Payment::observe(PaymentObserver::class);
        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            RoleCatalog::sync();
        }
        //FIN OBSERVERS
        View::composer('navigation-menu', function ($view)
        {
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            $authUser = auth()->user();
            $MenuSistema = array(
                        // array('title' => 'Dashboard', 'route' => 'dashboard','icon' => 'fas fa-tachometer-alt', 'can'=>'dashboard'),
                        array('title' => 'Usuarios', 'route' => 'users.index','icon' => 'fas fa-users-cog', 'can'=>'users.index'),
                        array('title' => 'Roles', 'route' => 'roles.index','icon' => 'fas fa-user-shield', 'can'=>'roles.index'),
                        array('title' => 'Planes', 'route' => 'products.index','icon' => 'fas fa-cubes', 'can'=>'products.index'),
                        array(
                            'title' => 'Mis Planes',
                            'route' => 'products.mine',
                            'icon' => 'fas fa-box-open',
                            'can' => 'products.mine',
                            'visible' => $authUser ? $authUser->client()->exists() : false,
                        ),
                        // array('title' => 'Planes contratados', 'route' => 'products.asigne','icon' => 'fas fa-cubes', 'can'=>'products.asigne'),
                        // array('title' => 'Categorias', 'route' => 'categories.index','icon' => 'fas fa-sitemap', 'can'=>'categories.index'),
                        array('title' => 'Zonas/Nodos', 'route' => 'zones.index','icon' => 'fas fa-map-marker-alt', 'can'=>'zones.index'),
                        array('title' => 'Buscar Cliente', 'route' => 'clients.search','icon' => 'fas fa-search', 'can'=>'clients.search'),
                        array('title' => 'Clientes', 'route' => 'clients.index','icon' => 'fas fa-users', 'can'=>'clients.index'),
                        array('title' => 'Mapa ISP', 'route' => '#','icon' => 'fas fa-map', 'can'=>'clients.map', 'submenu'=>[
                            array('title' => __('Vista general'), 'route' => 'clients.map', 'icon' => 'fas fa-map-marked-alt', 'can'=>'clients.map'),
                            array('title' => __('Activos de red'), 'route' => 'map.assets', 'icon' => 'fas fa-network-wired', 'can'=>'map.assets'),
                            array('title' => __('Rutas de fibra'), 'route' => 'map.fiber', 'icon' => 'fas fa-project-diagram', 'can'=>'map.fiber'),
                            array('title' => __('Coberturas'), 'route' => 'map.coverage', 'icon' => 'fas fa-broadcast-tower', 'can'=>'map.coverage'),
                        ]),
                        array('title' => 'Facturas', 'route' => 'invoices.index','icon' => 'fas fa-file-alt', 'can'=>'invoices.index'),
                        array('title' => 'Pagos', 'route' => 'payments.index','icon' => 'fas fa-dollar-sign', 'can'=>'payments.index'),
                        array('title' => 'Pagos Pendientes', 'route' => 'payments.pending','icon' => 'fas fa-hourglass-half', 'can'=>'payments.index'),
                        array('title' => 'Tickets', 'route' => '#','icon' => 'fas fa-ticket-alt', 'can'=>'tickets.index', 'submenu'=>[
                            array('title' => __('Listado general'), 'route' => 'tickets.index', 'icon' => 'fas fa-list-ul', 'can'=>'tickets.index'),
                            array('title' => __('Tipos de ticket'), 'route' => 'tickets.types', 'icon' => 'fas fa-tags', 'can'=>'tickets.index'),
                        ]),
                        array('title' => 'Reportes', 'route' => '#','icon' => 'fas fa-chart-line', 'can'=>'report.index', 'submenu'=>[
                            array('title' => __('Clientes'), 'route' => 'report.clients', 'icon' => 'fas fa-users', 'can'=>'report.clients'),
                            array('title' => __('Clientes Morosos'), 'route' => 'report.clients.moroso', 'icon' => 'fas fa-user-times', 'can'=>'report.clients.moroso'),
                            array('title' => __('Facturas'), 'route' => 'report.invoices', 'icon' => 'fas fa-file-alt', 'can'=>'report.invoices'),
                            array('title' => __('Planes por Zona'), 'route' => 'report.plans.zone', 'icon' => 'fas fa-sitemap', 'can'=>'report.invoices'),
                        ]),
                        array('title' => __('Configuración'), 'route' => 'setting.index', 'icon' => 'fas fa-sliders-h', 'can'=>'setting.index'),





                    );

            $MenuSistema = array_values(array_filter($MenuSistema, function ($item) {
                return !array_key_exists('visible', $item) || $item['visible'];
            }));

            $view->with('MenuSistema',$MenuSistema);
        });
    }
}
