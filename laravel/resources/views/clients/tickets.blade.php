<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Tickets de '.$client->user->name_complete) }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            @can('clients.index')
            <li class="me-2">
                <a class="btn btn-sm btn-secondary" href="{{ route('clients.show', $client) }}">Ver cliente</a>
            </li>
            <li>
                <a class="btn btn-sm btn-info" href="{{ route('clients.products', $client) }}">Planes contratados</a>
            </li>
            @endcan
        </ol>
    </x-slot>

    @livewire('ticket-component', ['client' => $client, 'clientProductId' => request('client_product_id')])

    <x-slot name="script">
        <script type="text/javascript">
            window.livewire.on('ticketSaved', (message) => {
                swal(message, {icon: "success"});
                $('#ticketModal').modal('hide');
                $('#ticketStatusModal').modal('hide');
            });

            window.livewire.on('openTicketStatusModal', () => {
                $('#ticketStatusModal').modal('show');
            });

            window.livewire.on('openTicketInfoModal', () => {
                $('#ticketInfoModal').modal('show');
            });

            window.livewire.on('ticketTypeSavedNotice', (message) => {
                swal(message, {icon: "success"});
            });

            window.livewire.on('closeTicketTypeModal', () => {
                $('#ticketTypeModal').modal('hide');
            });
        </script>
    </x-slot>
</x-app-layout>
