<?php

namespace App\Http\Livewire;

use App\Models\MapCoverage;
use App\Models\Node;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MapCoverageComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $zone_id;
    public $node_id;
    public $name;
    public $coverage_type = 'circle';
    public $technology;
    public $center_latitude;
    public $center_longitude;
    public $radius_meters = 300;
    public $polygon_points_json;
    public $fill_color = '#22c55e';
    public $stroke_color = '#15803d';
    public $description;
    public $selected_id;
    public $search = '';
    public $createMode = true;

    private $pagination = 15;

    public function render()
    {
        $list_coverages = MapCoverage::query()
            ->with(['zone', 'node'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('technology', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('coverage_type', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('description', 'LIKE', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($this->pagination);

        $zones = Zone::orderBy('name')->pluck('name', 'id');
        $nodes = Node::orderBy('name')->pluck('name', 'id');

        return view('livewire.map-coverage-component', compact('list_coverages', 'zones', 'nodes'));
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
        $this->coverage_type = 'circle';
        $this->technology = '';
        $this->center_latitude = '';
        $this->center_longitude = '';
        $this->radius_meters = 300;
        $this->polygon_points_json = '';
        $this->fill_color = '#22c55e';
        $this->stroke_color = '#15803d';
        $this->description = '';
        $this->selected_id = null;
        $this->createMode = true;
    }

    public function store()
    {
        abort_if(!Auth::user()->can('map.coverage'), 401);

        $data = $this->validateData();
        if ($data === null) {
            return;
        }
        MapCoverage::create($data);

        $this->emit('mapCoverageSaved', 'Cobertura creada correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('map.coverage'), 401);

        $coverage = MapCoverage::findOrFail($id);
        $this->selected_id = $coverage->id;
        $this->zone_id = $coverage->zone_id;
        $this->node_id = $coverage->node_id;
        $this->name = $coverage->name;
        $this->coverage_type = $coverage->coverage_type;
        $this->technology = $coverage->technology;
        $this->center_latitude = $coverage->center_latitude;
        $this->center_longitude = $coverage->center_longitude;
        $this->radius_meters = $coverage->radius_meters;
        $this->polygon_points_json = $coverage->polygon_points ? json_encode($coverage->polygon_points, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
        $this->fill_color = $coverage->fill_color;
        $this->stroke_color = $coverage->stroke_color;
        $this->description = $coverage->description;
        $this->createMode = false;

        $this->emit('mapCoverageEdit', 'Editar cobertura: '.$coverage->name);
    }

    public function update()
    {
        abort_if(!Auth::user()->can('map.coverage'), 401);

        $data = $this->validateData();
        if ($data === null) {
            return;
        }
        $coverage = MapCoverage::findOrFail($this->selected_id);
        $coverage->update($data);

        $this->emit('mapCoverageSaved', 'Cobertura actualizada correctamente.');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('map.coverage'), 401);

        $coverage = MapCoverage::findOrFail($id);
        $coverage->delete();
        $this->emit('mapCoverageDeleted');
    }

    protected $listeners = [
        'onDeleteMapCoverageRow' => 'destroy',
    ];

    private function validateData()
    {
        $validated = $this->validate([
            'zone_id' => ['nullable', 'exists:zones,id'],
            'node_id' => ['nullable', 'exists:nodes,id'],
            'name' => ['required', 'string', 'max:191'],
            'coverage_type' => ['required', 'in:circle,polygon'],
            'technology' => ['nullable', 'string', 'max:50'],
            'center_latitude' => ['required', 'numeric'],
            'center_longitude' => ['required', 'numeric'],
            'radius_meters' => ['nullable', 'integer', 'min:1'],
            'polygon_points_json' => ['nullable', 'string'],
            'fill_color' => ['nullable', 'string', 'max:20'],
            'stroke_color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $polygonPoints = null;
        if ($validated['coverage_type'] === 'polygon') {
            $polygonPoints = $this->decodeJsonArray($validated['polygon_points_json'] ?? '', 'polygon_points_json');
            if ($polygonPoints === false) {
                return null;
            }
        }

        return [
            'zone_id' => $validated['zone_id'] ?: null,
            'node_id' => $validated['node_id'] ?: null,
            'name' => trim($validated['name']),
            'coverage_type' => $validated['coverage_type'],
            'technology' => trim((string) ($validated['technology'] ?? '')) ?: null,
            'center_latitude' => $validated['center_latitude'],
            'center_longitude' => $validated['center_longitude'],
            'radius_meters' => $validated['coverage_type'] === 'circle' ? ($validated['radius_meters'] ?: 300) : null,
            'polygon_points' => $polygonPoints,
            'fill_color' => trim((string) ($validated['fill_color'] ?? '')) ?: '#22c55e',
            'stroke_color' => trim((string) ($validated['stroke_color'] ?? '')) ?: '#15803d',
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
