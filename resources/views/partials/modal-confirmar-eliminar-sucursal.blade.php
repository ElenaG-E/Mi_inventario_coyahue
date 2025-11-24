@foreach($sucursales as $sucursal)
<div class="modal fade" id="confirmarEliminarSucursal{{ $sucursal->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('sucursales.destroy', $sucursal) }}" method="POST" class="modal-content">
            @csrf @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Seguro que deseas eliminar la sucursal <strong>{{ $sucursal->nombre }}</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>
@endforeach