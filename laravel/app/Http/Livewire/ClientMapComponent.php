<?php

namespace App\Http\Livewire;

use App\Models\ClientProduct;
use App\Models\MapAsset;
use App\Models\MapCoverage;
use App\Models\MapFiberRoute;
use App\Models\Node;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClientMapComponent extends Component
{
    public $filter_zone_id = '';
    public $filter_node_id = '';
    public $filter_status = '';
    public $filter_tickets = '';
    public $filter_asset_type = '';
    public $search = '';
    public $show_clients = true;
    public $show_assets = true;
    public $show_fiber = true;
    public $show_coverage = true;

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
        $canManageAssets = Auth::user()->can('map.assets');
        $canManageFiber = Auth::user()->can('map.fiber');
        $canManageCoverage = Auth::user()->can('map.coverage');

        if (!$canManageAssets) {
            $this->show_assets = false;
            $this->filter_asset_type = '';
        }

        if (!$canManageFiber) {
            $this->show_fiber = false;
        }

        if (!$canManageCoverage) {
            $this->show_coverage = false;
        }

        $allowedPlans = $this->allowedPlans();
        $zone_options = $this->zoneOptions($allowedPlans);
        $node_options = $this->nodeOptions($allowedPlans);
        $map_points = $this->filteredPlans($allowedPlans);
        $map_assets = $this->filteredAssets();
        $fiber_routes = $this->filteredFiberRoutes();
        $coverages = $this->filteredCoverages();
        $side_assets = $map_assets->take(8);

        $summary = [
            'plans' => $map_points->count(),
            'clients' => $map_points->pluck('client_id')->unique()->count(),
            'zones' => $map_points->pluck('node.zone_id')->filter()->unique()->count(),
            'nodes' => $map_points->pluck('node_id')->filter()->unique()->count(),
            'active' => $map_points->where('status', '1')->count(),
            'suspended' => $map_points->where('status', '0')->count(),
            'debt' => $map_points->where('billing_status', 'deuda')->count(),
            'open_tickets' => $map_points->where('open_tickets_count', '>', 0)->count(),
            'assets' => $map_assets->count(),
            'fiber_routes' => $fiber_routes->count(),
            'coverages' => $coverages->count(),
        ];

        $map_payload = [
            'points' => $map_points->map(function ($plan) {
                $status = $this->statusMeta($plan->status);
                $billing = $this->billingMeta($plan->billing_status);
                $topOpenTicket = $plan->tickets->first();
                $ticketPriority = $topOpenTicket ? $this->ticketPriorityMeta($topOpenTicket->priority) : null;

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
                    'billingStatus' => $plan->billing_status,
                    'billingLabel' => $billing['label'],
                    'billingTone' => $billing['tone'],
                    'hasOpenTickets' => (int) $plan->open_tickets_count > 0,
                    'openTicketsCount' => (int) $plan->open_tickets_count,
                    'openTicketPriority' => $ticketPriority['key'] ?? null,
                    'openTicketPriorityLabel' => $ticketPriority['label'] ?? null,
                    'openTicketPriorityColor' => $ticketPriority['color'] ?? null,
                    'debtMonths' => (int) ($plan->debt_months ?? 0),
                    'planPrice' => optional($plan->product)->price,
                    'latitude' => (float) $plan->latitude,
                    'longitude' => (float) $plan->longitude,
                    'canOpenClient' => Auth::user()->can('clients.index'),
                    'canOpenPlan' => Auth::user()->can('clients.index'),
                    'canOpenTickets' => Auth::user()->can('tickets.index'),
                    'clientUrl' => Auth::user()->can('clients.index') ? route('clients.show', $plan->client_id) : null,
                    'planUrl' => Auth::user()->can('clients.index') ? route('clients.products', $plan->client_id) : null,
                    'ticketsUrl' => Auth::user()->can('tickets.index')
                        ? route('clients.tickets', ['client' => $plan->client_id, 'client_product_id' => $plan->id])
                        : null,
                ];
            })->values()->all(),
            'assets' => $map_assets->map(function ($asset) {
                $meta = is_array($asset->meta) ? $asset->meta : [];

                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'code' => $asset->code,
                    'assetType' => $asset->asset_type,
                    'assetLabel' => $this->assetTypeMeta($asset->asset_type)['label'],
                    'assetIcon' => $this->assetTypeMeta($asset->asset_type)['icon'],
                    'assetColor' => $this->assetTypeMeta($asset->asset_type)['color'],
                    'assetStatus' => $asset->asset_status,
                    'description' => $asset->description,
                    'zoneName' => optional($asset->zone)->name ?: 'Sin zona',
                    'nodeName' => optional($asset->node)->name ?: 'Sin nodo',
                    'latitude' => (float) $asset->latitude,
                    'longitude' => (float) $asset->longitude,
                    'meta' => $meta,
                ];
            })->values()->all(),
            'fiberRoutes' => $fiber_routes->map(function ($route) {
                return [
                    'id' => $route->id,
                    'name' => $route->name,
                    'routeType' => $route->route_type,
                    'strokeColor' => $route->stroke_color ?: '#0ea5e9',
                    'strokeWeight' => (int) ($route->stroke_weight ?: 4),
                    'description' => $route->description,
                    'path' => collect($route->path_points ?: [])->map(function ($point) {
                        return [
                            'lat' => (float) data_get($point, 'lat'),
                            'lng' => (float) data_get($point, 'lng'),
                        ];
                    })->filter(function ($point) {
                        return $point['lat'] && $point['lng'];
                    })->values()->all(),
                ];
            })->values()->all(),
            'coverages' => $coverages->map(function ($coverage) {
                return [
                    'id' => $coverage->id,
                    'name' => $coverage->name,
                    'coverageType' => $coverage->coverage_type,
                    'technology' => $coverage->technology,
                    'center' => [
                        'lat' => (float) $coverage->center_latitude,
                        'lng' => (float) $coverage->center_longitude,
                    ],
                    'radiusMeters' => (int) ($coverage->radius_meters ?: 0),
                    'polygon' => collect($coverage->polygon_points ?: [])->map(function ($point) {
                        return [
                            'lat' => (float) data_get($point, 'lat'),
                            'lng' => (float) data_get($point, 'lng'),
                        ];
                    })->filter(function ($point) {
                        return $point['lat'] && $point['lng'];
                    })->values()->all(),
                    'fillColor' => $coverage->fill_color ?: '#22c55e',
                    'strokeColor' => $coverage->stroke_color ?: '#15803d',
                    'description' => $coverage->description,
                ];
            })->values()->all(),
        ];

        return view('livewire.client-map-component', compact(
            'zone_options',
            'node_options',
            'map_points',
            'summary',
            'map_payload',
            'side_assets',
            'canManageAssets',
            'canManageFiber',
            'canManageCoverage'
        ));
    }

    private function filteredPlans($allowedPlans = null)
    {
        return $this->basePlansQuery($allowedPlans)
            ->withCount([
                'invoices as debt_months' => function ($query) {
                    $query->whereIn('status', ['0', '-1']);
                },
            ])
            ->withCount([
                'tickets as open_tickets_count' => function ($query) {
                    $query->whereIn('status', $this->openTicketStatuses());
                }
            ])
            ->with([
                'client.user',
                'product',
                'node.zone',
                'tickets' => function ($query) {
                    $query->whereIn('status', $this->openTicketStatuses())
                        ->orderByRaw("FIELD(priority, 'alta', 'media', 'baja')")
                        ->orderBy('id');
                },
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($plan) {
                $plan->billing_status = $this->resolveBillingStatus($plan);
                return $plan;
            })
            ->when($this->filter_status === 'deuda', function ($collection) {
                return $collection->where('billing_status', 'deuda')->values();
            })
            ->when(!$this->show_clients, function ($collection) {
                return collect();
            });
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
            ->when($this->filter_status !== '' && $this->filter_status !== 'deuda', function ($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->filter_tickets === 'open', function ($query) {
                $query->whereHas('tickets', function ($ticketQuery) {
                    $ticketQuery->whereIn('status', $this->openTicketStatuses());
                });
            })
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);

                $query->where(function ($searchQuery) use ($search) {
                    applyFlexibleSearch($searchQuery, $search, planSearchExpressions('client_product'));

                    $searchQuery->orWhereHas('product', function ($productQuery) use ($search) {
                            applyFlexibleSearch($productQuery, $search, ['products.name']);
                        })
                        ->orWhereHas('client.user', function ($userQuery) use ($search) {
                            applyFlexibleSearch($userQuery, $search, userSearchExpressions('users'));
                        })
                        ->orWhereHas('node', function ($nodeQuery) use ($search) {
                            applyFlexibleSearch($nodeQuery, $search, ['nodes.name', 'nodes.code'])
                                ->orWhereHas('zone', function ($zoneQuery) use ($search) {
                                    applyFlexibleSearch($zoneQuery, $search, ['zones.name', 'zones.code']);
                                });
                        });
                });
            });
    }

    private function filteredAssets()
    {
        if (!Auth::user()->can('map.assets') || !$this->show_assets) {
            return collect();
        }

        return MapAsset::query()
            ->with(['zone', 'node'])
            ->when($this->filter_zone_id !== '', function ($query) {
                $query->where('zone_id', $this->filter_zone_id);
            })
            ->when($this->filter_node_id !== '', function ($query) {
                $query->where('node_id', $this->filter_node_id);
            })
            ->when($this->filter_asset_type !== '', function ($query) {
                $query->where('asset_type', $this->filter_asset_type);
            })
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($subQuery) use ($search) {
                    applyFlexibleSearch($subQuery, $search, ['map_assets.name', 'map_assets.code', 'map_assets.description', 'map_assets.asset_type']);
                });
            })
            ->orderBy('asset_type')
            ->orderBy('name')
            ->get();
    }

    private function filteredFiberRoutes()
    {
        if (!Auth::user()->can('map.fiber') || !$this->show_fiber) {
            return collect();
        }

        return MapFiberRoute::query()
            ->when($this->filter_zone_id !== '', function ($query) {
                $query->where('zone_id', $this->filter_zone_id);
            })
            ->when($this->filter_node_id !== '', function ($query) {
                $query->where('node_id', $this->filter_node_id);
            })
            ->orderBy('name')
            ->get();
    }

    private function filteredCoverages()
    {
        if (!Auth::user()->can('map.coverage') || !$this->show_coverage) {
            return collect();
        }

        return MapCoverage::query()
            ->when($this->filter_zone_id !== '', function ($query) {
                $query->where('zone_id', $this->filter_zone_id);
            })
            ->when($this->filter_node_id !== '', function ($query) {
                $query->where('node_id', $this->filter_node_id);
            })
            ->orderBy('name')
            ->get();
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

        $allowedPlans = Auth::user()->clientProductsArray();

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

    private function billingMeta($billingStatus)
    {
        switch ((string) $billingStatus) {
            case 'deuda':
                return ['label' => 'Con deuda', 'tone' => 'danger'];
            case 'suspendido':
                return ['label' => 'Suspendido', 'tone' => 'warning'];
            default:
                return ['label' => 'Al dia', 'tone' => 'success'];
        }
    }

    private function resolveBillingStatus($plan)
    {
        if ((string) $plan->status === '0') {
            return 'suspendido';
        }

        if ((int) ($plan->debt_months ?? 0) > 0) {
            return 'deuda';
        }

        return 'al_dia';
    }

    private function openTicketStatuses()
    {
        return ['pendiente'];
    }

    private function ticketPriorityMeta($priority)
    {
        switch ((string) $priority) {
            case 'alta':
                return ['key' => 'alta', 'label' => 'Alta', 'color' => '#dc2626'];
            case 'media':
                return ['key' => 'media', 'label' => 'Media', 'color' => '#f59e0b'];
            default:
                return ['key' => 'baja', 'label' => 'Baja', 'color' => '#2563eb'];
        }
    }

    private function assetTypeMeta($type)
    {
        switch ((string) $type) {
            case 'poste':
                return ['label' => 'Poste', 'icon' => 'post', 'color' => '#92400e'];
            case 'nap':
                return ['label' => 'Caja NAP', 'icon' => 'box', 'color' => '#7c3aed'];
            case 'olt':
                return ['label' => 'OLT', 'icon' => 'olt', 'color' => '#2563eb'];
            case 'onu':
                return ['label' => 'ONU', 'icon' => 'onu', 'color' => '#0f766e'];
            case 'antena':
                return ['label' => 'Antena/AP', 'icon' => 'antenna', 'color' => '#16a34a'];
            default:
                return ['label' => 'Activo', 'icon' => 'asset', 'color' => '#475569'];
        }
    }
}
