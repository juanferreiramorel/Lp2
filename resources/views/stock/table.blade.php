    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" id="productos-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sucursal</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stock as $fila)
                        <tr>
                            <td>{{ $fila->id }}</td>
                            <td>{{ $fila->sucursal }}</td>
                            <td>{{ $fila->producto }}</td>
                            <td>{{ $fila->marca }}</td>
                            <td>{{ $fila->cantidad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            <div class="float-right">
                @include('adminlte-templates::common.paginate', ['records' => $stock])
            </div>
        </div>
    </div>
