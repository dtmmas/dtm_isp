<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Pagos ').(($mostrarPendientes)?'Pendientes':'') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
        </ol>
    </x-slot>
    @livewire('payment-component', ['invoice_id'=>null, 'mostrarPendientes'=>$mostrarPendientes])

    <x-slot name="script">
        <script type="text/javascript">            

            @can('payments.process')
            function ProcesarPago(id, no_voucher, tipo){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se "+((tipo=='1')?' aprobara ':'rechazara')+" el pago "+no_voucher+". Esta accion es irreversible",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, "+((tipo=='1')?' Aprobar ':'Rechazar'),
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                  })
                .then((willDelete) => {
                    if (willDelete) {
                        window.livewire.emit('onProcessPaymentRow',id,tipo)
                        window.livewire.on('ProcessPayment', () => {
                            swal('Pago Procesado', {icon: "success",});
                        });
                    }
                });
            }
            @endcan

            @can('payments.destroy')
            function EliminarPago(id, no_voucher){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminará el pago "+no_voucher+". Esta accion es irreversible",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Eliminar pago",
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                  })
                .then((willDelete) => {
                    if (willDelete) {
                        window.livewire.emit('onDeletePaymentRow',id)
                        window.livewire.on('PaymentDeleted', (message) => {
                            swal(message, {icon: "success",});
                        });
                    }
                });
            }
            @endcan
        </script>
    </x-slot>
</x-app-layout>
