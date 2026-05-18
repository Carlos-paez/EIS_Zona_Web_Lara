<!-- ============================================================
     VISTA: ASESORÍA LEGAL
     ============================================================
     Permite registrar y validar documentos para asesoría
     jurídica gratuita. Incluye un formulario de registro,
     historial de asesorías y lista de documentos permitidos.

     Renderizada dentro del layout principal por
     AsesoriasController::index().

     NOTA: La lógica de registro, validación y búsqueda es
     manejada por JavaScript en app.js (UI prototype).
     ============================================================ -->

<!-- ========== BANNER DE ENCABEZADO ========== -->
<div class="row">
    <div class="col s12">
        <!-- Banner con degradado indigo -->
        <div class="card welcome-banner" style="background:linear-gradient(135deg, #283593 0%, #5c6bc0 100%);padding:1.75rem 2rem;">
            <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
                <!-- Icono de la sección (mazo de juez) -->
                <div><i class="material-icons" style="font-size:2.5rem;">gavel</i></div>
                <div style="flex:1;">
                    <h2 style="font-size:1.5rem;font-weight:700;margin:0 0 0.25rem;">Asesoría Legal</h2>
                    <p style="margin:0;opacity:0.9;font-size:0.9rem;">Registro y validación de documentos para asesoría jurídica gratuita</p>
                </div>
                <!-- Chip con contador de registros del día (visible solo en tablets/desktop) -->
                <div class="hide-on-small-only">
                    <span class="chip indigo lighten-2 white-text" style="font-size:0.85rem;padding:0.3rem 1rem;" id="asesoriasCountChip">0 registradas hoy</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <!-- ========== COLUMNA IZQUIERDA: FORMULARIO DE REGISTRO ========== -->
    <div class="col s12 l5">
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">note_add</i>Registrar Asesoría</span>

                <!-- Contenedor para mostrar resultado de validación de documento -->
                <!-- Se muestra/oculta dinámicamente por JS -->
                <div id="documentValidationResult" style="display:none;" class="card-panel mb-3">
                    <div id="validationMessage"></div>
                </div>

                <!-- Formulario de registro de asesoría -->
                <form id="asesoriaForm">
                    <div class="row" style="margin-bottom:0;">

                        <!-- Campo: Nombre del Ciudadano -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">person</i>
                            <input type="text" id="ciudadano" name="ciudadano" required>
                            <label for="ciudadano">Nombre del Ciudadano</label>
                        </div>

                        <!-- Campo: Cédula de Identidad -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">badge</i>
                            <input type="text" id="cedula" name="cedula" required>
                            <label for="cedula">Cédula de Identidad</label>
                        </div>

                        <!-- Campo: Tipo de Documento / Asesoría -->
                        <!-- Usa datalist para sugerencias mientras el usuario escribe -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">description</i>
                            <!-- list="documentSuggestions" vincula con el datalist -->
                            <input type="text" id="documento" name="documento" list="documentSuggestions" required placeholder="Ej: Consulta Laboral">
                            <label for="documento">Tipo de Documento / Asesoría</label>
                            <!-- Datalist con sugerencias de tipos de documento -->
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

                        <!-- Campo: Descripción / Motivo de la Consulta -->
                        <div class="input-field col s12">
                            <i class="material-icons prefix">notes</i>
                            <!-- materialize-textarea: Materialize agranda el textarea automáticamente -->
                            <textarea id="descripcion" name="descripcion" class="materialize-textarea"></textarea>
                            <label for="descripcion">Descripción / Motivo de la Consulta</label>
                        </div>

                        <!-- Botón de envío -->
                        <div class="col s12" style="margin-top:0.5rem;">
                            <!-- disabled inicialmente: JS lo habilita cuando el documento es válido -->
                            <button type="submit" class="btn waves-effect waves-light indigo" id="btnRegistrar" style="width:100%;" disabled>
                                <i class="material-icons left">verified</i>Validar y Registrar
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== COLUMNA DERECHA: HISTORIAL E INFORMACIÓN ========== -->
    <div class="col s12 l7">

        <!-- ===== HISTORIAL DE ASESORÍAS ===== -->
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">history</i>Historial de Asesorías
                    <!-- Badge con total de asesorías registradas -->
                    <span class="badge indigo white-text" id="totalAsesoriasBadge" style="font-size:0.85rem;border-radius:4px;">0</span>
                </span>

                <!-- Buscador en el historial -->
                <div class="row" style="margin-bottom:0;">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">search</i>
                        <input type="text" id="searchAsesoria" placeholder="Buscar por ciudadano, cédula o documento...">
                        <label for="searchAsesoria">Buscar en historial</label>
                    </div>
                </div>

                <!-- Tabla de historial -->
                <table class="striped responsive-table">
                    <thead>
                        <tr>
                            <th>#</th>                          <!-- Número correlativo -->
                            <th>Ciudadano</th>                  <!-- Nombre del ciudadano -->
                            <th>Cédula</th>                     <!-- Número de cédula -->
                            <th>Documento</th>                  <!-- Tipo de asesoría -->
                            <th>Estado</th>                     <!-- Pendiente, Finalizada, etc. -->
                            <th>Fecha</th>                      <!-- Fecha de registro -->
                            <th class="right-align">Acción</th> <!-- Botones de acción -->
                        </tr>
                    </thead>
                    <!-- tbody llenado dinámicamente por JS -->
                    <tbody id="asesoriasTableBody">
                    </tbody>
                </table>

                <!-- Mensaje cuando no hay asesorías registradas -->
                <div id="asesoriasEmpty" class="center-align" style="padding:2rem 0;color:var(--text-muted);">
                    <i class="material-icons" style="font-size:3rem;display:block;margin-bottom:0.5rem;opacity:0.3;">gavel</i>
                    <p>No hay asesorías registradas aún.<br><small>Completa el formulario para registrar la primera.</small></p>
                </div>
            </div>
        </div>

        <!-- ===== LISTA DE DOCUMENTOS PERMITIDOS ===== -->
        <div class="card">
            <div class="card-content">
                <span class="card-title"><i class="material-icons left">info</i>Documentos Permitidos</span>

                <!-- Chips verdes con los tipos de documento que se pueden registrar -->
                <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.8rem;border-radius:4px;height:auto;padding:0.4rem 0.75rem;margin-right:0.5rem;">✓ Consulta Laboral</div>
                <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.8rem;border-radius:4px;height:auto;padding:0.4rem 0.75rem;margin-right:0.5rem;">✓ Consulta Civil</div>
                <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.8rem;border-radius:4px;height:auto;padding:0.4rem 0.75rem;margin-right:0.5rem;">✓ Consulta Familiar</div>
                <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.8rem;border-radius:4px;height:auto;padding:0.4rem 0.75rem;margin-right:0.5rem;">✓ Orientación Legal General</div>
                <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.8rem;border-radius:4px;height:auto;padding:0.4rem 0.75rem;margin-right:0.5rem;">✓ Revisión de Contrato</div>
                <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.8rem;border-radius:4px;height:auto;padding:0.4rem 0.75rem;margin-right:0.5rem;">✓ Elaboración de Documento Simple</div>
                <div class="chip green lighten-4 green-text" style="font-weight:600;font-size:0.8rem;border-radius:4px;height:auto;padding:0.4rem 0.75rem;margin-right:0.5rem;">✓ Asesoría Prevencional</div>

                <!-- Nota: documentos no listados requieren derivación -->
                <p class="grey-text" style="font-size:0.8rem;margin-top:0.75rem;">
                    <i class="material-icons left" style="font-size:1rem;">lock</i>
                    Documentos no listados (juicios, demandas, apelaciones, herencias, penal, etc.) requieren derivación a una oficina oficial de atención legal.
                </p>
            </div>
        </div>

    </div>
</div>
