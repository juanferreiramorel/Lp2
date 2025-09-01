<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="ciudades-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ciudad</th>
                    <th>Departamento</th>
                    <th colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ciudades as $ciudad)
                    <tr>
                        <td>{{ $ciudad->id_ciudad }}</td>
                        <td>{{ $ciudad->descripcion }}</td>
                        <td>{{ $ciudad->departamento }}</td>
                        <td style="width: 120px">
                            <div class='btn-group'>
                                <a href="{{ route('ciudades.edit', [$ciudad->id_ciudad]) }}"
                                    class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-xs"
                                    onclick="openGlobalDeleteModal('{{ route('ciudades.destroy', $ciudad->id_ciudad) }}', '¿Deseas dar de baja esta ciudad?')">
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
            {{-- @include('adminlte-templates::common.paginate', ['records' => $ciudades]) --}}
        </div>
    </div>
</div>
