<form wire:submit.prevent="updatePassword">
    <div class="form-group ">
        {!! Form::label('current_password', 'Contraseña Actual'); !!}
        {!! Form::password('current_password',  ['wire:model.defer'=> 'state.current_password', 'class' => 'form-control', 'placeholder'=>'Contraseña', 'autocomplete'=>'on']); !!}
        @error('current_password') <span class="text-danger">{{ $message }}</span>@enderror
    </div>

    <div class="form-group ">
        {!! Form::label('password', 'Nueva Contraseña'); !!}
        {!! Form::password('password',  ['wire:model.defer'=> 'state.password', 'class' => 'form-control', 'placeholder'=>'Contraseña', 'autocomplete'=>'on']); !!}
        @error('password') <span class="text-danger">{{ $message }}</span>@enderror
    </div>

    <div class="form-group ">
        {!! Form::label('password_confirmation', 'Confirmar Nueva Contraseña'); !!}
        {!! Form::password('password_confirmation',  ['wire:model.defer'=> 'state.password_confirmation', 'class' => 'form-control', 'placeholder'=>'Contraseña', 'autocomplete'=>'on']); !!}
        @error('password_confirmation') <span class="text-danger">{{ $message }}</span>@enderror
    </div>
    <button type="submit" wire:loading.attr="disabled"  class="btn btn-primary">Actualizar</button>
</form>
