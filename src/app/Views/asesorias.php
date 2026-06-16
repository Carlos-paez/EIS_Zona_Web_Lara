<!-- ============================================================
     VISTA: ASESORÍA LEGAL (asesorias.php)
     Módulo de registro y validación de documentos para asesoría
     jurídica gratuita. Incluye formulario de registro, historial
     de asesorías con búsqueda y tabla informativa de documentos
     permitidos.
     NOTA: La lógica dinámica es manejada por JS en app.legal.js.
     ============================================================ -->

<div class="row">
    <div class="col s12">
        <!-- Banner de bienvenida con degradado -->
        <div class="card welcome-banner" style="background:linear-gradient(135deg, #283593 0%, #5c6bc0 100%);padding:1.75rem 2rem;">
            <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
                <div><i class="material-icons" style="font-size:2.5rem;">gavel</i></div>
                <div style="flex:1;">
                    <h2 style="font-size:1.5rem;font-weight:700;margin:0 0 0.25rem;">Asesoría Legal</h2>
                    <p style="margin:0;opacity:0.9;font-size:0.9rem;">Registro y validación de documentos para asesoría jurídica gratuita</p>
                </div>
                <div>
                    <!-- Chip que muestra cuántas asesorías se registraron hoy (actualizado por JS) -->
                    <span class="chip indigo lighten-2 white-text" style="font-size:0.8rem;padding:0.25rem 0.75rem;display:inline-flex;align-items:center;gap:0.25rem;" id="asesoriasCountChip"><i class="material-icons" style="font-size:1rem;">receipt</i> <span>0 registradas hoy</span></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Columna izquierda: Formulario de registro -->
    <div class="col s12 l5">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">note_add</i>Registrar Asesoría</span>

                <!-- Contenedor para mostrar resultado de validación de documento (oculto inicialmente) -->
                <div id="documentValidationResult" style="display:none;" class="card-panel mb-3">
                    <div id="validationMessage"></div>
                </div>

                <form id="asesoriaForm">
                    <div class="row" style="margin-bottom:0;">
                        <!-- Campo: Nombre del ciudadano -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">person</i>
                            <input type="text" id="ciudadano" name="ciudadano" required>
                            <label for="ciudadano">Nombre del Ciudadano</label>
                        </div>
                        <!-- Campo: Cédula de identidad -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">badge</i>
                            <input type="text" id="cedula" name="cedula" required>
                            <label for="cedula">Cédula de Identidad</label>
                        </div>
                        <!-- Campo: Tipo de documento con sugerencias (datalist) -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">description</i>
                            <input type="text" id="documento" name="documento" list="documentSuggestions" required placeholder="Ej: Consulta Laboral">
                            <label for="documento">Tipo de Documento / Asesoría</label>
                            <!-- Lista de sugerencias para autocompletado -->
                            <datalist id="documentSuggestions">
                                <option value="Consulta Laboral">
                                <option value="Consulta Civil">
                                <option value="Consulta Familiar">
                                <option value="Orientación Legal General">
                                <option value="Revisión de Contrato">
                                <option value="Elaboración de Documento Simple">
                                <option value="Asesoría Prevencional">
                                <option value="Juicio / Litigio">
                                <option value="Demanda Formal">
                                <option value="Apelación">
                                <option value="Recurso de Amparo">
                                <option value="Divorcio Contencioso">
                                <option value="Herencia / Sucesión">
                                <option value="Penal / Delito">
                            </datalist>
                            <small class="grey-text" style="font-size:0.75rem;">Selecciona o escribe el tipo de documento</small>
                        </div>
                        <!-- Campo: Descripción del motivo de la consulta -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">notes</i>
                            <textarea id="descripcion" name="descripcion" class="materialize-textarea"></textarea>
                            <label for="descripcion">Descripción / Motivo de la Consulta</label>
                        </div>
                        <!-- Botón de envío (deshabilitado hasta validar el documento) -->
                        <div class="col s12" style="margin-top:0.5rem;">
                            <button type="submit" class="btn waves-effect waves-light indigo" id="btnRegistrar" style="width:100%;" disabled>
                                <i class="material-icons left">verified</i>Validar y Registrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Columna derecha: Historial e información -->
    <div class="col s12 l7">
        <!-- Tarjeta: Historial de asesorías registradas -->
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">history</i>Historial de Asesorías
                    <!-- Badge con el total de asesorías (actualizado por JS) -->
                    <span class="badge indigo white-text" id="totalAsesoriasBadge" style="font-size:0.85rem;border-radius:4px;">0</span>
                </span>
                <div class="row" style="margin-bottom:0;">
                    <!-- Buscador en el historial -->
                    <div class="input-field col s12">
                        <i class="material-icons prefix">search</i>
                        <input type="text" id="searchAsesoria" placeholder="Buscar por ciudadano, cédula o documento...">
                        <label for="searchAsesoria">Buscar en historial</label>
                    </div>
                </div>
                <!-- Tabla de asesorías (los datos los carga JS dinámicamente) -->
                <table class="striped responsive-table">
                    <thead>
                        <tr>
                            <th class="hide-on-small-only">#</th>
                            <th>Ciudadano</th>
                            <th class="hide-on-small-only">Cédula</th>
                            <th>Documento</th>
                            <th>Estado</th>
                            <th class="hide-on-small-only">Fecha</th>
                            <th class="right-align hide-on-small-only">Acción</th>
                            <th class="hide-on-med-and-up right-align">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="asesoriasTableBody">
                        <!-- Las filas se generan dinámicamente con JavaScript -->
                    </tbody>
                </table>
                <!-- Mensaje mostrado cuando no hay asesorías -->
                <div id="asesoriasEmpty" class="center-align" style="padding:2rem 0;color:var(--text-muted);">
                    <i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">gavel</i>
                    <p>No hay asesorías registradas aún.<br><small>Completa el formulario para registrar la primera.</small></p>
                </div>
            </div>
        </div>

        <!-- Tarjeta informativa: Documentos permitidos -->
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">info</i>Documentos Permitidos</span>
                <!-- Chips con los tipos de documento aceptados -->
                <div style="display:flex;flex-wrap:wrap;gap:0.35rem;">
                    <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.78rem;border-radius:4px;height:auto;padding:0.35rem 0.6rem;margin:0;">✓ Consulta Laboral</div>
                    <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.78rem;border-radius:4px;height:auto;padding:0.35rem 0.6rem;margin:0;">✓ Consulta Civil</div>
                    <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.78rem;border-radius:4px;height:auto;padding:0.35rem 0.6rem;margin:0;">✓ Consulta Familiar</div>
                    <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.78rem;border-radius:4px;height:auto;padding:0.35rem 0.6rem;margin:0;">✓ Orientación Legal General</div>
                    <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.78rem;border-radius:4px;height:auto;padding:0.35rem 0.6rem;margin:0;">✓ Revisión de Contrato</div>
                    <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.78rem;border-radius:4px;height:auto;padding:0.35rem 0.6rem;margin:0;">✓ Elaboración de Documento Simple</div>
                    <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.78rem;border-radius:4px;height:auto;padding:0.35rem 0.6rem;margin:0;">✓ Asesoría Prevencional</div>
                </div>
                <!-- Nota sobre documentos que requieren derivación -->
                <p class="grey-text" style="font-size:0.8rem;margin-top:0.75rem;"><i class="material-icons left" style="font-size:1rem;">lock</i>Documentos no listados (juicios, demandas, apelaciones, herencias, penal, etc.) requieren derivación a una oficina oficial de atención legal.</p>
            </div>
        </div>
    </div>
</div>
