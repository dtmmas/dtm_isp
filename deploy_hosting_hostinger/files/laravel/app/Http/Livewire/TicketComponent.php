<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Collector;
use App\Models\Ticket;
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
    public $filter_client_product_id = '';
    public $title = '';
    public $description = '';
    public $priority = 'media';
    public $status = 'abierto';

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
        $query = Ticket::with(['clientProduct.client.user', 'clientProduct.product', 'user'])
        ->when($this->client, function ($subQuery) {
            $subQuery->whereHas('clientProduct', function ($clientProductQuery) {
                $clientProductQuery->where('client_id', $this->client->id);
            });
        })
        ->when(Auth::user()->hasRole('Cobrador'), function ($subQuery) {
            $allowedPlans = Collector::find(Auth::user()->id)->clientProductsArray();

            if (empty($allowedPlans)) {
                $allowedPlans = [0];
            }

            $subQuery->whereIn('client_product_id', $allowedPlans);
        })
        ->when($this->filter_client_product_id != '', function ($subQuery) {
            $subQuery->where('client_product_id', $this->filter_client_product_id);
        })
        ->when($this->filter_status != '', function ($subQuery) {
            $subQuery->where('status', $this->filter_status);
        })
        ->when($this->filter_priority != '', function ($subQuery) {
            $subQuery->where('priority', $this->filter_priority);
        })
        ->when($this->search != '', function ($subQuery) {
            $search = '%'.$this->search.'%';

            $subQuery->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'LIKE', $search)
                ->orWhere('description', 'LIKE', $search)
                ->orWhereHas('clientProduct.client.user', function ($userQuery) use ($search) {
                    $userQuery->whereRaw('CONCAT(users.name," ",users.lastname) LIKE ?', [$search])
                    ->orWhere('users.dni', 'LIKE', $search);
                })
                ->orWhereHas('clientProduct.product', function ($productQuery) use ($search) {
                    $productQuery->where('products.name', 'LIKE', $search);
                })
                ->orWhereHas('clientProduct', function ($planQuery) use ($search) {
                    $planQuery->where('address', 'LIKE', $search);
                });
            });
        })
        ->latest();

        $list_tickets = $query->paginate($this->pagination);
        $plan_options = $this->planOptions();

        return view('livewire.ticket-component', compact('list_tickets', 'plan_options'));
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

    public function updatedFilterClientProductId()
    {
        $this->ticket_client_product_id = $this->filter_client_product_id;
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->selected_id = null;
        $this->title = '';
        $this->description = '';
        $this->priority = 'media';
        $this->status = 'abierto';
        $this->ticket_client_product_id = $this->filter_client_product_id;
        $this->createMode = true;
    }

    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorizeTicket($ticket);

        $this->selected_id = $ticket->id;
        $this->ticket_client_product_id = (string) $ticket->client_product_id;
        $this->title = $ticket->title;
        $this->description = $ticket->description;
        $this->priority = $ticket->priority;
        $this->status = $ticket->status;
        $this->createMode = false;
    }

    public function save()
    {
        abort_if(!Auth::user()->can('clients.index'), 401);

        $data = $this->validate([
            'ticket_client_product_id' => ['required', 'exists:client_product,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:baja,media,alta'],
            'status' => ['required', 'in:abierto,en_proceso,cerrado'],
        ]);

        $plan = ClientProduct::findOrFail($data['ticket_client_product_id']);
        $this->authorizePlan($plan);

        if ($this->createMode) {
            Ticket::create([
                'client_product_id' => $plan->id,
                'user_id' => Auth::user()->id,
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => $this->status,
            ]);

            $message = 'Ticket creado correctamente.';
        } else {
            $ticket = Ticket::findOrFail($this->selected_id);
            $this->authorizeTicket($ticket);

            $ticket->update([
                'client_product_id' => $plan->id,
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => $this->status,
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
            $allowedPlans = Collector::find(Auth::user()->id)->clientProductsArray();

            if (empty($allowedPlans)) {
                $allowedPlans = [0];
            }

            $query->whereIn('client_product.id', $allowedPlans);
        }

        return $query->orderBy('users.name')->pluck('label', 'client_product.id');
    }

    private function authorizePlan($plan)
    {
        if ($this->client && $plan->client_id != $this->client->id) {
            abort(403);
        }

        if (Auth::user()->hasRole('Cobrador')) {
            $allowedPlans = Collector::find(Auth::user()->id)->clientProductsArray();
            abort_if(!in_array($plan->id, $allowedPlans), 403);
        }
    }

    private function authorizeTicket($ticket)
    {
        $this->authorizePlan($ticket->clientProduct);
    }
}
