<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TicketComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $client;
    public $selected_id;
    public $createMode = true;

    public $ticket_client_product_id = '';
    public $ticket_type_id = '';
    public $filter_client_product_id = '';
    public $filter_ticket_type_id = '';
    public $description = '';
    public $priority = 'media';
    public $status = 'pendiente';
    public $status_note = '';
    public $original_status = 'pendiente';
    public $quick_status_ticket_id;
    public $quick_status = '';
    public $quick_status_note = '';
    public $status_info_title = '';
    public $status_info_badge = '';
    public $status_info_note = '';
    public $status_info_reported_by = '';
    public $status_info_updated_at = '';
    public $new_ticket_type_name = '';
    public $show_ticket_type_form = false;

    public $filter_status = '';
    public $filter_priority = '';
    public $search = '';

    private $pagination = 15;

    public function mount($client = null, $clientProductId = null)
    {
        if (!empty($client)) {
            $this->client = $client;
        }

        if (!empty($clientProductId)) {
            $this->filter_client_product_id = (string) $clientProductId;
            $this->ticket_client_product_id = (string) $clientProductId;
        }
    }

    public function render()
    {
        $query = Ticket::with(['clientProduct.client.user', 'clientProduct.product', 'user', 'ticketType'])
        ->when($this->client, function ($subQuery) {
            $subQuery->whereHas('clientProduct', function ($clientProductQuery) {
                $clientProductQuery->where('client_id', $this->client->id);
            });
        })
        ->when(Auth::user()->hasRole('Cobrador'), function ($subQuery) {
            $allowedPlans = Auth::user()->clientProductsArray();

            if (empty($allowedPlans)) {
                $allowedPlans = [0];
            }

            $subQuery->whereIn('client_product_id', $allowedPlans);
        })
        ->when($this->filter_client_product_id != '', function ($subQuery) {
            $subQuery->where('client_product_id', $this->filter_client_product_id);
        })
        ->when($this->filter_ticket_type_id != '', function ($subQuery) {
            $subQuery->where('ticket_type_id', $this->filter_ticket_type_id);
        })
        ->when($this->filter_status != '', function ($subQuery) {
            $subQuery->where('status', $this->filter_status);
        })
        ->when($this->filter_priority != '', function ($subQuery) {
            $subQuery->where('priority', $this->filter_priority);
        })
        ->when($this->search != '', function ($subQuery) {
            $search = $this->search;

            $subQuery->where(function ($searchQuery) use ($search) {
                applyFlexibleSearch($searchQuery, $search, ['tickets.description', 'tickets.status_note', 'tickets.title'])
                ->orWhereHas('clientProduct.client.user', function ($userQuery) use ($search) {
                    applyFlexibleSearch($userQuery, $search, userSearchExpressions('users'));
                })
                ->orWhereHas('clientProduct', function ($planQuery) use ($search) {
                    applyFlexibleSearch($planQuery, $search, planSearchExpressions('client_product'));
                })
                ->orWhereHas('clientProduct.product', function ($productQuery) use ($search) {
                    applyFlexibleSearch($productQuery, $search, ['products.name']);
                })
                ->orWhereHas('user', function ($reporterQuery) use ($search) {
                    applyFlexibleSearch($reporterQuery, $search, userSearchExpressions('users'));
                })
                ->orWhereHas('ticketType', function ($typeQuery) use ($search) {
                    applyFlexibleSearch($typeQuery, $search, ['ticket_types.name']);
                });
            });
        })
        ->latest();

        $list_tickets = $query->paginate($this->pagination);
        $plan_options = $this->planOptions();
        $ticket_type_options = $this->ticketTypeOptions();

        return view('livewire.ticket-component', compact('list_tickets', 'plan_options', 'ticket_type_options'));
    }

    public function updatingSearch()
    {
        $this->gotoPage(1);
    }

    public function updatedFilterStatus()
    {
        $this->gotoPage(1);
    }

    public function updatedFilterPriority()
    {
        $this->gotoPage(1);
    }

    public function updatedFilterTicketTypeId()
    {
        $this->gotoPage(1);
    }

    public function updatedFilterClientProductId()
    {
        $this->ticket_client_product_id = $this->filter_client_product_id;
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->selected_id = null;
        $this->ticket_type_id = '';
        $this->description = '';
        $this->priority = 'media';
        $this->status = 'pendiente';
        $this->status_note = '';
        $this->original_status = 'pendiente';
        $this->ticket_client_product_id = $this->filter_client_product_id;
        $this->new_ticket_type_name = '';
        $this->show_ticket_type_form = false;
        $this->createMode = true;
    }

    public function resetQuickStatusFields()
    {
        $this->quick_status_ticket_id = null;
        $this->quick_status = '';
        $this->quick_status_note = '';
    }

    public function showStatusInfo($id)
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $ticket = Ticket::findOrFail($id);
        $this->authorizeTicket($ticket);

        $this->status_info_title = $ticket->type_name ?: 'Sin tipo';
        $this->status_info_badge = $ticket->status_html;
        $this->status_info_note = $ticket->status_note ?: 'Sin observacion registrada.';
        $this->status_info_reported_by = $ticket->reported_by ?: 'Sin usuario';
        $this->status_info_updated_at = optional($ticket->updated_at)->format('Y-m-d H:i');

        $this->emit('openTicketInfoModal');
    }

    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorizeTicket($ticket);

        $this->selected_id = $ticket->id;
        $this->ticket_client_product_id = (string) $ticket->client_product_id;
        $this->ticket_type_id = (string) $ticket->ticket_type_id;
        $this->description = $ticket->description;
        $this->priority = $ticket->priority;
        $this->status = $this->normalizeStatus($ticket->status);
        $this->status_note = $ticket->status_note;
        $this->original_status = $this->normalizeStatus($ticket->status);
        $this->show_ticket_type_form = false;
        $this->createMode = false;
    }

    public function showTicketTypeForm()
    {
        $this->show_ticket_type_form = true;
    }

    public function hideTicketTypeForm()
    {
        $this->show_ticket_type_form = false;
        $this->new_ticket_type_name = '';
        $this->resetErrorBag(['new_ticket_type_name']);
    }

    public function prepareTicketTypeCreate()
    {
        $this->hideTicketTypeForm();
    }

    public function saveTicketType()
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $data = $this->validate([
            'new_ticket_type_name' => ['required', 'string', 'max:255'],
        ], [
            'new_ticket_type_name.required' => 'Debes escribir el nombre del tipo de ticket.',
        ]);

        $name = trim($data['new_ticket_type_name']);

        if ($name === '') {
            $this->addError('new_ticket_type_name', 'Debes escribir el nombre del tipo de ticket.');
            return;
        }

        $existingType = TicketType::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name, 'UTF-8')])
            ->first();

        if ($existingType) {
            $this->ticket_type_id = (string) $existingType->id;
            $this->hideTicketTypeForm();
            $this->emit('ticketTypeSavedNotice', 'Ese tipo ya existia y fue seleccionado.');
            $this->emit('closeTicketTypeModal');
            return;
        }

        $ticketType = TicketType::create([
            'name' => $name,
        ]);

        $this->ticket_type_id = (string) $ticketType->id;
        $this->hideTicketTypeForm();
        $this->emit('ticketTypeSavedNotice', 'Tipo de ticket creado correctamente.');
        $this->emit('closeTicketTypeModal');
    }

    public function prepareQuickStatus($id, $status)
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        if (!in_array($status, ['resuelto', 'cancelado'])) {
            return;
        }

        $ticket = Ticket::findOrFail($id);
        $this->authorizeTicket($ticket);

        $this->quick_status_ticket_id = $ticket->id;
        $this->quick_status = $this->normalizeStatus($status);
        $this->quick_status_note = '';

        $this->emit('openTicketStatusModal');
    }

    public function updateQuickStatus()
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $data = $this->validate([
            'quick_status_ticket_id' => ['required', 'exists:tickets,id'],
            'quick_status' => ['required', 'in:resuelto,cancelado'],
            'quick_status_note' => ['required', 'string', 'min:3'],
        ]);

        $ticket = Ticket::findOrFail($data['quick_status_ticket_id']);
        $this->authorizeTicket($ticket);

        $message = $data['quick_status'] === 'cancelado'
            ? 'Debes escribir el motivo de cancelacion.'
            : 'Debes escribir una observacion del resultado.';

        if (trim((string) $data['quick_status_note']) === '') {
            $this->addError('quick_status_note', $message);
            return;
        }

        $ticket->update([
            'status' => $data['quick_status'],
            'status_note' => trim($data['quick_status_note']),
        ]);

        $this->emit('ticketSaved', 'Estado del ticket actualizado correctamente.');
        $this->resetQuickStatusFields();
    }

    public function save()
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $data = $this->validate([
            'ticket_client_product_id' => ['required', 'exists:client_product,id'],
            'ticket_type_id' => ['required', 'exists:ticket_types,id'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:baja,media,alta'],
        ]);

        $plan = ClientProduct::findOrFail($data['ticket_client_product_id']);
        $ticketType = TicketType::findOrFail($data['ticket_type_id']);
        $this->authorizePlan($plan);

        if ($this->createMode) {
            Ticket::create([
                'client_product_id' => $plan->id,
                'user_id' => Auth::user()->id,
                'ticket_type_id' => $ticketType->id,
                'title' => $ticketType->name,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => 'pendiente',
                'status_note' => null,
            ]);

            $message = 'Ticket creado correctamente.';
        } else {
            $ticket = Ticket::findOrFail($this->selected_id);
            $this->authorizeTicket($ticket);

            $ticket->update([
                'client_product_id' => $plan->id,
                'ticket_type_id' => $ticketType->id,
                'title' => $ticketType->name,
                'description' => $this->description,
                'priority' => $this->priority,
            ]);

            $message = 'Ticket actualizado correctamente.';
        }

        $this->emit('ticketSaved', $message);
        $this->resetInputFields();
    }

    private function planOptions()
    {
        $query = ClientProduct::query()
        ->join('clients', 'clients.id', '=', 'client_product.client_id')
        ->join('users', 'users.id', '=', 'clients.user_id')
        ->join('products', 'products.id', '=', 'client_product.product_id')
        ->select(
            'client_product.id',
            DB::raw("CONCAT(users.name,' ',users.lastname,' / ',products.name,' / ',client_product.address) AS label")
        );

        if ($this->client) {
            $query->where('client_product.client_id', $this->client->id);
        }

        if (Auth::user()->hasRole('Cobrador')) {
            $allowedPlans = Auth::user()->clientProductsArray();

            if (empty($allowedPlans)) {
                $allowedPlans = [0];
            }

            $query->whereIn('client_product.id', $allowedPlans);
        }

        return $query->orderBy('users.name')->pluck('label', 'client_product.id');
    }

    private function ticketTypeOptions()
    {
        return TicketType::query()
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    private function authorizePlan($plan)
    {
        if ($this->client && $plan->client_id != $this->client->id) {
            abort(403);
        }

        if (Auth::user()->hasRole('Cobrador')) {
            $allowedPlans = Auth::user()->clientProductsArray();
            abort_if(!in_array($plan->id, $allowedPlans), 403);
        }
    }

    private function authorizeTicket($ticket)
    {
        $this->authorizePlan($ticket->clientProduct);
    }

    private function normalizeStatus($status)
    {
        switch ($status) {
            case 'abierto':
            case 'en_proceso':
                return 'pendiente';
            case 'cerrado':
                return 'resuelto';
            default:
                return $status ?: 'pendiente';
        }
    }
}
