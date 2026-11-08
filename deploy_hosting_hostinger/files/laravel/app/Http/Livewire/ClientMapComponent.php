<?php

namespace App\Http\Livewire;

use App\Models\ClientProduct;
use App\Models\Collector;
use App\Models\Node;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClientMapComponent extends Component
{
    public $filter_zone_id = '';
    public $filter_node_id = '';
    public $filter_status = '';
    public $search = '';

    public function updatingSearch()
    {
        // Mantiene la UX consistente al refrescar solo tras terminar el render.
    }

    public function updatedFilterZoneId()
    {
        $this->filter_node_id = '';
    }

    public function render()
    {
        $allowedPlans = $this->allowedPlans();
        $zone_options = $this->zoneOptions($allowedPlans);
        $node_options = $this->nodeOptions($allowedPlans);
        $map_points = $this->filteredPlans($allowedPlans);

        $summary = [
            'plans' => $map_points->count(),
            'clients' => $map_points->pluck('client_id')->unique()->count(),
            'zones' => $map_points->pluck('node.zone_id')->filter()->unique()->count(),
            'nodes' => $map_points->pluck('node_id')->filter()->unique()->count(),
            'active' => $map_points->where('status', '1')->count(),
            'suspended' => $map_points->where('status', '0')->count(),
            'cancelled' => $map_points->where('status', '-1')->count(),
        ];

        $map_payload = [
            'points' => $map_points->map(function ($plan) {
                $status = $this->statusMeta($plan->status);

                return [
                    'id' => $plan->id,
                    'clientName' => $plan->client && $plan->client->user ? $plan->client->user->name_complete : 'Cliente sin nombre',
                    'clientPhone' => optional(optional($plan->client)->user)->phone,
                    'productName' => optional($plan->product)->name ?: 'Plan no definido',
                    'address' => $plan->address ?: 'Sin direccion',
                    'zoneName' => optional(optional($plan->node)->zone)->name ?: 'Sin zona',
                    'nodeName' => optional($plan->node)->name ?: 'Sin nodo',
                    'status' => $plan->status,
                    'statusLabel' => $status['label'],
                    'statusTone' => $status['tone'],
                    'latitude' => (float) $plan->latitude,
                    'longitude' => (float) $plan->longitude,
                    'clientUrl' => route('clients.show', $plan->client_id),
                    'planUrl' => route('clients.products', $plan->client_id),
                ];
            })->values()->all(),
        ];

        return view('livewire.client-map-component', compact('zone_options', 'node_options', 'map_points', 'summary', 'map_payload'));
    }

    private function filteredPlans($allowedPlans = null)
    {
        return $this->basePlansQuery($allowedPlans)
            ->with(['client.user', 'product', 'node.zone'])
            ->orderByDesc('id')
            ->get();
    }

    private function basePlansQuery($allowedPlans = null)
    {
        return ClientProduct::query()
            ->whereNotNull('latitude')
            ->where('latitude', '!=', '')
            ->whereNotNull('longitude')
            ->where('longitude', '!=', '')
            ->when($allowedPlans !== null, function ($query) use ($allowedPlans) {
                $query->whereIn('client_product.id', $allowedPlans);
            })
            ->when($this->filter_zone_id !== '', function ($query) {
                $query->whereHas('node', function ($nodeQuery) {
                    $nodeQuery->where('zone_id', $this->filter_zone_id);
                });
            })
            ->when($this->filter_node_id !== '', function ($query) {
                $query->where('node_id', $this->filter_node_id);
            })
            ->when($this->filter_status !== '', function ($query) {
                $query->where('status', $this->filter_status);
            })
            ->when(trim($this->search) !== '', function ($query) {
                $search = '%'.trim($this->search).'%';

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('address', 'LIKE', $search)
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('products.name', 'LIKE', $search);
                        })
                        ->orWhereHas('client.user', function ($userQuery) use ($search) {
                            $userQuery->whereRaw('CONCAT(users.name," ",users.lastname) LIKE ?', [$search])
                                ->orWhere('users.phone', 'LIKE', $search)
                                ->orWhere('users.dni', 'LIKE', $search);
                        })
                        ->orWhereHas('node', function ($nodeQuery) use ($search) {
                            $nodeQuery->where('nodes.name', 'LIKE', $search)
                                ->orWhereHas('zone', function ($zoneQuery) use ($search) {
                                    $zoneQuery->where('zones.name', 'LIKE', $search);
                                });
                        });
                });
            });
    }

    private function zoneOptions($allowedPlans = null)
    {
        return Zone::query()
            ->whereHas('nodes.client_products', function ($query) use ($allowedPlans) {
                $query->whereNotNull('latitude')
                    ->where('latitude', '!=', '')
                    ->whereNotNull('longitude')
                    ->where('longitude', '!=', '')
                    ->when($allowedPlans !== null, function ($subQuery) use ($allowedPlans) {
                        $subQuery->whereIn('client_product.id', $allowedPlans);
                    });
            })
            ->orderBy('name')
            ->get();
    }

    private function nodeOptions($allowedPlans = null)
    {
        return Node::query()
            ->with('zone')
            ->whereHas('client_products', function ($query) use ($allowedPlans) {
                $query->whereNotNull('latitude')
                    ->where('latitude', '!=', '')
                    ->whereNotNull('longitude')
                    ->where('longitude', '!=', '')
                    ->when($allowedPlans !== null, function ($subQuery) use ($allowedPlans) {
                        $subQuery->whereIn('client_product.id', $allowedPlans);
                    });
            })
            ->when($this->filter_zone_id !== '', function ($query) {
                $query->where('zone_id', $this->filter_zone_id);
            })
            ->orderBy('name')
            ->get();
    }

    private function allowedPlans()
    {
        if (!Auth::user()->hasRole('Cobrador')) {
            return null;
        }

        $collector = Collector::find(Auth::user()->id);
        $allowedPlans = $collector ? $collector->clientProductsArray() : [];

        return empty($allowedPlans) ? [0] : $allowedPlans;
    }

    private function statusMeta($status)
    {
        switch ((string) $status) {
            case '1':
                return ['label' => 'Activo', 'tone' => 'success'];
            case '0':
                return ['label' => 'Suspendido', 'tone' => 'warning'];
            case '-1':
                return ['label' => 'Cancelado', 'tone' => 'danger'];
            default:
                return ['label' => 'Indefinido', 'tone' => 'muted'];
        }
    }
}
