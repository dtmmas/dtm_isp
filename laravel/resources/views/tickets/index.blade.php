<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Tickets por plan') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto"></ol>
    </x-slot>

    @livewire('ticket-component')

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
