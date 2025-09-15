<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="departamentos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripcion</th>
                    <th colspan="3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departamentos as $departamento)
                    <tr>
                        <td>{{ $departamento->id_departamento }}</td>
                        <td>{{ $departamento->descripcion }}</td>
                        <td style="width: 120px">
                            <div class='btn-group'>
                                <a href="{{ route('departamentos.edit', [$departamento->id_departamento]) }}"
                                    class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-xs"
                                    onclick="openGlobalDeleteModal('{{ route('departamentos.destroy', $departamento->id_departamento) }}', '¿Deseas dar de baja este departamento?')">
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
            {{-- @include('adminlte-templates::common.paginate', ['records' => $departamentos]) --}}
        </div>
    </div>
</div>