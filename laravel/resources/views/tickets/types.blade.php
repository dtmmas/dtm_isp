<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Tipos de ticket') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto"></ol>
    </x-slot>

    @livewire('ticket-type-component')

    <x-slot name="script">
        <script type="text/javascript">
            window.livewire.on('ticketTypeCrudSaved', (message) => {
                swal(message, {icon: "success"});
                $('#ticketTypeCrudModal').modal('hide');
            });

            window.livewire.on('ticketTypeCrudEdit', (message) => {
                $('#ticketTypeCrudModal').modal('show');
                $('.titulo_pagina').html(message);
            });

            window.livewire.on('ticketTypeCrudNotice', (message, icon) => {
                swal(message, {icon: icon || "success"});
            });

            function EliminarTicketType(id, name) {
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el tipo de ticket " + name + ". Esta accion es irreversible",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Eliminar",
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                })
                .then((willDelete) => {
                    if (willDelete) {
                        window.livewire.emit('onDeleteTicketTypeRow', id);
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>
