<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Setting;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    // ticket o recibo impreso
    public $ticket_documento_titulo = 'RECIBO';
    public $ticket_copias = 2;
    public $ticket_mensaje_final = '';
    public $ticket_rollo_ancho_mm = 80;
    public $ticket_margen_interno_mm = 4;
    public $ticket_margen_superior_mm = 4;
    public $ticket_margen_derecho_mm = 4;
    public $ticket_margen_inferior_mm = 4;
    public $ticket_margen_izquierdo_mm = 4;
    public $ticket_separador_estilo = 'dashed';
    public $ticket_mostrar_linea_corte = true;
    public $ticket_mostrar_logo = true;
    public $ticket_logo_ancho = 80;
    public $ticket_mostrar_dpi = true;
    public $ticket_mostrar_direccion_plan = true;
    public $ticket_mostrar_observacion = true;
    public $ticket_auto_imprimir = true;
    public $ticket_estilos = [];

    public $tab='empresa';

    public function mount()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort_if(true, 403);
        }

        $this->img_id = rand();
        $this->ticket_estilos = $this->defaultTicketStyles();

        foreach (Setting::all() as $value) {
            $name = $value->name;

            if (Str::startsWith($name, 'ticket_estilo_')) {
                $this->hydrateTicketStyleSetting($name, $value->value);
                continue;
            }

            if ($name === 'logo_empresa' || !property_exists($this, $name)) {
                continue;
            }

            $this->$name = $this->normalizeSettingValue($name, $value->value);
        }
    }

    public function render()
    {
        return view('livewire.setting-component');
    }

    public function guardarEmpresa()
    {
        abort_if(!Auth::user()->can('setting.index'), 401);

        $this->validate([
            'nombre_empresa' => ['required', 'string'],
            'direccion_empresa' => ['nullable', 'string'],
            'telefono_empresa' => ['nullable', 'string'],
            'nit_empresa' => ['nullable', 'string'],
            'slogan_empresa' => ['nullable', 'string'],
            'logo_empresa' => ['nullable', 'image', 'max:4048'],
        ]);

        if ($this->logo_empresa) {
            $img = $this->logo_empresa->store('logo');
            $this->saveSettingValue('empresa', 'logo_empresa', $img.'?'.rand());
        }

        foreach ([
            'nombre_empresa',
            'direccion_empresa',
            'telefono_empresa',
            'nit_empresa',
            'slogan_empresa',
        ] as $campo) {
            $this->saveSettingValue('empresa', $campo, (string) ($this->$campo ?? ''));
        }

        $this->tab = 'empresa';
        $this->emit('infoGuardada', 'Configuración guardada correctamente');
    }

    public function guardarTicket()
    {
        abort_if(!Auth::user()->can('setting.index'), 401);

        $validatedData = $this->validate([
            'ticket_documento_titulo' => ['required', 'string', 'max:50'],
            'ticket_copias' => ['required', 'integer', 'min:1', 'max:3'],
            'ticket_mensaje_final' => ['nullable', 'string', 'max:255'],
            'ticket_rollo_ancho_mm' => ['required', 'integer', 'in:58,72,80,110,112'],
            'ticket_margen_interno_mm' => ['required', 'integer', 'in:2,4,6'],
            'ticket_margen_superior_mm' => ['required', 'integer', 'in:2,4,6,8'],
            'ticket_margen_derecho_mm' => ['required', 'integer', 'in:2,4,6,8'],
            'ticket_margen_inferior_mm' => ['required', 'integer', 'in:2,4,6,8'],
            'ticket_margen_izquierdo_mm' => ['required', 'integer', 'in:2,4,6,8'],
            'ticket_separador_estilo' => ['required', 'in:dashed,solid,dotted'],
            'ticket_mostrar_linea_corte' => ['boolean'],
            'ticket_mostrar_logo' => ['boolean'],
            'ticket_logo_ancho' => ['required', 'integer', 'min:40', 'max:180'],
            'ticket_mostrar_dpi' => ['boolean'],
            'ticket_mostrar_direccion_plan' => ['boolean'],
            'ticket_mostrar_observacion' => ['boolean'],
            'ticket_auto_imprimir' => ['boolean'],
            'ticket_estilos.encabezado.familia' => ['required', 'in:sans,serif,mono'],
            'ticket_estilos.encabezado.tamano' => ['required', 'integer', 'min:10', 'max:24'],
            'ticket_estilos.encabezado.peso' => ['required', 'in:normal,medium,full'],
            'ticket_estilos.logo.familia' => ['required', 'in:sans,serif,mono'],
            'ticket_estilos.logo.tamano' => ['required', 'integer', 'min:10', 'max:24'],
            'ticket_estilos.logo.peso' => ['required', 'in:normal,medium,full'],
            'ticket_estilos.titulo.familia' => ['required', 'in:sans,serif,mono'],
            'ticket_estilos.titulo.tamano' => ['required', 'integer', 'min:10', 'max:24'],
            'ticket_estilos.titulo.peso' => ['required', 'in:normal,medium,full'],
            'ticket_estilos.cliente.familia' => ['required', 'in:sans,serif,mono'],
            'ticket_estilos.cliente.tamano' => ['required', 'integer', 'min:10', 'max:24'],
            'ticket_estilos.cliente.peso' => ['required', 'in:normal,medium,full'],
            'ticket_estilos.concepto.familia' => ['required', 'in:sans,serif,mono'],
            'ticket_estilos.concepto.tamano' => ['required', 'integer', 'min:10', 'max:24'],
            'ticket_estilos.concepto.peso' => ['required', 'in:normal,medium,full'],
            'ticket_estilos.pie.familia' => ['required', 'in:sans,serif,mono'],
            'ticket_estilos.pie.tamano' => ['required', 'integer', 'min:10', 'max:24'],
            'ticket_estilos.pie.peso' => ['required', 'in:normal,medium,full'],
        ]);

        foreach ($validatedData as $campo => $valor) {
            if ($campo === 'ticket_estilos') {
                continue;
            }

            $this->saveSettingValue('ticket', $campo, is_bool($valor) ? ($valor ? '1' : '0') : (string) $valor);
        }

        foreach ($validatedData['ticket_estilos'] as $seccion => $estilo) {
            foreach ($estilo as $clave => $valor) {
                $this->saveSettingValue('ticket', 'ticket_estilo_'.$seccion.'_'.$clave, (string) $valor);
            }
        }

        $this->tab = 'empresa';
        $this->emit('infoGuardada', 'Configuración del ticket guardada correctamente');
    }

    private function saveSettingValue($type, $name, $value)
    {
        $setting = Setting::withTrashed()
            ->where('type', $type)
            ->where('name', $name)
            ->first();

        if (!$setting) {
            $setting = new Setting();
            $setting->type = $type;
            $setting->name = $name;
        }

        if (method_exists($setting, 'trashed') && $setting->trashed()) {
            $setting->restore();
        }

        $setting->value = $value;
        $setting->save();
    }

    private function normalizeSettingValue($name, $value)
    {
        if (in_array($name, [
            'ticket_mostrar_linea_corte',
            'ticket_mostrar_logo',
            'ticket_mostrar_dpi',
            'ticket_mostrar_direccion_plan',
            'ticket_mostrar_observacion',
            'ticket_auto_imprimir',
        ], true)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $value === '1';
        }

        if (in_array($name, [
            'ticket_copias',
            'ticket_logo_ancho',
            'ticket_rollo_ancho_mm',
            'ticket_margen_interno_mm',
            'ticket_margen_superior_mm',
            'ticket_margen_derecho_mm',
            'ticket_margen_inferior_mm',
            'ticket_margen_izquierdo_mm',
        ], true)) {
            return (int) $value;
        }

        return $value;
    }

    private function defaultTicketStyles()
    {
        return [
            'encabezado' => ['familia' => 'sans', 'tamano' => 12, 'peso' => 'normal'],
            'logo' => ['familia' => 'sans', 'tamano' => 18, 'peso' => 'full'],
            'titulo' => ['familia' => 'sans', 'tamano' => 14, 'peso' => 'full'],
            'cliente' => ['familia' => 'sans', 'tamano' => 12, 'peso' => 'medium'],
            'concepto' => ['familia' => 'mono', 'tamano' => 12, 'peso' => 'normal'],
            'pie' => ['familia' => 'sans', 'tamano' => 11, 'peso' => 'medium'],
        ];
    }

    private function hydrateTicketStyleSetting($name, $value)
    {
        $styleName = Str::after($name, 'ticket_estilo_');
        $lastUnderscorePosition = strrpos($styleName, '_');

        if ($lastUnderscorePosition === false) {
            return;
        }

        $section = substr($styleName, 0, $lastUnderscorePosition);
        $attribute = substr($styleName, $lastUnderscorePosition + 1);

        if (!isset($this->ticket_estilos[$section]) || !array_key_exists($attribute, $this->ticket_estilos[$section])) {
            return;
        }

        $this->ticket_estilos[$section][$attribute] = $attribute === 'tamano' ? (int) $value : $value;
    }

    public function updatedTicketMargenInternoMm($value)
    {
        $margin = (int) $value;

        $this->ticket_margen_superior_mm = $margin;
        $this->ticket_margen_derecho_mm = $margin;
        $this->ticket_margen_inferior_mm = $margin;
        $this->ticket_margen_izquierdo_mm = $margin;
    }
}
