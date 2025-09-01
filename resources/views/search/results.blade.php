@extends('layouts.app') {{-- o tu layout principal --}}

@section('content')
<div class="container mt-4">
    <h3>Resultados de búsqueda para: <em>{{ $q }}</em></h3>

    @if(!$q)
        <p class="text-muted">Escribe algo en el buscador...</p>
    @else
        <p>Resultados para: <strong>{{ $q }}</strong></p>
    @endif
</div>
@endsection
