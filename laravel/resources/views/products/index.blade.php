<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Planes') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('product-component')

    <x-slot name="script">
        

        
        <script type="text/javascript"> 
        
            window.livewire.on('productUpdateStore', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });
            window.livewire.on('productEditCreate', (message) => {
                $('#exampleModal').modal('show');
                $('.titulo_pagina').html(message)
            });
            function EliminarProduct(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el plan "+name+". Esta accion es irreversible",
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
                        window.livewire.emit('onDeleteRow',id)
                        window.livewire.on('productDelete', () => {
                            swal('Plan Eliminado', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>