<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Reporte de Facturas') }}
    </x-slot>
    <x-slot name="opciones_nav">

    </x-slot>
    @livewire('report-invoice-component')
</x-app-layout>