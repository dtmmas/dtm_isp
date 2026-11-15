<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Facturas') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
        </ol>
    </x-slot>
    @livewire('invoice-component', ['pivot'=>null,'client'=>null])

    <x-slot name="script">
        <script type="text/javascript">
            function printPaymentReceipts(paymentId) {
                if (!paymentId) {
                    return;
                }

                if (window.DTMPrinter) {
                        return window.DTMPrinter.printPaymentReceipt(paymentId, {
                            allowBrowserFallback: false,
                        }).catch(function () {
                            swal('No fue posible reimprimir sin selector. Verifica que QZ Tray este activo en esta estacion.', {icon: 'warning'});
                        });
                }
            }

            function promptPaymentReceiptReprint(paymentId) {
                if (!paymentId) {
                    return;
                }

                setTimeout(function () {
                    swal({
                        title: "Ticket impreso",
                        text: "¿Desea reimprimir el ticket?",
                        icon: "info",
                        buttons: {
                            cancel: "No",
                            confirm: {
                                text: "Si, reimprimir",
                                value: true,
                                visible: true
                            },
                        },
                    }).then((shouldReprint) => {
                        if (shouldReprint) {
                            printPaymentReceipts(paymentId);
                        }
                    });
                }, 1200);
            }

            window.livewire.on('paymentUpdateStore', (message, id, paymentId) => {
                $('#ModalPagar'+id).modal('hide');
                $('#ModalPagarEfectivo'+id).modal('hide');
                swal(message, {icon: "success", buttons: false, timer: 1200});
                printPaymentReceipts(paymentId);
                promptPaymentReceiptReprint(paymentId);
            });
            
        </script>
    </x-slot>
</x-app-layout>
