<?php

namespace App\Http\Livewire;

use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ReportPlanZoneComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $zone_id = '';
    public $status = '1';

    private $pagination = 25;

    public function render()
    {
        $zones = Zone::orderBy('name')->pluck('name', 'id');
        $plansQuery = $this->plansQuery();
        $plans_summary = $this->summaryQuery()->get();
        $total_summary_planes = $plans_summary->sum('total_planes');
        $total_summary_prices = $plans_summary->sum('total_precios');
        $list_plans = $plansQuery->paginate($this->pagination);

        return view('livewire.report-plan-zone-component', compact(
            'zones',
            'plans_summary',
            'total_summary_planes',
            'total_summary_prices',
            'list_plans'
        ));
    }

    public function filtrar()
    {
        $this->gotoPage(1);
    }

    public function exportExcel()
    {
        $list_plans = $this->ListPlans();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PlanZoneExport($list_plans),
            'reporte_planes_zona_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function ListPlans()
    {
        return $this->plansQuery()->get();
    }

    private function plansQuery()
    {
        return $this->allPlansQuery()
        ->join('clients as c', 'c.id', '=', 'cp.client_id')
        ->join('users as u', 'u.id', '=', 'c.user_id')
        ->join('products as p', 'p.id', '=', 'cp.product_id')
        ->leftJoin('nodes as n', 'n.id', '=', 'cp.node_id')
        ->leftJoin('zones as z', 'z.id', '=', 'n.zone_id')
        ->select(
            'cp.id',
            'cp.address',
            'cp.reference',
            'cp.date_instalation',
            'cp.status',
            'p.name as plan_name',
            'p.price as plan_price',
            'u.name',
            'u.lastname',
            'u.email',
            'u.phone',
            'n.name as node_name',
            'z.name as zone_name'
        )
        ->whereNull('c.deleted_at')
        ->whereNull('u.deleted_at')
        ->when($this->status !== '', function ($query) {
            $query->where('cp.status', $this->status);
        })
        ->when($this->zone_id !== '', function ($query) {
            $query->where('z.id', $this->zone_id);
        })
        ->orderBy('z.name')
        ->orderBy('u.name')
        ->orderBy('p.name');
    }

    private function allPlansQuery()
    {
        return DB::table('client_product as cp');
    }

    private function summaryQuery()
    {
        return DB::table('client_product as cp')
        ->join('products as p', 'p.id', '=', 'cp.product_id')
        ->leftJoin('nodes as n', 'n.id', '=', 'cp.node_id')
        ->leftJoin('zones as z', 'z.id', '=', 'n.zone_id')
        ->select(
            DB::raw("COALESCE(z.name, 'Sin zona') as zone_name"),
            DB::raw('COUNT(cp.id) as total_planes'),
            DB::raw('SUM(p.price) as total_precios')
        )
        ->when($this->status !== '', function ($query) {
            $query->where('cp.status', $this->status);
        })
        ->when($this->zone_id !== '', function ($query) {
            $query->where('z.id', $this->zone_id);
        })
        ->groupBy('z.id', 'z.name')
        ->orderBy('zone_name');
    }
}
