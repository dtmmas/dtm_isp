<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Categorias') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('category-component')

    <x-slot name="script">
        
        <script type="text/javascript">            
            window.livewire.on('categoryUpdateStore', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });
            window.livewire.on('categoryEditCreate', (message) => {
                $('#exampleModal').modal('show');
                $('.titulo_pagina').html(message)
            });
            function EliminarCategory(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara la categoria "+name+". Esta accion es irreversible",
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
                        window.livewire.on('categoryDelete', () => {
                            swal('Categoria Eliminada', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>