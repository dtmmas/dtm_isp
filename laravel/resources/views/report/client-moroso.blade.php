<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Reporte de Clientes Morosos') }}
    </x-slot>
    <x-slot name="opciones_nav">

    </x-slot>
    @livewire('report-client-moroso-component')
</x-app-layout>