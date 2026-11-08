<?php

namespace App\Http\Livewire;

use App\Models\MapAsset;
use App\Models\Node;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MapAssetComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $zone_id;
    public $node_id;
    public $name;
    public $code;
    public $asset_type = 'poste';
    public $asset_status = 'activo';
    public $latitude;
    public $longitude;
    public $description;
    public $meta_json;
    public $selected_id;
    public $search = '';
    public $createMode = true;

    private $pagination = 15;

    public function render()
    {
        $list_assets = MapAsset::query()
            ->with(['zone', 'node'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('code', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('asset_type', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('description', 'LIKE', '%'.$this->search.'%');
                });
            })
            ->orderBy('asset_type')
            ->orderBy('name')
            ->paginate($this->pagination);

        $zones = Zone::orderBy('name')->pluck('name', 'id');
        $nodes = Node::orderBy('name')->pluck('name', 'id');

        return view('livewire.map-asset-component', compact('list_assets', 'zones', 'nodes'));
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
        $this->code = '';
        $this->asset_type = 'poste';
        $this->asset_status = 'activo';
        $this->latitude = '';
        $this->longitude = '';
        $this->description = '';
        $this->meta_json = '';
        $this->selected_id = null;
        $this->createMode = true;
    }

    public function store()
    {
        abort_if(!Auth::user()->can('map.assets'), 401);

        $data = $this->validateData();
        if ($data === null) {
            return;
        }

        MapAsset::create($data);

        $this->emit('mapAssetSaved', 'Activo de red creado correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('map.assets'), 401);

        $asset = MapAsset::findOrFail($id);
        $this->selected_id = $asset->id;
        $this->zone_id = $asset->zone_id;
        $this->node_id = $asset->node_id;
        $this->name = $asset->name;
        $this->code = $asset->code;
        $this->asset_type = $asset->asset_type;
        $this->asset_status = $asset->asset_status;
        $this->latitude = $asset->latitude;
        $this->longitude = $asset->longitude;
        $this->description = $asset->description;
        $this->meta_json = $asset->meta ? json_encode($asset->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
        $this->createMode = false;

        $this->emit('mapAssetEdit', 'Editar activo: '.$asset->name);
    }

    public function update()
    {
        abort_if(!Auth::user()->can('map.assets'), 401);

        $data = $this->validateData();
        if ($data === null) {
            return;
        }
        $asset = MapAsset::findOrFail($this->selected_id);
        $asset->update($data);

        $this->emit('mapAssetSaved', 'Activo de red actualizado correctamente.');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('map.assets'), 401);

        $asset = MapAsset::findOrFail($id);
        $asset->delete();
        $this->emit('mapAssetDeleted');
    }

    protected $listeners = [
        'onDeleteMapAssetRow' => 'destroy',
    ];

    private function validateData()
    {
        $validated = $this->validate([
            'zone_id' => ['nullable', 'exists:zones,id'],
            'node_id' => ['nullable', 'exists:nodes,id'],
            'name' => ['required', 'string', 'max:191'],
            'code' => ['nullable', 'string', 'max:80'],
            'asset_type' => ['required', 'in:poste,nap,olt,onu,antena'],
            'asset_status' => ['required', 'in:activo,mantenimiento,reserva,fuera_servicio'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'meta_json' => ['nullable', 'string'],
        ]);

        $meta = $this->decodeJsonOrNull($validated['meta_json'] ?? '');
        if ($meta === false) {
            return null;
        }

        return [
            'zone_id' => $validated['zone_id'] ?: null,
            'node_id' => $validated['node_id'] ?: null,
            'name' => trim($validated['name']),
            'code' => trim((string) ($validated['code'] ?? '')) ?: null,
            'asset_type' => $validated['asset_type'],
            'asset_status' => $validated['asset_status'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'meta' => $meta,
        ];
    }

    private function decodeJsonOrNull($json)
    {
        $json = trim((string) $json);

        if ($json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('meta_json', 'El JSON de datos extra no es valido.');
            return false;
        }

        return $decoded;
    }
}
