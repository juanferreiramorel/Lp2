@extends('layouts.app')

@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center mb-3">
    <h1 class="m-0">Resultados para “{{ $q ?: '—' }}”</h1>
    @if(isset($total)) <span class="badge badge-primary ml-3">{{ $total }}</span> @endif
  </div>

  <form method="GET" action="{{ route('search') }}" class="mb-4">
    <div class="input-group input-group-lg">
      <input autofocus class="form-control" type="search" name="q" value="{{ $q }}" placeholder="Buscar vistas por nombre o contenido…">
      <div class="input-group-append">
        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Buscar</button>
      </div>
    </div>
  </form>

  @php
    $hl = function($text, $q) {
      if(!$q) return e($text);
      return preg_replace('/('.preg_quote($q,'/').')/i', '<mark>$1</mark>', e($text));
    };
  @endphp

  @if(empty($groups))
    <div class="alert alert-info mb-0">
      @if($q === '')
        Escribe un término para comenzar.
      @else
        No se encontraron vistas que coincidan con <strong>{{ $q }}</strong>.
      @endif
    </div>
  @else

  <div id="groupsAccordion">
    @foreach($groups as $folder => $items)
      <div class="card mb-3 shadow-sm">
        <div class="card-header bg-light" id="h-{{ Str::slug($folder) }}" data-toggle="collapse" data-target="#c-{{ Str::slug($folder) }}" style="cursor:pointer;">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <i class="far fa-folder-open mr-1"></i>
              <strong>{{ $folder }}</strong>
            </div>
            <span class="badge badge-secondary">{{ count($items) }}</span>
          </div>
        </div>
        <div id="c-{{ Str::slug($folder) }}" class="collapse show">
          <ul class="list-group list-group-flush">
            @foreach($items as $it)
              <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="pr-3">
                    <div class="h5 mb-1">{!! $hl($it['view'], $q) !!}</div>
                    <div class="text-muted small mb-2">
                      <i class="far fa-file-code mr-1"></i>{{ $it['rel'] }}
                    </div>
                    @if($it['snippet'])
                      <div class="text-monospace small text-break">
                        {!! $hl($it['snippet'], $q) !!}…
                      </div>
                    @endif
                    <div class="mt-2">
                      <span class="badge badge-pill badge-info"><i class="far fa-clock mr-1"></i> {{ $it['updated'] }}</span>
                      <span class="badge badge-pill badge-light"><i class="far fa-hdd mr-1"></i> {{ $it['size'] }} KB</span>
                      @if($it['type']==='namespace')
                        <span class="badge badge-pill badge-warning"><i class="fas fa-tags mr-1"></i> namespace</span>
                      @endif
                    </div>
                  </div>
                  <div class="text-right">
                    {{-- Render en nueva pestaña (solo vistas sin datos dinámicos) --}}
                    <a class="btn btn-sm btn-primary mb-2" target="_blank"
                       href="{{ url('/render?view='.urlencode($it['view'])) }}">
                      <i class="far fa-eye"></i> Render
                    </a>
                    <button class="btn btn-sm btn-outline-secondary copy-btn" data-copy="{{ $it['view'] }}">
                      <i class="far fa-copy"></i> Copiar vista
                    </button>
                  </div>
                </div>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endforeach
  </div>

  @endif
</div>

@push('scripts')
<script>
  document.addEventListener('click', function(e){
    if(e.target.closest('.copy-btn')){
      const btn = e.target.closest('.copy-btn');
      const text = btn.getAttribute('data-copy') || '';
      if(!text) return;

      navigator.clipboard.writeText(text).then(function(){
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="fas fa-check"></i> Copiado';
        setTimeout(() => {
          btn.classList.add('btn-outline-secondary');
          btn.classList.remove('btn-success');
          btn.innerHTML = '<i class="far fa-copy"></i> Copiar vista';
        }, 1500);
      });
    }
  }, false);
</script>
@endpush
@endsection
