<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="cajas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripcion</th>
                    <th>Sucursal</th>
                    <th>Punto Expedicion</th>
                    <th>Ultima Factura Impresa</th>
                    <th colspan="3">Accion</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cajas as $caja)
                    <tr>
                        <td>{{ $caja->id_caja }}</td>
                        <td>{{ $caja->descripcion }}</td>
                        <td>{{ $caja->sucursal }}</td>
                        <td>{{ $caja->punto_expedicion }}</td>
                        <td>{{ $caja->ultima_factura_impresa }}</td>
                        <td style="width: 120px">
                            <div class='btn-group'>
                                <a href="{{ route('cajas.edit', [$caja->id_caja]) }}" class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                </a>
                                 <button type="button" class="btn btn-danger btn-xs"
                                    onclick="openGlobalDeleteModal('{{ route('cajas.destroy', $caja->id_caja) }}', '¿Deseas dar de baja esta caja?')">
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
            {{-- @include('adminlte-templates::common.paginate', ['records' => $cajas]) --}}
        </div>
    </div>
</div>
