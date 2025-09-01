<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="sucursales-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Descripcion</th>
                <th>Direccion</th>
                <th>Telefono</th>
                <th>Ciudad</th>
                <th colspan="3">Accion</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sucursales as $sucursal)
                <tr>
                    <td>{{ $sucursal->id_sucursal }}</td>
                    <td>{{ $sucursal->descripcion }}</td>
                    <td>{{ $sucursal->direccion }}</td>
                    <td>{{ $sucursal->telefono }}</td>
                    <td>{{ $sucursal->ciudades }}</td>
                    <td  style="width: 120px">
                        <div class='btn-group'>
                            <a href="{{ route('sucursales.edit', [$sucursal->id_sucursal]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-xs"
                                onclick="openGlobalDeleteModal('{{ route('sucursales.destroy', $sucursal->id_sucursal) }}', '¿Deseas dar de baja esta sucursal?')">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- @include('adminlte-templates::common.paginate', ['records' => $sucursales]) --}}
        </div>
    </div>
</div>
