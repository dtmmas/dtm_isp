<div class="form-group">
    {!! Form::label('name', 'Nombre *'); !!}
    {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control mayusculas', 'placeholder'=>'Nombre', 'autocomplete'=>'off']); !!}
    @error('name') <span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    {!! Form::label('lastname', 'Apellido *'); !!}
    {!! Form::text('lastname', null, ['wire:model.defer'=> 'lastname', 'class' => 'form-control mayusculas', 'placeholder'=>'Apellido', 'autocomplete'=>'off']); !!}
    @error('lastname') <span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    {!! Form::label('dni', 'DPI *'); !!}
    {!! Form::text('dni', null, ['wire:model.defer'=> 'dni', 'class' => 'form-control mayusculas', 'placeholder'=>'DPI del usuario', 'autocomplete'=>'off']); !!}
    @error('dni') <span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    {!! Form::label('address', 'Direccion *'); !!}
    {!! Form::text('address', null, ['wire:model.defer'=> 'address', 'class' => 'form-control mayusculas', 'placeholder'=>'Direccion del usuario', 'autocomplete'=>'off']); !!}
    @error('address') <span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    {!! Form::label('phone', 'Telefono *'); !!}
    <input wire:model.defer="phone" class="form-control mayusculas" placeholder="Telefono" autocomplete="off" name="phone" type="tel" id="phone">
    @error('phone') <span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    {!! Form::label('email', 'Email *'); !!}
    {!! Form::email('email', null, ['wire:model.defer'=> 'email', 'required','class' => 'form-control minuscula', 'placeholder'=>'Email', 'autocomplete'=>'off']); !!}
    @error('email') <span class="text-danger">{{ $message }}</span>@enderror
</div>
@if (!$createMode)
<div class="form-group">
    {!! Form::label('clave', 'Contraseña'); !!}
    {!! Form::password('clave',  ['wire:model.defer'=> 'clave', 'class' => 'form-control ', 'placeholder'=>'Si desea cambiar la contraseña ingrese la nueva', 'autocomplete'=>'on']); !!}
    @error('clave') <span class="text-danger">{{ $message }}</span>@enderror
</div>
@else
<div class="form-group ">
    {!! Form::label('password', 'Contraseña'); !!}
    {!! Form::password('password',  ['wire:model.defer'=> 'password', 'class' => 'form-control ', 'placeholder'=>'Contraseña', 'autocomplete'=>'on']); !!}
    @error('password') <span class="text-danger">{{ $message }}</span>@enderror
</div>
@endif