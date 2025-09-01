<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre y Apellido</th>
                    <th>Username</th>
                    <th>Nro Doc.</th>
                    <th>Teléfono</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->ci }}</td>
                        <td>{{ $user->telefono }}</td>
                        <td>{{ !empty($user->fecha_ingreso) ? Carbon\Carbon::parse($user->fecha_ingreso)->format('d/m/Y') : '' }}</td>
                        <td>{{ $user->estado == true ? 'Activo' : 'Inactivo' }}</td>
                        <td style="width: 120px">
                            <div class='btn-group', title="Editar">
                                <a href="{{ route('users.edit', [$user->id]) }}" class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                    
                                </a>
                                @if($user->estado == true)
                                    <button type="button" class="btn btn-danger btn-xs"
                                        onclick="openGlobalDeleteModal('{{ route('users.destroy', $user->id) }}', '¿Deseas inactivar este usuario?')">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success btn-xs"
                                        onclick="openGlobalConfirmModal('{{ route('users.destroy', $user->id) }}', 'Activar Usuario', 'success', '¿Deseas activar este usuario?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- @include('adminlte-templates::common.paginate', ['records' => $users]) --}}
        </div>
    </div>
</div>
{{-- Holaaaa --}}