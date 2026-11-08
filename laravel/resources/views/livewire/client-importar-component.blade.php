<div>  
    <form method="post" enctype="multipart/form-data">
    <div class="form-group col-md-6">
        <input type="file" class="form-control" accept=".xlsx" wire:model="archivo">
        <span wire:loading wire:target="archivo" class="text-danger" > Cargando...</span>
        @error('archivo') <span class="text-danger error">{{ $message }}</span>@enderror
    </div>   
    <div class="form-group col-md-6">
        <span> El archivo debe ser .xlsx y tener la siguiente estructura <a href="{{asset('importar_clientes.xlsx')}}"><i class="fas fa-download"></i></a></span><br>
    </div>
    <div class="form-group col-md-6">
        <button type="button" wire:loading.attr="disabled"  wire:target="archivo,ValidateClient,ImportarClient" wire:click="ValidateClient()" class="btn btn-primary btnIniciarProceso">Iniciar Proceso</button>
    </div>
    </form>
    <div>
        <div class="validado {{(($validado)?'alert-success':'alert-secondary')}} alert {{((session()->has('validacionExcel'))?'alert-success':'alert-secondary')}}" role="alert">
            1. Validar Información
        </div>
        <div class="cargando {{(($cargando)?'alert-success':'alert-secondary')}} alert" role="alert">
            2. Cargar Información
        </div>
    </div>
</div>
