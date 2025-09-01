<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="marcas-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Descripcion</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($marcas as $marca)
                <tr>
                    <td>{{ $marca->id_marca }}</td>
                    <td>{{ $marca->descripcion }}</td>
                    <td  style="width: 120px">
                        <div class='btn-group'>
                            <a href="{{ route('marcas.edit', [$marca->id_marca]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-xs"
                                onclick="openGlobalDeleteModal('{{ route('marcas.destroy', $marca->id_marca) }}', '¿Deseas dar de baja esta marca?')">
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
            {{-- @include('adminlte-templates::common.paginate', ['records' => $marcas]) --}}
        </div>
    </div>
</div>
