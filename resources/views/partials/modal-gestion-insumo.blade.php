<div class="modal fade" id="modalGestionInsumo" tabindex="-1" aria-labelledby="modalGestionInsumoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('insumo.update', $insumo->id) }}">
      @csrf
      @method('PUT')
      <input type="hidden" name="categoria" value="insumo">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="modalGestionInsumoLabel">Editar y Asignar Insumo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="{{ $insumo->nombre }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Cantidad Total</label>
            <input type="number" name="cantidad" class="form-control" value="{{ $insumo->cantidad }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Estado</label>
            <select name="estado_equipo_id" class="form-select">
              @foreach($estados as $estado)
                <option value="{{ $estado->id }}" {{ $insumo->estado_equipo_id == $estado->id ? 'selected' : '' }}>
                  {{ $estado->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Proveedor</label>
            <select name="proveedor_id" class="form-select">
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}" {{ $insumo->proveedor_id == $prov->id ? 'selected' : '' }}>
                  {{ $prov->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Sucursal</label>
            <select name="sucursal_id" class="form-select">
              @foreach($sucursales as $suc)
                <option value="{{ $suc->id }}" {{ $insumo->sucursal_id == $suc->id ? 'selected' : '' }}>
                  {{ $suc->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Precio</label>
            <input type="number" name="precio" class="form-control" value="{{ $insumo->precio }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Asignar a Usuario</label>
            <select name="usuario_id" class="form-select">
              <option value="">Sin asignar</option>
              @foreach($usuarios as $usuario)
                <option value="{{ $usuario->id }}" {{ $insumo->usuario_id == $usuario->id ? 'selected' : '' }}>
                  {{ $usuario->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <!-- Campos adicionales para historial -->
          <div class="col-md-6">
            <label class="form-label">Cantidad a Asignar</label>
            <input type="number" name="cantidad_asignada" class="form-control" placeholder="Ej: 5">
          </div>
          <div class="col-md-12">
            <label class="form-label">Motivo de la Asignación</label>
            <textarea name="motivo" class="form-control" rows="2" placeholder="Ej: entrega a usuario por proyecto"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>