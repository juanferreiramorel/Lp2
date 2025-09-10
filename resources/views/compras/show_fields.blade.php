<div class="row">
  <div class="form-group col-sm-3">
    <label><strong>ID Compra:</strong></label>
    <div>{{ $compra->id_compra }}</div>
  </div>
  <div class="form-group col-sm-3">
    <label><strong>Fecha:</strong></label>
    <div>{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</div>
  </div>
  <div class="form-group col-sm-3">
    <label><strong>Usuario:</strong></label>
    <div>{{ $compra->usuario ?? ($compra->user_name ?? '') }}</div>
  </div>
  <div class="form-group col-sm-3">
    <label><strong>Total:</strong></label>
    <div class="text-right">{{ number_format($compra->total ?? 0, 0, ',', '.') }}</div>
  </div>
</div>

<div class="row">
  <div class="form-group col-sm-6">
    <label><strong>Proveedor:</strong></label>
    <div>
      @if(isset($compra->proveedor))
        {{ $compra->proveedor->nombre ?? $compra->proveedor->razon_social ?? '—' }}
      @else
        {{ $compra->proveedor ?? $compra->id_proveedor }}
      @endif
    </div>
  </div>
</div>
