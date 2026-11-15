<?php

namespace App\Http\Livewire;

use App\Models\MapFiberRoute;
use App\Models\Node;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MapFiberRouteComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $zone_id;
    public $node_id;
    public $name;
    public $route_type = 'fibra';
    public $stroke_color = '#0ea5e9';
    public $stroke_weight = 4;
    public $path_points_json;
    public $description;
    public $selected_id;
    public $search = '';
    public $createMode = true;

    private $pagination = 15;

    public function render()
    {
        $list_routes = MapFiberRoute::query()
            ->with(['zone', 'node'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('route_type', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('description', 'LIKE', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($this->pagination);

        $zones = Zone::orderBy('name')->pluck('name', 'id');
        $nodes = Node::orderBy('name')->pluck('name', 'id');

        return view('livewire.map-fiber-route-component', compact('list_routes', 'zones', 'nodes'));
    }

    public function updatingSearch()
    {
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->zone_id = '';
        $this->node_id = '';
        $this->name = '';
        $this->route_type = 'fibra';
        $this->stroke_color = '#0ea5e9';
        $this->stroke_weight = 4;
        $this->path_points_json = '';
        $this->description = '';
        $this->selected_id = null;
        $this->createMode = true;
    }

    public function store()
    {
        abort_if(!Auth::user()->can('map.fiber'), 401);

        $data = $this->validateData();
        if ($data === null) {
            return;
        }
        MapFiberRoute::create($data);

        $this->emit('mapFiberSaved', 'Ruta de fibra creada correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('map.fiber'), 401);

        $route = MapFiberRoute::findOrFail($id);
        $this->selected_id = $route->id;
        $this->zone_id = $route->zone_id;
        $this->node_id = $route->node_id;
        $this->name = $route->name;
        $this->route_type = $route->route_type;
        $this->stroke_color = $route->stroke_color;
        $this->stroke_weight = $route->stroke_weight;
        $this->path_points_json = $route->path_points ? json_encode($route->path_points, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
        $this->description = $route->description;
        $this->createMode = false;

        $this->emit('mapFiberEdit', 'Editar ruta: '.$route->name);
    }

    public function update()
    {
        abort_if(!Auth::user()->can('map.fiber'), 401);

        $data = $this->validateData();
        if ($data === null) {
            return;
        }
        $route = MapFiberRoute::findOrFail($this->selected_id);
        $route->update($data);

        $this->emit('mapFiberSaved', 'Ruta de fibra actualizada correctamente.');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('map.fiber'), 401);

        $route = MapFiberRoute::findOrFail($id);
        $route->delete();
        $this->emit('mapFiberDeleted');
    }

    protected $listeners = [
        'onDeleteMapFiberRow' => 'destroy',
    ];

    private function validateData()
    {
        $validated = $this->validate([
            'zone_id' => ['nullable', 'exists:zones,id'],
            'node_id' => ['nullable', 'exists:nodes,id'],
            'name' => ['required', 'string', 'max:191'],
            'route_type' => ['required', 'in:fibra,backbone,drop'],
            'stroke_color' => ['nullable', 'string', 'max:20'],
            'stroke_weight' => ['required', 'integer', 'min:1', 'max:12'],
            'path_points_json' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $pathPoints = $this->decodeJsonArray($validated['path_points_json'], 'path_points_json');
        if ($pathPoints === false) {
            return null;
        }

        return [
            'zone_id' => $validated['zone_id'] ?: null,
            'node_id' => $validated['node_id'] ?: null,
            'name' => trim($validated['name']),
            'route_type' => $validated['route_type'],
            'stroke_color' => trim((string) ($validated['stroke_color'] ?? '')) ?: '#0ea5e9',
            'stroke_weight' => $validated['stroke_weight'],
            'path_points' => $pathPoints,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
        ];
    }

    private function decodeJsonArray($json, $field)
    {
        $decoded = json_decode(trim((string) $json), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $this->addError($field, 'Debes ingresar un JSON valido.');
            return false;
        }

        return $decoded;
    }
}
