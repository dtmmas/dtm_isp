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
            function printPaymentReceipts(paymentId, options) {
                if (!paymentId) {
                    return Promise.resolve(null);
                }

                if (window.DTMPrinter && typeof window.DTMPrinter.printViaBrowser === 'function') {
                    return window.DTMPrinter.printViaBrowser(paymentId).catch(function () {
                        swal('No fue posible abrir la impresion del recibo. Verifica que el navegador permita ventanas emergentes.', {icon: 'warning'});
                        return null;
                    });
                }

                return Promise.resolve(null);
            }

            window.livewire.on('paymentUpdateStore', (message, id, paymentId) => {
                $('#ModalPagar'+id).modal('hide');
                $('#ModalPagarEfectivo'+id).modal('hide');
                swal(message, {icon: "success", buttons: false, timer: 1200});
                printPaymentReceipts(paymentId);
            });
            
        </script>
    </x-slot>
</x-app-layout>
