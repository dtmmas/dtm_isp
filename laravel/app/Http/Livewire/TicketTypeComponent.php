<?php

namespace App\Http\Livewire;

use App\Models\TicketType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TicketTypeComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $name = '';
    public $selected_id;
    public $search = '';
    public $createMode = true;

    private $pagination = 15;

    public function render()
    {
        $query = TicketType::query()
            ->withCount('tickets')
            ->orderBy('name');

        if ($this->search !== '') {
            $query->where('name', 'LIKE', '%'.$this->search.'%');
        }

        $list_ticket_types = $query->paginate($this->pagination);

        return view('livewire.ticket-type-component', compact('list_ticket_types'));
    }

    public function updatingSearch()
    {
        $this->gotoPage(1);
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->selected_id = null;
        $this->createMode = true;
    }

    public function store()
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $this->validate([
            'name' => ['required', 'string', 'max:191'],
        ]);

        $name = trim($this->name);

        if ($name === '') {
            $this->addError('name', 'Debes escribir el nombre del tipo de ticket.');
            return;
        }

        $exists = TicketType::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name, 'UTF-8')])
            ->exists();

        if ($exists) {
            $this->addError('name', 'Ya existe un tipo de ticket con ese nombre.');
            return;
        }

        TicketType::create([
            'name' => $name,
        ]);

        $this->emit('ticketTypeCrudSaved', 'Tipo de ticket creado correctamente.');
        $this->resetInputFields();
    }

    public function edit($id)
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $ticketType = TicketType::findOrFail($id);
        $this->selected_id = $ticketType->id;
        $this->name = $ticketType->name;
        $this->createMode = false;

        $this->emit('ticketTypeCrudEdit', 'Editar tipo de ticket: '.$ticketType->name);
    }

    public function update()
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $this->validate([
            'name' => ['required', 'string', 'max:191'],
        ]);

        $name = trim($this->name);

        if ($name === '') {
            $this->addError('name', 'Debes escribir el nombre del tipo de ticket.');
            return;
        }

        $exists = TicketType::query()
            ->where('id', '!=', $this->selected_id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name, 'UTF-8')])
            ->exists();

        if ($exists) {
            $this->addError('name', 'Ya existe un tipo de ticket con ese nombre.');
            return;
        }

        $ticketType = TicketType::findOrFail($this->selected_id);
        $ticketType->update([
            'name' => $name,
        ]);

        // Conserva compatibilidad con tickets historicos que aun lean title.
        $ticketType->tickets()->update(['title' => $name]);

        $this->emit('ticketTypeCrudSaved', 'Tipo de ticket actualizado correctamente.');
        $this->resetInputFields();
    }

    public function destroy($id)
    {
        abort_if(!Auth::user()->can('tickets.index'), 401);

        $ticketType = TicketType::withCount('tickets')->findOrFail($id);

        if ($ticketType->tickets_count > 0) {
            $this->emit('ticketTypeCrudNotice', 'No puedes eliminar este tipo porque ya tiene tickets asociados.', 'warning');
            return;
        }

        $ticketType->delete();
        $this->emit('ticketTypeCrudNotice', 'Tipo de ticket eliminado correctamente.', 'success');
    }

    protected $listeners = [
        'onDeleteTicketTypeRow' => 'destroy',
    ];
}
