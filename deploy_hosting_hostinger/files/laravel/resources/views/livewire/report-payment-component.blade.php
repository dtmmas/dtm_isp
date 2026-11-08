<div>
    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#ModalPagar{{$invoice->id}}">
        Pago con Deposito
    </button>

    <!-- Modal PAGAR-->
    <div wire:ignore.self class="modal fade" id="ModalPagar{{$invoice->id}}" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalPagar{{$invoice->id}}Label" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalPagarLabel">Reportar Pago</h5>
            </div>
            <div class="modal-body">
                {!! Form::open(['id' => 'FormPayment', 'method' => 'POST', 'files' => true]) !!}
                    <div class="form-group">
                        {!! Form::label('no_voucher', 'No. de Tranferecia *'); !!}
                        {!! Form::text('no_voucher', null, ['wire:model.defer'=> 'no_voucher', 'class' => 'form-control', 'placeholder'=>'No. de Tranferecia', 'autocomplete'=>'off']); !!}
                        @error('no_voucher') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
            
                    @if (false)
                    <div class="form-group">
                        {!! Form::label('price', 'Monto *'); !!}
                        {!! Form::text('price', null, ['wire:model'=> 'price', 'class' => 'form-control', 'placeholder'=>'Monto', 'autocomplete'=>'off', 'onlyread']); !!}
                        @error('price') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    @endif
            
                    <div class="form-group">
                        {!! Form::label('img_voucher', 'Adjuntar Tranferecia'); !!}
                        {!! Form::file('img_voucher',  ['wire:model'=> 'img_voucher', 'class' => 'form-control', 'placeholder'=>'Adjuntar Tranferecia', 'accept'=>'image/*']); !!}
                        @error('img_voucher') <span class="text-danger">{{ $message }}</span>@enderror
                        <span wire:loading wire:target="img_voucher" class="text-danger" > Cargando...</span>
                    </div>
            
                    <div class="form-group">
                        {!! Form::label('observation', 'Observación'); !!}
                        {!! Form::text('observation', null, ['wire:model.defer'=> 'observation', 'class' => 'form-control', 'placeholder'=>'Observación']); !!}
                        @error('observation') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                {!! Form::close() !!}             
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:loading.attr="disabled" wire:target="img_voucher,store" wire:click="store()" class="btn btn-primary">Enviar Pago</button>
            </div>
            </div>
        </div>
    </div>
   
    @if (Auth::user()->hasRole('Cobrador') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Secretaria'))
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#ModalPagarEfectivo{{$invoice->id}}">
            Pago en Efectivo
        </button>

        <!-- Modal PAGAR-->
        <div wire:ignore.self class="modal fade" id="ModalPagarEfectivo{{$invoice->id}}" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalPagarEfectivo{{$invoice->id}}Label" aria-hidden="true">
            <div class="modal-dialog ">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalPagarEfectivoLabel">Reportar Pago</h5>
                </div>
                <div class="modal-body">
                    <p>Seguro que desea reportar el pago con efectivo</p>            
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" wire:loading.attr="disabled" wire:target="img_voucher,store" wire:click="store('3')" class="btn btn-primary">Enviar Pago</button>
                </div>
                </div>
            </div>
        </div>
    @endif
</div>
