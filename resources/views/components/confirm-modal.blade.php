<div class="modal fade" id="globalConfirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header text-white" id="confirmModalHeader">
        <h5 class="modal-title" id="confirmModalLabel">Confirmar Acción</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="confirmMessage">
        ¿Está seguro de que desea eliminar este elemento?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <form id="globalConfirmForm" method="POST">
          @csrf
          <button type="submit" class="btn btn-success">Aceptar</button>
        </form>
      </div>
    </div>
  </div>
</div>
