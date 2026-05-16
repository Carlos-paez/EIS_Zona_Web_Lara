<!-- ============================================================
     VISTA: CONTROL DE CYBERCAFÉ
     Panel de monitoreo y gestión de estaciones de cybercafé.
     Incluye resumen de estado, filtros visuales y un grid de
     estaciones (disponible/ocupada/mantenimiento).
     La lógica de filtrado y cambio de estado se maneja por JS.
     NOTA: Todos los datos son estáticos (UI prototype).
     ============================================================ -->

<!-- Resumen de estaciones (tarjetas KPI) -->
<div class="row" style="margin-bottom:1.5rem;">
    <!-- Contador de estaciones disponibles -->
    <div class="col s6 m3">
        <div class="card" style="margin:0;padding:1rem;text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:var(--success);" id="countDisponibles">5</div>
            <div style="color:var(--text-muted);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">Disponibles</div>
        </div>
    </div>
    <!-- Contador de estaciones ocupadas -->
    <div class="col s6 m3">
        <div class="card" style="margin:0;padding:1rem;text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:var(--warning);" id="countOcupadas">3</div>
            <div style="color:var(--text-muted);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">Ocupadas</div>
        </div>
    </div>
    <!-- Contador de estaciones en mantenimiento -->
    <div class="col s6 m3">
        <div class="card" style="margin:0;padding:1rem;text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:var(--danger);" id="countMantenimiento">1</div>
            <div style="color:var(--text-muted);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">Mantenimiento</div>
        </div>
    </div>
    <!-- Total de estaciones -->
    <div class="col s6 m3">
        <div class="card" style="margin:0;padding:1rem;text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:var(--text);">9</div>
            <div style="color:var(--text-muted);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">Total Estaciones</div>
        </div>
    </div>
</div>

<!-- Barra de filtros y acciones -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-content" style="padding:0.75rem 1.25rem;">
        <div class="row" style="margin-bottom:0;">
            <!-- Filtros rápidos por estado -->
            <div class="col s12 m7" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;padding-top:0.25rem;padding-bottom:0.25rem;">
                <span style="font-weight:600;color:var(--text-muted);font-size:0.85rem;margin-right:0.5rem;">FILTRAR:</span>
                <a class="btn-small waves-effect waves-light green filter-btn active" data-filter="all" style="border-radius:20px;padding:0 1rem;">Todas</a>
                <a class="btn-small waves-effect waves-light green filter-btn" data-filter="disponible" style="border-radius:20px;padding:0 1rem;">Disponibles</a>
                <a class="btn-small waves-effect waves-light orange filter-btn" data-filter="ocupada" style="border-radius:20px;padding:0 1rem;">Ocupadas</a>
                <a class="btn-small waves-effect waves-light red filter-btn" data-filter="mantenimiento" style="border-radius:20px;padding:0 1rem;">Mantenimiento</a>
            </div>
            <!-- Botones de acción -->
            <div class="col s12 m5 right-align" style="padding-top:0.25rem;padding-bottom:0.25rem;">
                <button class="btn-small waves-effect waves-light indigo" style="border-radius:20px;"><i class="material-icons left" style="font-size:1rem;">add</i>Nueva</button>
                <button class="btn-small waves-effect waves-light grey darken-1" style="border-radius:20px;margin-left:0.25rem;"><i class="material-icons left" style="font-size:1rem;">history</i>Historial</button>
            </div>
        </div>
    </div>
</div>

<!-- Grid de estaciones de cybercafé -->
<div class="row" id="cyberGrid">
    <!-- Cada estación tiene data-status para filtrado JS y clases CSS según su estado -->
    <!-- Estación #1: Disponible -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card disponible" data-status="disponible">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">check_circle</i></div>
                <div class="station-number">#1</div>
                <div class="station-status">Disponible</div>
                <div class="station-desc">PC Gaming</div>
            </div>
        </div>
    </div>
    <!-- Estación #2: Ocupada (con precio y tiempo restante) -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card ocupada" data-status="ocupada">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">timelapse</i></div>
                <div class="station-number">#2</div>
                <div class="station-status">Ocupada</div>
                <div class="station-desc">45 min restantes</div>
                <div class="station-price">$2.50</div>
            </div>
        </div>
    </div>
    <!-- Estación #3: Disponible -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card disponible" data-status="disponible">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">check_circle</i></div>
                <div class="station-number">#3</div>
                <div class="station-status">Disponible</div>
                <div class="station-desc">PC Estándar</div>
            </div>
        </div>
    </div>
    <!-- Estación #4: Mantenimiento -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card mantenimiento" data-status="mantenimiento">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">build</i></div>
                <div class="station-number">#4</div>
                <div class="station-status">Mantenimiento</div>
                <div class="station-desc">Teclado dañado</div>
            </div>
        </div>
    </div>
    <!-- Estación #5: Ocupada -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card ocupada" data-status="ocupada">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">timelapse</i></div>
                <div class="station-number">#5</div>
                <div class="station-status">Ocupada</div>
                <div class="station-desc">1h 20 min restantes</div>
                <div class="station-price">$4.50</div>
            </div>
        </div>
    </div>
    <!-- Estación #6: Disponible -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card disponible" data-status="disponible">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">check_circle</i></div>
                <div class="station-number">#6</div>
                <div class="station-status">Disponible</div>
                <div class="station-desc">PC Gaming</div>
            </div>
        </div>
    </div>
    <!-- Estación #7: Ocupada -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card ocupada" data-status="ocupada">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">timelapse</i></div>
                <div class="station-number">#7</div>
                <div class="station-status">Ocupada</div>
                <div class="station-desc">30 min restantes</div>
                <div class="station-price">$1.50</div>
            </div>
        </div>
    </div>
    <!-- Estación #8: Disponible -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card disponible" data-status="disponible">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">check_circle</i></div>
                <div class="station-number">#8</div>
                <div class="station-status">Disponible</div>
                <div class="station-desc">PC Estándar</div>
            </div>
        </div>
    </div>
    <!-- Estación #9: Disponible -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card disponible" data-status="disponible">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">check_circle</i></div>
                <div class="station-number">#9</div>
                <div class="station-status">Disponible</div>
                <div class="station-desc">PC Gaming</div>
            </div>
        </div>
    </div>
    <!-- Estación #10: Ocupada -->
    <div class="col s6 m4 l3 xl2">
        <div class="station-card ocupada" data-status="ocupada">
            <div class="station-inner">
                <div class="station-icon"><i class="material-icons">timelapse</i></div>
                <div class="station-number">#10</div>
                <div class="station-status">Ocupada</div>
                <div class="station-desc">2h restantes</div>
                <div class="station-price">$6.00</div>
            </div>
        </div>
    </div>
</div>

<!-- Nota informativa -->
<p style="color:var(--text-muted);font-size:0.85rem;text-align:center;margin-top:0.5rem;">
    <i class="material-icons left" style="font-size:1rem;">info</i> Haz clic en una estación para cambiar su estado
</p>
