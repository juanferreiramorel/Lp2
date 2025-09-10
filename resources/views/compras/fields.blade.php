<!-- Proveedor -->
<div class="form-group col-sm-6">
  {!! Form::label('id_proveedor', 'Proveedor:') !!}
  {!! Form::select('id_proveedor', $proveedores ?? [], isset($compra) ? $compra->id_proveedor : null, [
      'class' => 'form-control',
      'placeholder' => 'Seleccione...',
      'required'
  ]) !!}
</div>

<!-- Fecha -->
<div class="form-group col-sm-3">
  {!! Form::label('fecha_compra', 'Fecha Compra:') !!}
  {!! Form::date('fecha_compra', isset($compra) ? $compra->fecha_compra : \Carbon\Carbon::now()->format('Y-m-d'), [
      'class' => 'form-control',
      'required'
  ]) !!}
</div>

<!-- Usuario (solo visual) -->
<div class="form-group col-sm-3">
  {!! Form::label('usuario', 'Usuario:') !!}
  <input type="text" class="form-control" value="{{ auth()->user()->name ?? '' }}" readonly>
</div>
<input type="hidden" name="user_id" value="{{ auth()->id() }}">

<!-- Total -->
<div class="form-group col-sm-3">
  {!! Form::label('total', 'Total:') !!}
  {!! Form::text('total', isset($compra) ? number_format($compra->total ?? 0, 0, ',', '.') : '0', [
      'class' => 'form-control text-right',
      'readonly',
      'id' => 'total'
  ]) !!}
</div>

{{-- sucursal --}}
<div class="form-group col-sm-3">
  {!! Form::label('id_sucursal', 'Sucursal:') !!}
  {!! Form::select('id_sucursal', $sucursales ?? [], isset($compra) ? $compra->id_sucursal : null, [
      'class' => 'form-control',
      'placeholder' => 'Seleccione...',
      'required'
  ]) !!}
</div>

@includeIf('compras.modal_producto')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // comenzar la carga con document ready
        $(document).ready(function() {
            
            /** CONSULTAR AJAX PARA LLENAR POR DEFECTO EL MODAL AL ABRIR SE CONSULTA LA URL */
            document.getElementById('buscar').addEventListener('click', function() {
                $('#productSearchModal').modal('show'); // Mostrar el modal
                let query = document.getElementById('productSearchQuery').value;
                fetch('{{ url('buscar-productoscompras') }}?query=' + query + '&cod_suc=' + $("#id_sucursal").val())// capturar valor de sucursal utilzando val()
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalResults').innerHTML = html; // innerHTML es para cargar en el modal
                    })
                    .catch(error => {
                        console.error('Error:', error); // mostrar error en consola
                    });
            });
        });
    </script>
@endpush