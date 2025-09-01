<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="cargos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripcion</th>
                    <th colspan="3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cargos as $cargo)
                    <tr>
                        <td>{{ $cargo->id_cargo }}</td>
                        <td>{{ $cargo->descripcion }}</td>
                        <td style="width: 120px">
                            <div class='btn-group'>
                                <a href="{{ route('cargos.edit', [$cargo->id_cargo]) }}" class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-xs"
                                    onclick="openGlobalDeleteModal('{{ route('cargos.destroy', $cargo->id_cargo) }}', '¿Deseas dar de baja este cargo?')">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- @include('adminlte-templates::common.paginate', ['records' => $cargos]) --}}
        </div>
    </div>
</div>
