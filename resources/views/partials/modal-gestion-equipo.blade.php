<div class="modal fade" id="modalGestionEquipo" tabindex="-1" aria-labelledby="modalGestionEquipoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('equipo.update', $equipo->id) }}">
      @csrf
      @method('PUT')
      <input type="hidden" name="categoria" value="equipo">
      <div class="modal-content">
        <div class="modal-header bg-orange text-white">
          <h5 class="modal-title" id="modalGestionEquipoLabel">Editar y Asignar Equipo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-6">
            <label class="form-label">Marca</label>
            <input type="text" name="marca" class="form-control" value="{{ $equipo->marca }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Modelo</label>
            <input type="text" name="modelo" class="form-control" value="{{ $equipo->modelo }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Número de Serie</label>
            <input type="text" name="numero_serie" class="form-control" value="{{ $equipo->numero_serie }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Estado</label>
            <select name="estado_equipo_id" class="form-select">
              @foreach($estados as $estado)
                <option value="{{ $estado->id }}" {{ $equipo->estado_equipo_id == $estado->id ? 'selected' : '' }}>
                  {{ $estado->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Proveedor</label>
            <select name="proveedor_id" class="form-select">
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}" {{ $equipo->proveedor_id == $prov->id ? 'selected' : '' }}>
                  {{ $prov->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Sucursal</label>
            <select name="sucursal_id" class="form-select">
              @foreach($sucursales as $suc)
                <option value="{{ $suc->id }}" {{ $equipo->sucursal_id == $suc->id ? 'selected' : '' }}>
                  {{ $suc->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Precio</label>
            <input type="number" name="precio" class="form-control" value="{{ $equipo->precio }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Asignar a Usuario</label>
            <select name="usuario_id" class="form-select">
              <option value="">Sin asignar</option>
              @foreach($usuarios as $usuario)
                <option value="{{ $usuario->id }}" {{ $equipo->usuario_id == $usuario->id ? 'selected' : '' }}>
                  {{ $usuario->nombre }}
                </option>
              @endforeach
            </select>
          </div>
          <!-- Campo adicional para motivo -->
          <div class="col-md-12">
            <label class="form-label">Motivo de la Asignación</label>
            <textarea name="motivo" class="form-control" rows="2" placeholder="Ej: reasignación por cambio de sucursal"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>