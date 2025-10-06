@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Ventas</h1>
                </div>
                <div class="col-sm-6">
                    <!-- Boton nueva venta -->
                    @if(!empty($caja_abierta) && \Carbon\Carbon::parse($caja_abierta->fecha_apertura)->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d'))
                    <a class="btn btn-primary float-right" 
                        href="{{ route('ventas.create') }}">
                        <i class="fas fa-plus"></i>
                        Nueva Venta
                    </a>
                     @endif

                    <!-- Boton abrir caja -->
                    <!-- Verificar si la caja esta cerrada entonces muestro el boton abrir caja -->
                    @if(empty($caja_abierta))
                    <a class="btn btn-primary float-right mr-2" href="#">
                        <i class="fas fa-cart-plus"></i>
                        Abrir Caja
                    </a>
                    @endif

                    <!-- Boton cerrar caja -->
                    <!-- Verificar si la caja esta abierta y es del dia actual entonces muestro el boton cerrar caja -->
                    @if(!empty($caja_abierta) && \Carbon\Carbon::parse($caja_abierta->fecha_apertura)->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d'))
                    <a class="btn btn-success float-right mr-2" 
                        href="#">
                        <i class="fas fa-cart-plus"></i>
                        Cerrar Caja
                    </a>
                    @endif

                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')

        <div class="clearfix"></div>

        <div class="card">
            @include('ventas.table')
        </div>
    </div>
@endsection
