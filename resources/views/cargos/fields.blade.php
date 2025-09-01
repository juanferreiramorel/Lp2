<!-- Descripcion Field -->
<div class="form-group col-sm-12">
    <!-- Utilizando form helper para crear el campo -->
    {!! Form::label('descripcion', 'Descripcion:') !!}
    {!! Form::text('descripcion', null, 
    ['class' => 'form-control', 
    'placeholder' => 'Ingrese la descripción', 'required']) !!}

    <!-- codigo en html puro la version de abajo -->
    {{-- <label for="descripcion">Descripcion</label>
    <input type="text" name="descripcion" id="descripcion" class="form-control" 
    placeholder="Ingrese la Descripcion"> --}}
</div>