<?php

namespace App\Http\Livewire;

use App\Models\ClientProduct;
use App\Models\Invoice;
use Carbon\Carbon;
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
    public $invoice_audit_results = [];
    public $invoice_audit_summary = [
        'plans_checked' => 0,
        'plans_with_issues' => 0,
        'missing_months' => 0,
        'created_invoices' => 0,
        'last_run_at' => null,
    ];
    public $invoice_unpaid_audit_results = [];
    public $invoice_unpaid_audit_summary = [
        'plans_checked' => 0,
        'plans_with_issues' => 0,
        'unpaid_months' => 0,
        'last_run_at' => null,
    ];

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

    public function auditarMesesFacturacion()
    {
        abort_if(!Auth::user()->can('setting.index'), 401);

        [$results, $summary] = $this->buildInvoiceAuditSnapshot();

        $this->invoice_audit_results = $results;
        $this->invoice_audit_summary = $summary;

        $this->emit(
            'infoGuardada',
            $summary['plans_with_issues'] > 0
                ? 'Auditoría completada. Se encontraron '.$summary['missing_months'].' meses faltantes.'
                : 'Auditoría completada. No se encontraron meses faltantes.'
        );
    }

    public function crearMesesFaltantes($clientProductId)
    {
        abort_if(!Auth::user()->can('setting.index'), 401);

        $contract = $this->buildAuditBaseQuery()
            ->where('id', $clientProductId)
            ->firstOrFail();

        $auditItem = $this->buildAuditItem($contract);

        if (!$auditItem || empty($auditItem['missing_month_keys'])) {
            $this->auditarMesesFacturacion();
            $this->emit('infoGuardada', 'Ese plan no tiene meses faltantes pendientes.');
            return;
        }

        $createdInvoices = $this->createMissingInvoicesForPlan($contract, $auditItem['missing_month_keys']);

        $this->auditarMesesFacturacion();
        $this->emit('infoGuardada', 'Se crearon '.$createdInvoices.' factura(s) faltante(s) para '.$auditItem['client_name'].'.');
    }

    public function crearTodosLosMesesFaltantes()
    {
        abort_if(!Auth::user()->can('setting.index'), 401);

        $contracts = $this->buildAuditBaseQuery()->get();
        $createdInvoices = 0;

        foreach ($contracts as $contract) {
            $auditItem = $this->buildAuditItem($contract);

            if (!$auditItem || empty($auditItem['missing_month_keys'])) {
                continue;
            }

            $createdInvoices += $this->createMissingInvoicesForPlan($contract, $auditItem['missing_month_keys']);
        }

        $this->auditarMesesFacturacion();
        $this->emit('infoGuardada', 'Se crearon '.$createdInvoices.' factura(s) faltante(s) en total.');
    }

    public function auditarMesesSinPago()
    {
        abort_if(!Auth::user()->can('setting.index'), 401);

        [$results, $summary] = $this->buildUnpaidInvoiceAuditSnapshot();

        $this->invoice_unpaid_audit_results = $results;
        $this->invoice_unpaid_audit_summary = $summary;

        $this->emit(
            'infoGuardada',
            $summary['plans_with_issues'] > 0
                ? 'Auditoría completada. Se encontraron '.$summary['unpaid_months'].' meses con factura sin pagar.'
                : 'Auditoría completada. No se encontraron meses con factura sin pagar.'
        );
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

    private function buildInvoiceAuditSnapshot()
    {
        $results = [];
        $summary = [
            'plans_checked' => 0,
            'plans_with_issues' => 0,
            'missing_months' => 0,
            'created_invoices' => $this->invoice_audit_summary['created_invoices'] ?? 0,
            'last_run_at' => now()->format('Y-m-d H:i:s'),
        ];

        foreach ($this->buildAuditBaseQuery()->get() as $contract) {
            $summary['plans_checked']++;

            $auditItem = $this->buildAuditItem($contract);

            if (!$auditItem) {
                continue;
            }

            $results[] = $auditItem;
            $summary['plans_with_issues']++;
            $summary['missing_months'] += count($auditItem['missing_month_keys']);
        }

        usort($results, function ($left, $right) {
            return strcmp($left['client_name'], $right['client_name']);
        });

        return [$results, $summary];
    }

    private function buildUnpaidInvoiceAuditSnapshot()
    {
        $results = [];
        $summary = [
            'plans_checked' => 0,
            'plans_with_issues' => 0,
            'unpaid_months' => 0,
            'last_run_at' => now()->format('Y-m-d H:i:s'),
        ];

        foreach ($this->buildAuditBaseQuery()->get() as $contract) {
            $summary['plans_checked']++;

            $auditItem = $this->buildUnpaidAuditItem($contract);

            if (!$auditItem) {
                continue;
            }

            $results[] = $auditItem;
            $summary['plans_with_issues']++;
            $summary['unpaid_months'] += count($auditItem['unpaid_month_keys']);
        }

        usort($results, function ($left, $right) {
            return strcmp($left['client_name'], $right['client_name']);
        });

        return [$results, $summary];
    }

    private function buildAuditBaseQuery()
    {
        return ClientProduct::with([
            'client.user',
            'product',
            'invoices' => function ($query) {
                $query->orderBy('created_at');
            },
        ])->where('status', '1');
    }

    private function buildAuditItem(ClientProduct $contract)
    {
        $timeline = $this->resolveAuditTimeline($contract);

        if (!$timeline) {
            return null;
        }

        $existingMonthKeys = $timeline['existing_month_keys'];
        $auditStartMonth = $timeline['audit_start_month'];
        $auditEndMonth = $timeline['audit_end_month'];

        $missingMonthKeys = [];
        $existingLookup = array_flip($existingMonthKeys->all());
        $coveredAdvanceMonthKeys = array_flip($this->buildAdvanceCoveredMonthKeys($contract, $auditEndMonth));
        $cursor = $auditStartMonth->copy();

        while ($cursor->lte($auditEndMonth)) {
            $monthKey = $cursor->format('Y-m');

            if (!isset($existingLookup[$monthKey]) && !isset($coveredAdvanceMonthKeys[$monthKey])) {
                $missingMonthKeys[] = $monthKey;
            }

            $cursor->addMonthNoOverflow();
        }

        if (empty($missingMonthKeys)) {
            return null;
        }

        return [
            'client_product_id' => $contract->id,
            'plan_url' => url('clients/'.$contract->client_id.'/products?modeResume=true&cp='.$contract->id),
            'client_name' => optional(optional($contract->client)->user)->name_complete ?: 'Cliente sin nombre',
            'plan_name' => optional($contract->product)->name ?: 'Plan sin nombre',
            'address' => (string) ($contract->address ?: ''),
            'reference' => (string) ($contract->reference ?: ''),
            'start_month_label' => $this->monthLabel($auditStartMonth),
            'reactivated_at_label' => $contract->reactivated_at ? Carbon::parse($contract->reactivated_at)->format('Y-m-d') : null,
            'registered_months' => $existingMonthKeys->map(function ($monthKey) {
                return $this->monthLabel(Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth());
            })->values()->all(),
            'missing_months' => collect($missingMonthKeys)->map(function ($monthKey) {
                return $this->monthLabel(Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth());
            })->values()->all(),
            'missing_month_keys' => $missingMonthKeys,
        ];
    }

    private function buildUnpaidAuditItem(ClientProduct $contract)
    {
        $timeline = $this->resolveAuditTimeline($contract);

        if (!$timeline) {
            return null;
        }

        $reactivationMonth = $timeline['reactivation_month'];
        $auditStartMonth = $timeline['audit_start_month'];
        $auditEndMonth = $timeline['audit_end_month'];

        $invoicesByMonth = collect($contract->invoices)
            ->filter(function ($invoice) use ($reactivationMonth) {
                if (!$reactivationMonth) {
                    return true;
                }

                return Carbon::parse($invoice->created_at)->startOfMonth()->gte($reactivationMonth);
            })
            ->groupBy(function ($invoice) {
                return Carbon::parse($invoice->created_at)->startOfMonth()->format('Y-m');
            });

        $monthEntries = [];
        $paidMonthLabels = [];
        $unpaidMonthLabels = [];
        $unpaidMonthKeys = [];
        $cursor = $auditStartMonth->copy();

        while ($cursor->lte($auditEndMonth)) {
            $monthKey = $cursor->format('Y-m');
            $monthInvoices = $invoicesByMonth->get($monthKey, collect());

            if ($monthInvoices->isEmpty()) {
                $cursor->addMonthNoOverflow();
                continue;
            }

            $monthLabel = $this->monthLabel($cursor);
            $hasPaidInvoice = $monthInvoices->contains(function ($invoice) {
                return (string) $invoice->status === '1';
            });
            $statusLabels = $monthInvoices
                ->map(function ($invoice) {
                    return $invoice->statusVisualizacion();
                })
                ->unique()
                ->values()
                ->all();

            $monthEntries[] = [
                'key' => $monthKey,
                'label' => $monthLabel,
                'has_paid' => $hasPaidInvoice,
                'status_labels' => $statusLabels,
            ];

            if ($hasPaidInvoice) {
                $paidMonthLabels[] = $monthLabel;
            }

            $cursor->addMonthNoOverflow();
        }

        $paidMonthKeys = collect($monthEntries)
            ->filter(function ($entry) {
                return $entry['has_paid'];
            })
            ->pluck('key')
            ->values();

        if ($paidMonthKeys->count() < 2) {
            return null;
        }

        foreach ($monthEntries as $entry) {
            if ($entry['has_paid']) {
                continue;
            }

            $hasPaidBefore = $paidMonthKeys->contains(function ($paidMonthKey) use ($entry) {
                return $paidMonthKey < $entry['key'];
            });

            $hasPaidAfter = $paidMonthKeys->contains(function ($paidMonthKey) use ($entry) {
                return $paidMonthKey > $entry['key'];
            });

            if (!$hasPaidBefore || !$hasPaidAfter) {
                continue;
            }

            $unpaidMonthKeys[] = $entry['key'];
            $unpaidMonthLabels[] = $entry['label'].' ('.implode(', ', $entry['status_labels']).')';
        }

        if (empty($unpaidMonthKeys)) {
            return null;
        }

        return [
            'client_product_id' => $contract->id,
            'plan_url' => url('clients/'.$contract->client_id.'/products?modeResume=true&cp='.$contract->id),
            'client_name' => optional(optional($contract->client)->user)->name_complete ?: 'Cliente sin nombre',
            'plan_name' => optional($contract->product)->name ?: 'Plan sin nombre',
            'address' => (string) ($contract->address ?: ''),
            'reference' => (string) ($contract->reference ?: ''),
            'start_month_label' => $this->monthLabel($auditStartMonth),
            'reactivated_at_label' => $contract->reactivated_at ? Carbon::parse($contract->reactivated_at)->format('Y-m-d') : null,
            'paid_months' => $paidMonthLabels,
            'unpaid_months' => $unpaidMonthLabels,
            'unpaid_month_keys' => $unpaidMonthKeys,
        ];
    }

    private function resolveAuditTimeline(ClientProduct $contract)
    {
        $rawExistingMonthKeys = collect($contract->invoices)
            ->map(function ($invoice) {
                return Carbon::parse($invoice->created_at)->startOfMonth()->format('Y-m');
            })
            ->unique()
            ->sort()
            ->values();

        $reactivationMonth = $contract->reactivated_at
            ? Carbon::parse($contract->reactivated_at)->startOfMonth()
            : null;

        $existingMonthKeys = $rawExistingMonthKeys
            ->filter(function ($monthKey) use ($reactivationMonth) {
                if (!$reactivationMonth) {
                    return true;
                }

                return Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth()->gte($reactivationMonth);
            })
            ->values();

        if ($existingMonthKeys->isEmpty()) {
            return null;
        }

        $auditStartMonth = Carbon::createFromFormat('Y-m', $existingMonthKeys->first())->startOfMonth();
        $auditEndMonth = now()->startOfMonth();
        $latestExistingMonth = Carbon::createFromFormat('Y-m', $existingMonthKeys->last())->startOfMonth();

        if ($latestExistingMonth->gt($auditEndMonth)) {
            $auditEndMonth = $latestExistingMonth;
        }

        return [
            'reactivation_month' => $reactivationMonth,
            'existing_month_keys' => $existingMonthKeys,
            'audit_start_month' => $auditStartMonth,
            'audit_end_month' => $auditEndMonth,
        ];
    }

    private function buildAdvanceCoveredMonthKeys(ClientProduct $contract, Carbon $auditEndMonth)
    {
        $paymentsAdvancement = max(0, (int) $contract->payments_advancement);
        $discountedMonth = $contract->discounted_month ? Carbon::parse($contract->discounted_month)->startOfMonth() : null;

        if ($paymentsAdvancement === 0 && !$discountedMonth) {
            return [];
        }

        $coveredMonthKeys = [];
        $coveredStart = $discountedMonth ?: now()->startOfMonth();
        $coveredCount = $discountedMonth ? max(1, $paymentsAdvancement + 1) : $paymentsAdvancement;
        $coveredCursor = $coveredStart->copy();

        for ($index = 0; $index < $coveredCount; $index++) {
            if ($coveredCursor->gt($auditEndMonth)) {
                break;
            }

            $coveredMonthKeys[] = $coveredCursor->format('Y-m');
            $coveredCursor->addMonthNoOverflow();
        }

        return array_values(array_unique($coveredMonthKeys));
    }

    private function createMissingInvoicesForPlan(ClientProduct $contract, array $missingMonthKeys)
    {
        $createdInvoices = 0;
        $planPrice = (float) optional($contract->product)->price;

        foreach ($missingMonthKeys as $monthKey) {
            $month = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $invoiceExists = Invoice::where('client_product_id', $contract->id)
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $monthEnd->copy()->endOfDay()])
                ->exists();

            if ($invoiceExists) {
                continue;
            }

            $createdAt = $month->copy()->day(min(5, (int) $monthEnd->format('d')))->setTime(12, 0, 0);

            Invoice::create([
                'user_id' => Auth::id(),
                'client_product_id' => $contract->id,
                'price' => $planPrice,
                'date_expiration' => $monthEnd->format('Y-m-d 23:59:59'),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'status' => $monthEnd->lt(now()) ? '-1' : '0',
            ]);

            $createdInvoices++;
        }

        $this->invoice_audit_summary['created_invoices'] = ($this->invoice_audit_summary['created_invoices'] ?? 0) + $createdInvoices;

        return $createdInvoices;
    }

    private function monthLabel(Carbon $date)
    {
        $monthNames = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return ($monthNames[(int) $date->format('n')] ?? $date->format('F')).' '.$date->format('Y');
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
