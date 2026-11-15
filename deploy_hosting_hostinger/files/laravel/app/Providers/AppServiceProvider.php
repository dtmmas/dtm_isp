<?php

namespace App\Providers;

use App\Models\Payment;
use App\Observers\PaymentObserver;
use Illuminate\Support\ServiceProvider;
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
        //FIN OBSERVERS
        View::composer('navigation-menu', function ($view)
        {
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            $MenuSistema = array(
                        // array('title' => 'Dashboard', 'route' => 'dashboard','icon' => 'fas fa-tachometer-alt', 'can'=>'dashboard'),
                        // array('title' => 'Roles', 'route' => 'roles.index','icon' => 'fas fa-user-secret', 'can'=>'roles.index'),
                        array('title' => 'Planes', 'route' => 'products.index','icon' => 'fas fa-cubes', 'can'=>'products.index'),
                        array('title' => 'Mis Planes', 'route' => 'products.mine','icon' => 'fas fa-box-open', 'can'=>'products.mine'),
                        // array('title' => 'Planes contratados', 'route' => 'products.asigne','icon' => 'fas fa-cubes', 'can'=>'products.asigne'),
                        // array('title' => 'Categorias', 'route' => 'categories.index','icon' => 'fas fa-sitemap', 'can'=>'categories.index'),
                        array('title' => 'Zonas/Nodos', 'route' => 'zones.index','icon' => 'fas fa-map-marker-alt', 'can'=>'zones.index'),
                        array('title' => 'Cobradores', 'route' => 'collectors.index','icon' => 'fas fa-user', 'can'=>'collectors.index'),
                        array('title' => 'Buscar Cliente', 'route' => 'clients.search','icon' => 'fas fa-search', 'can'=>'clients.search'),
                        array('title' => 'Clientes', 'route' => 'clients.index','icon' => 'fas fa-users', 'can'=>'clients.index'),
                        array('title' => 'Mapa Clientes', 'route' => 'clients.map','icon' => 'fas fa-map', 'can'=>'clients.index'),
                        array('title' => 'Facturas', 'route' => 'invoices.index','icon' => 'fas fa-file-alt', 'can'=>'invoices.index'),
                        array('title' => 'Pagos', 'route' => 'payments.index','icon' => 'fas fa-dollar-sign', 'can'=>'payments.index'),
                        array('title' => 'Pagos Pendientes', 'route' => 'payments.pending','icon' => 'fas fa-hourglass-half', 'can'=>'payments.index'),
                        array('title' => 'Tickets', 'route' => '#','icon' => 'fas fa-ticket-alt', 'can'=>'clients.index', 'submenu'=>[
                            array('title' => __('Listado general'), 'route' => 'tickets.index', 'icon' => 'fas fa-list-ul', 'can'=>'clients.index'),
                        ]),
                        array('title' => 'Reportes', 'route' => '#','icon' => 'fas fa-chart-line', 'can'=>'report.index', 'submenu'=>[
                            array('title' => __('Clientes'), 'route' => 'report.clients', 'icon' => 'fas fa-users', 'can'=>'report.clients'),
                            array('title' => __('Clientes Morosos'), 'route' => 'report.clients.moroso', 'icon' => 'fas fa-user-times', 'can'=>'report.clients.moroso'),
                            array('title' => __('Facturas'), 'route' => 'report.invoices', 'icon' => 'fas fa-file-alt', 'can'=>'report.invoices'),
                            array('title' => __('Planes por Zona'), 'route' => 'report.plans.zone', 'icon' => 'fas fa-sitemap', 'can'=>'report.invoices'),
                        ]),
                        array('title' => __('Configuración'), 'route' => 'setting.index', 'icon' => 'fas fa-sliders-h', 'can'=>'setting.index'),


                        


                    );

            $view->with('MenuSistema',$MenuSistema);
        });
    }
}
