<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Setting;

use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class SettingComponent extends Component
{
    use WithFileUploads;
    //datos empresa
    public $nombre_empresa;
    public $direccion_empresa;
    public $telefono_empresa;
    public $nit_empresa;
    public $slogan_empresa;
    public $logo_empresa;
    public $img_id;

    public $tab='empresa';

    public function mount()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort_if(true, 403);
        } 
        $this->img_id = rand();
        $configuraciones = Setting::all();
        foreach ($configuraciones as $key => $value) {
            $name = $value->name;
            if($name!='logo_empresa')
                $this->$name= $value->value;
        }
    }

    public function render()
    {   

        return view('livewire.setting-component');
    }

    public function guardarEmpresa()
    {
        abort_if(!Auth::user()->can('setting.index'), 401);
        $validatedDate = $this->validate([
            'nombre_empresa' => ['required', 'string'],
            'logo_empresa' => ['image', 'max:4048'],
        ]);
        
        if($this->logo_empresa){
            $img = $this->logo_empresa->store('logo');
        }

        $settings = Setting::where('type','empresa')->get();
        foreach ($settings as $key => $setting) {
            $campo = $setting->name;
            if($this->$campo){
                if($campo=='logo_empresa'){
                    $setting->update([
                        'value' => "$img?".rand(),
                    ]);
                }else{
                    $setting->update([
                        'value' => $this->$campo,
                    ]);
                }
            }
        }
        $this->tab='empresa';
        $this->emit('infoGuardada', 'Configuración guardada correctamente');
    }

}
