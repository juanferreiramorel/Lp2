    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" id="productos-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Cliente</th>
                        <th>N° de Factura</th>
                        <th>Fecha de Venta</th>
                        <th>Monto Total</th>
                        <th>Estado</th>
                        <th>Fecha de Vencimiento</th>
                        <th>N° Cuotas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cuentasacobrar as $fila)
                        <tr>
                            <td>{{ $fila->nro_cuenta }}</td>
                            <td>{{ $fila->cliente }}</td>
                            <td>{{ $fila->factura_nro }}</td>
                            <td>{{ $fila->fecha_venta }}</td>
                            <td>{{ $fila->importe }}</td>
                            <td>{{ $fila->estado }}</td>
                            <td>{{ $fila->vencimiento }}</td>
                            <td>{{ $fila->nro_cuotas }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            <div class="float-right">
                @include('adminlte-templates::common.paginate', ['records' => $cuentasacobrar])
            </div>
        </div>
    </div>
