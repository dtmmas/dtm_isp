<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Reporte de Clientes') }}
    </x-slot>
    <x-slot name="opciones_nav">

    </x-slot>
    @livewire('report-client-component')
</x-app-layout>