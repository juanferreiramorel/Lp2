@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stock</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')

        <div class="clearfix">
            <form id="form-busqueda">
                <div class="row">
                    <div class="form-group col-sm-3">
                        {!! Form::label('sucursales', 'Sucursal:') !!}
                        {!! Form::select('sucursales', $sucursales, request()->get('sucursales', null), [
                            'class' => 'select2',
                            'placeholder' => 'Seleccione una sucursal',
                            'id' => 'sucursales',
                        ]) !!}
                    </div>
                    <div class="form-group col-sm-3">
                        {!! Form::label('productos', 'Producto:') !!}
                        {!! Form::select('productos', $productos, request()->get('productos', null), [
                            'class' => 'select2',
                            'placeholder' => 'Seleccione un producto',
                            'id' => 'productos',
                        ]) !!}
                    </div>
                    {{-- limpiar filtros --}}
                    <div class="form-group col-sm-2">
                        <button class="btn btn-default" type="button" 
                            style="margin-top:32px"
                            id="btn-limpiar"
                            data-toggle="tooltip" data-placement="top"
                            title="Limpiar filtros">
                            <i class="fas fa fa-broom"></i> Limpiar
                        </button>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control buscar" name="buscar" 
                        value="{{ request()->get('buscar', null) }}"
                        placeholder="Buscar..."
                        data-url="{{ url()->current() }}"
                        aria-describedby="button-addon2">
                    <button class="btn btn-outline-secondary"
                    type="submit" id="button-addon2">Buscar</button>
                </div>
            </form>
        </div>

        <!-- agregar la clase tabla-container para mostrar los valores filtrados de table-->
        <div class="card tabla-container">
            @include('stock.table')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // limpiar filtros con jquery
            $('#btn-limpiar').click(function() {
                $('#sucursales').val('').trigger('change');
                $('#productos').val('').trigger('change');
                $('.buscar').val('');
                $('#form-busqueda').submit();
            });
        });
    </script>
@endpush
