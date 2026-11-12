<!-- ============================================================
     VISTA: MANUAL DE USUARIO (EIS SYSTEM)
     Guía visual paso a paso de todos los módulos del sistema.
     Las ilustraciones son renders vectoriales ubicados en
     Public/img/manual/ y se abren en tamaño completo al hacer clic.
     ============================================================ -->

<style>
    /* ===== Portada ===== */
    .manual-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1.15rem;
        padding: 2rem;
        margin-bottom: 1rem;
        border-radius: var(--radius-lg);
        color: #fff;
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 55%, var(--accent) 130%);
        box-shadow: var(--shadow-lg);
    }
    .manual-hero::before,
    .manual-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }
    .manual-hero::before { width: 220px; height: 220px; right: -60px; top: -80px; }
    .manual-hero::after { width: 150px; height: 150px; right: 100px; bottom: -95px; }
    .manual-hero-icon {
        position: relative;
        z-index: 1;
        flex: 0 0 64px;
        width: 64px;
        height: 64px;
        display: grid;
        place-items: center;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
    }
    .manual-hero-icon i { font-size: 2rem; }
    .manual-hero-body { position: relative; z-index: 1; flex: 1 1 320px; min-width: 0; }
    .manual-hero h2 { margin: 0; font-weight: 700; font-size: 1.6rem; color: #fff; line-height: 1.2; }
    .manual-hero p { margin: 0.3rem 0 0; opacity: 0.92; font-size: 0.9rem; line-height: 1.5; }
    .manual-meta-row { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.8rem; }
    .manual-meta {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.22rem 0.6rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        font-size: 0.74rem;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }
    .manual-meta i { font-size: 0.95rem; }
    .manual-hero-cta {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.5rem 0.85rem;
        border-radius: 999px;
        background: #fff;
        color: var(--primary-dark);
        font-size: 0.8rem;
        font-weight: 700;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }
    .manual-hero-cta:hover { transform: translateY(-1px); box-shadow: var(--shadow); }
    .manual-hero-cta i { font-size: 1rem; }
    .manual-hero-actions {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .manual-hero-cta--ghost {
        appearance: none;
        -webkit-appearance: none;
        border: 1px solid rgba(255, 255, 255, 0.55);
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
        font-family: inherit;
        line-height: 1.2;
        box-shadow: none;
        cursor: pointer;
    }
    .manual-hero-cta--ghost:hover {
        background: rgba(255, 255, 255, 0.28);
        box-shadow: none;
    }

    /* ===== Arranque rápido ===== */
    .manual-quickstart-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin: 0 0 0.45rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--text-muted);
    }
    .manual-quickstart-label i { font-size: 1rem; color: var(--primary); }
    .manual-quickstart {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.6rem;
        margin-bottom: 1.15rem;
    }
    .qs-card {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        background: var(--surface);
        box-shadow: var(--shadow-sm);
    }
    .qs-num {
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: var(--primary);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
    }
    [data-theme="dark"] .qs-num { background: var(--primary-light); color: #0b1026; }
    .qs-card strong { display: block; font-size: 0.88rem; line-height: 1.3; }
    .qs-card span { display: block; margin-top: 0.1rem; font-size: 0.76rem; line-height: 1.4; color: var(--text-muted); }

    /* ===== Índice ===== */
    .manual-toc { margin-bottom: 1.15rem; }
    .manual-toc .card-content { padding-bottom: 1.3rem; }
    .manual-toc-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.7rem;
        margin-bottom: 0.85rem;
    }
    .manual-toc-head .card-title {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
    }
    .manual-toc-head .card-title i { color: var(--primary); }
    [data-theme="dark"] .manual-toc-head .card-title i { color: var(--primary-light); }
    .manual-search { position: relative; flex: 1 1 220px; max-width: 330px; }
    .manual-search i {
        position: absolute;
        left: 0.72rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 1.1rem;
        pointer-events: none;
    }
    .manual-search input,
    .manual-search input:focus {
        margin: 0;
        height: 38px;
        padding: 0 0.9rem 0 2.3rem;
        border: 1px solid var(--border-light) !important;
        border-radius: 999px;
        background: var(--surface-hover);
        color: var(--text);
        font-size: 0.82rem;
        box-shadow: none !important;
        box-sizing: border-box;
    }
    .manual-search input::placeholder { color: var(--text-muted); }
    .manual-search input:focus { border-color: var(--primary) !important; }
    .manual-toc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(235px, 1fr));
        gap: 0.5rem;
    }
    .manual-toc-item {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        padding: 0.65rem 0.75rem;
        border: 1px solid var(--border-light);
        border-radius: 10px;
        background: var(--surface);
        color: var(--text);
        transition: var(--transition);
    }
    .manual-toc-item:hover,
    .manual-toc-item:focus {
        border-color: var(--primary);
        background: var(--surface-hover);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .toc-badge {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 9px;
        border: 1px solid var(--border-light);
        background: var(--surface-hover);
        color: var(--primary);
    }
    [data-theme="dark"] .toc-badge { color: var(--primary-light); }
    .toc-badge i { font-size: 1.15rem; }
    .toc-text { min-width: 0; }
    .toc-num {
        display: block;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
        line-height: 1.2;
    }
    .manual-toc-item strong { display: block; margin-top: 0.05rem; font-size: 0.84rem; line-height: 1.3; }
    .manual-toc-item small { display: block; margin-top: 0.08rem; color: var(--text-muted); font-size: 0.72rem; line-height: 1.35; }
    .manual-toc-empty { margin: 0.7rem 0 0; color: var(--text-muted); font-size: 0.82rem; }

    /* ===== Secciones ===== */
    .manual-section { scroll-margin-top: 90px; margin-bottom: 0.9rem; overflow: hidden; }
    .manual-section .card-content { padding: 1.35rem 1.45rem 1.45rem; }
    .manual-section-head { display: flex; align-items: center; gap: 0.8rem; }
    .manual-section-icon {
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        box-shadow: var(--shadow-sm);
    }
    .manual-section-icon i { font-size: 1.4rem; }
    [data-theme="dark"] .manual-section-icon { color: #0b1026; }
    .manual-kicker {
        display: block;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--primary);
        line-height: 1.2;
    }
    [data-theme="dark"] .manual-kicker { color: var(--primary-light); }
    .manual-section-head h3 { margin: 0.08rem 0 0; font-size: 1.15rem; font-weight: 700; line-height: 1.25; color: var(--text); }
    .manual-lead {
        margin: 0.5rem 0 0.85rem;
        color: var(--text-muted);
        font-size: 0.86rem;
        line-height: 1.55;
        max-width: 80ch;
    }

    /* ===== Subtítulos y pasos ===== */
    .manual-subtitle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 1.25rem 0 0.55rem;
        font-size: 0.94rem;
        font-weight: 700;
        color: var(--text);
    }
    .manual-subtitle::after {
        content: "";
        flex: 1 1 auto;
        height: 1px;
        background: var(--border-light);
    }
    .manual-subtitle i {
        flex: 0 0 26px;
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        border-radius: 7px;
        border: 1px solid var(--border-light);
        background: var(--surface-hover);
        color: var(--primary);
        font-size: 1rem;
    }
    [data-theme="dark"] .manual-subtitle i { color: var(--primary-light); }
    .manual-steps { display: grid; gap: 0.38rem; margin-bottom: 0.3rem; }
    .manual-step {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.52rem 0.62rem;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        background: var(--surface-hover);
        font-size: 0.84rem;
        line-height: 1.5;
    }
    .manual-step .step-num {
        flex: 0 0 22px;
        width: 22px;
        height: 22px;
        display: grid;
        place-items: center;
        margin-top: 0.08rem;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
    }
    [data-theme="dark"] .manual-step .step-num { background: var(--primary-light); color: #0b1026; }
    .manual-step > span:last-child { flex: 1 1 auto; min-width: 0; }

    /* ===== Figura + pasos en paralelo ===== */
    .manual-split {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 0.95rem;
        align-items: start;
        margin: 0.65rem 0 0.9rem;
    }
    .manual-split .manual-figure { margin: 0; }
    .manual-split .manual-steps { margin: 0; }

    /* ===== Figuras ===== */
    .manual-figure { margin: 0.85rem 0 1rem; }
    .manual-figure > a {
        display: block;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }
    .manual-figure > a:hover,
    .manual-figure > a:focus { border-color: var(--primary); box-shadow: var(--shadow); }
    .manual-figure img { display: block; width: 100%; height: auto; }
    .manual-figure figcaption {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem 0.45rem;
        margin-top: 0.42rem;
        font-size: 0.76rem;
        color: var(--text-muted);
        line-height: 1.4;
    }
    .manual-figure .fig-num {
        display: inline-flex;
        align-items: center;
        padding: 0.03rem 0.42rem;
        border-radius: 999px;
        border: 1px solid var(--border-light);
        background: var(--surface-hover);
        color: var(--primary);
        font-size: 0.66rem;
        font-weight: 700;
    }
    [data-theme="dark"] .manual-figure .fig-num { color: var(--primary-light); }
    .manual-figure .fig-open {
        display: inline-flex;
        align-items: center;
        gap: 0.12rem;
        margin-left: auto;
        color: var(--primary);
        font-weight: 600;
        white-space: nowrap;
    }
    [data-theme="dark"] .manual-figure .fig-open { color: var(--primary-light); }
    .manual-figure .fig-open i { font-size: 0.88rem; }

    /* ===== Avisos ===== */
    .manual-tip,
    .manual-warn {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        margin-top: 0.5rem;
        padding: 0.65rem 0.75rem;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 0.82rem;
        line-height: 1.5;
    }
    .manual-tip { background: #e8f1fb; border-left: 4px solid var(--info); }
    .manual-warn { background: #fff3e0; border-left: 4px solid var(--warning); }
    [data-theme="dark"] .manual-tip { background: #1e2a3a; }
    [data-theme="dark"] .manual-warn { background: #33291a; }
    .manual-tip > i,
    .manual-warn > i { margin-top: 0.05rem; font-size: 1.02rem; }
    .manual-tip > i { color: var(--info); }
    .manual-warn > i { color: var(--warning); }
    .manual-tip > div,
    .manual-warn > div { flex: 1 1 auto; min-width: 0; }
    .manual-tip strong,
    .manual-warn .callout-title {
        display: block;
        margin-bottom: 0.08rem;
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }
    .manual-tip strong { color: #1565c0; }
    .manual-warn .callout-title { color: #e65100; }
    [data-theme="dark"] .manual-tip strong { color: #93c5fd; }
    [data-theme="dark"] .manual-warn .callout-title { color: #fcd34d; }

    /* ===== Tarjetas y tablas ===== */
    .manual-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
        gap: 0.55rem;
        margin: 0.65rem 0 0.85rem;
    }
    .manual-grid-2 { grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); }
    .manual-box {
        padding: 0.6rem 0.75rem;
        border: 1px solid var(--border-light);
        border-left: 3px solid var(--primary);
        border-radius: 8px;
        background: var(--surface);
    }
    [data-theme="dark"] .manual-box { background: var(--surface-hover); }
    .manual-box strong { display: block; font-size: 0.84rem; line-height: 1.3; }
    .manual-box strong i { margin-right: 0.2rem; font-size: 1rem; vertical-align: -2px; color: var(--primary); }
    [data-theme="dark"] .manual-box strong i { color: var(--primary-light); }
    .manual-box span { display: block; margin-top: 0.12rem; font-size: 0.76rem; line-height: 1.4; color: var(--text-muted); }
    .manual-table {
        margin: 0.65rem 0 0.9rem;
        overflow-x: auto;
        border: 1px solid var(--border-light);
        border-radius: 10px;
    }
    .manual-table table { margin: 0; min-width: 620px; }
    .manual-table thead { background: var(--surface-hover); }
    .manual-table th {
        padding: 0.62rem 0.75rem;
        color: var(--primary);
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    [data-theme="dark"] .manual-table th { color: var(--primary-light); }
    .manual-table td { padding: 0.58rem 0.75rem; font-size: 0.8rem; line-height: 1.45; vertical-align: top; }
    .manual-table .manual-chip { white-space: normal; }
    .manual-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.04rem 0.44rem;
        border: 1px solid var(--border-light);
        border-radius: 999px;
        background: var(--surface-hover);
        color: var(--text);
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .manual-chip.ok { color: #1b5e20; background: #e8f5e9; border-color: #c8e6c9; }
    .manual-chip.bad { color: #b71c1c; background: #ffebee; border-color: #ffcdd2; }
    .manual-chip.warn { color: #e65100; background: #fff3e0; border-color: #ffe0b2; }
    [data-theme="dark"] .manual-chip.ok { background: #14301f; border-color: #1f5132; color: #86efac; }
    [data-theme="dark"] .manual-chip.bad { background: #3a1a1e; border-color: #6b2630; color: #fca5a5; }
    [data-theme="dark"] .manual-chip.warn { background: #3a2a12; border-color: #6b4a1c; color: #fcd34d; }

    /* ===== Volver al índice ===== */
    .manual-back {
        display: inline-flex;
        align-items: center;
        gap: 0.22rem;
        margin-top: 0.85rem;
        color: var(--primary);
        font-size: 0.75rem;
        font-weight: 600;
    }
    [data-theme="dark"] .manual-back { color: var(--primary-light); }
    .manual-back i { font-size: 0.92rem; }

    @media only screen and (max-width: 900px) {
        .manual-split { grid-template-columns: 1fr; }
    }
    @media only screen and (max-width: 600px) {
        .manual-hero { padding: 1.35rem 1.1rem; }
        .manual-hero h2 { font-size: 1.35rem; }
        .manual-hero-icon { flex-basis: 52px; width: 52px; height: 52px; }
        .manual-hero-icon i { font-size: 1.6rem; }
        .manual-section .card-content { padding: 1.05rem 0.95rem 1.15rem; }
        .manual-section-head h3 { font-size: 1.02rem; }
        .manual-figure .fig-open { margin-left: 0; }
        .manual-step { gap: 0.48rem; }
        .manual-table table { min-width: 520px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .manual-toc-item,
        .manual-hero-cta,
        .manual-figure > a { transition: none; }
    }

    /* ===== Exportación a PDF (impresión) =====
       Reproduce el manual tal como se ve en pantalla: se oculta el armazón
       del sistema, se fija la paleta clara y se evitan cortes dentro de
       pasos, avisos, figuras y filas de tabla. */
    @page {
        size: A4 portrait;
        margin: 12mm 11mm 14mm;
    }

    @media print {
        :root,
        html[data-theme="dark"] {
            --primary: #3949ab;
            --primary-light: #5c6bc0;
            --primary-dark: #283593;
            --accent: #7c4dff;
            --success: #43a047;
            --warning: #fb8c00;
            --danger: #e53935;
            --info: #1e88e5;
            --bg: #ffffff;
            --surface: #ffffff;
            --surface-hover: #f5f7fa;
            --text: #1f2937;
            --text-muted: #6b7280;
            --text-light: #9ca3af;
            --border: #d1d5db;
            --border-light: #e5e7eb;
            --shadow-xs: none;
            --shadow-sm: none;
            --shadow: none;
            --shadow-lg: none;
            --shadow-xl: none;
        }

        html,
        body {
            background: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body { margin: 0 !important; orphans: 2; widows: 2; }

        /* Fuera el menú lateral, la barra superior y los controles de pantalla */
        #slide-out,
        header,
        #backToTop,
        .sidenav-overlay,
        .manual-search,
        .manual-hero-cta,
        .manual-figure .fig-open,
        .manual-back,
        .manual-toc-empty { display: none !important; }

        main,
        main .container {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: none !important;
        }

        .manual-hero { box-shadow: none !important; break-inside: avoid; }
        .manual-quickstart,
        .manual-toc { break-inside: avoid; }

        /* El buscador no debe recortar el índice en el PDF */
        .manual-toc-item { display: flex !important; }

        /* Las secciones fluyen entre páginas sin recortes */
        .manual-section {
            overflow: visible !important;
            box-shadow: none !important;
            border: 1px solid var(--border-light) !important;
            break-inside: auto;
        }
        .manual-section .card-content { padding: 0.85rem 0.95rem 1rem; }

        .manual-section-head,
        .manual-subtitle { break-after: avoid; }
        .manual-step,
        .manual-tip,
        .manual-warn,
        .manual-box,
        .manual-figure,
        .manual-split,
        .manual-grid,
        .qs-card { break-inside: avoid; }

        .manual-split {
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) !important;
            gap: 0.6rem;
        }

        .manual-figure { margin: 0.35rem 0 0.55rem; }
        .manual-figure > a { box-shadow: none !important; }
        .manual-figure figcaption { font-size: 0.66rem; }

        /* Tablas: encabezado repetido y filas sin cortes */
        .manual-table { overflow: visible !important; break-inside: auto; }
        .manual-table table { width: 100% !important; min-width: 0 !important; }
        .manual-table thead { display: table-header-group; }
        .manual-table tr { break-inside: avoid; }
        .manual-table th,
        .manual-table td { padding: 0.34rem 0.42rem; font-size: 0.68rem; }

        /* Refuerzo de color aunque el navegador imprima desde el modo oscuro */
        html[data-theme="dark"] .manual-tip { background: #e8f1fb !important; }
        html[data-theme="dark"] .manual-warn { background: #fff3e0 !important; }
        html[data-theme="dark"] .manual-tip strong { color: #1565c0 !important; }
        html[data-theme="dark"] .manual-warn .callout-title { color: #e65100 !important; }
        html[data-theme="dark"] .manual-chip.ok { background: #e8f5e9 !important; border-color: #c8e6c9 !important; color: #1b5e20 !important; }
        html[data-theme="dark"] .manual-chip.bad { background: #ffebee !important; border-color: #ffcdd2 !important; color: #b71c1c !important; }
        html[data-theme="dark"] .manual-chip.warn { background: #fff3e0 !important; border-color: #ffe0b2 !important; color: #e65100 !important; }
        html[data-theme="dark"] .manual-box { background: var(--surface) !important; }
        html[data-theme="dark"] .manual-step .step-num,
        html[data-theme="dark"] .qs-num { background: var(--primary) !important; color: #fff !important; }
        html[data-theme="dark"] .manual-kicker,
        html[data-theme="dark"] .manual-toc-head .card-title i,
        html[data-theme="dark"] .toc-badge,
        html[data-theme="dark"] .manual-subtitle i,
        html[data-theme="dark"] .manual-box strong i,
        html[data-theme="dark"] .manual-table th,
        html[data-theme="dark"] .manual-figure .fig-num,
        html[data-theme="dark"] .manual-figure .fig-open { color: var(--primary) !important; }
        html[data-theme="dark"] .manual-section-icon { color: #fff !important; }

        .manual-hero,
        .manual-section-icon,
        .manual-chip,
        .manual-tip,
        .manual-warn,
        .manual-box,
        .manual-step,
        .manual-toc-item,
        .toc-badge,
        .qs-card,
        .qs-num {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<!-- ===== PORTADA ===== -->
<div class="manual-hero">
    <span class="manual-hero-icon"><i class="material-icons">menu_book</i></span>
    <div class="manual-hero-body">
        <h2>Manual de Usuario</h2>
        <p>Guía visual del Sistema de Gestión Integral <strong>EIS System</strong> · Zona Web Lara</p>
        <div class="manual-meta-row">
            <span class="manual-meta"><i class="material-icons">apps</i> 14 módulos</span>
            <span class="manual-meta"><i class="material-icons">image</i> 29 figuras</span>
            <span class="manual-meta"><i class="material-icons">schedule</i> 10 min</span>
            <span class="manual-meta"><i class="material-icons">verified</i> Versión 1.2 · Nov 2026</span>
        </div>
    </div>
    <div class="manual-hero-actions">
        <a class="manual-hero-cta" href="#indice"><i class="material-icons">arrow_downward</i> Ver contenido</a>
        <button type="button" class="manual-hero-cta manual-hero-cta--ghost" id="manual-export-pdf"
            title="Genera un PDF con el mismo diseño, contenido y distribución del manual"
            aria-label="Exportar el manual de usuario a PDF">
            <i class="material-icons">picture_as_pdf</i> Exportar PDF
        </button>
    </div>
</div>

<!-- ===== ARRANQUE RÁPIDO ===== -->
<div class="manual-quickstart-label"><i class="material-icons">rocket_launch</i> Empieza en 3 pasos</div>
<div class="manual-quickstart">
    <div class="qs-card"><span class="qs-num">1</span><div><strong>Inicia sesión</strong><span>Entra con tu usuario y contraseña.</span></div></div>
    <div class="qs-card"><span class="qs-num">2</span><div><strong>Revisa el Dashboard</strong><span>Consulta ventas, stock y pendientes del día.</span></div></div>
    <div class="qs-card"><span class="qs-num">3</span><div><strong>Elige un módulo</strong><span>Trabaja desde el menú lateral.</span></div></div>
</div>

<!-- ===== ÍNDICE ===== -->
<div class="card manual-toc" id="indice">
    <div class="card-content">
        <div class="manual-toc-head">
            <span class="card-title"><i class="material-icons">toc</i> Contenido</span>
            <div class="manual-search">
                <i class="material-icons">search</i>
                <input id="manual-toc-search" type="search" placeholder="Buscar módulo…" aria-label="Buscar módulo en el manual">
            </div>
        </div>
        <div class="manual-toc-grid">
            <a class="manual-toc-item" href="#acceso">
                <span class="toc-badge"><i class="material-icons">lock_open</i></span>
                <span class="toc-text"><span class="toc-num">01</span><strong>Acceso y navegación</strong><small>Inicio de sesión, menú y cierre de sesión</small></span>
            </a>
            <a class="manual-toc-item" href="#dashboard">
                <span class="toc-badge"><i class="material-icons">dashboard</i></span>
                <span class="toc-text"><span class="toc-num">02</span><strong>Panel de control</strong><small>Indicadores, ventas, stock y actividad</small></span>
            </a>
            <a class="manual-toc-item" href="#inventario">
                <span class="toc-badge"><i class="material-icons">inventory_2</i></span>
                <span class="toc-text"><span class="toc-num">03</span><strong>Inventario</strong><small>Productos, stock, precios y categorías</small></span>
            </a>
            <a class="manual-toc-item" href="#ventas">
                <span class="toc-badge"><i class="material-icons">point_of_sale</i></span>
                <span class="toc-text"><span class="toc-num">04</span><strong>Ventas (POS)</strong><small>Catálogo, carrito, cliente y cobro</small></span>
            </a>
            <a class="manual-toc-item" href="#clientes">
                <span class="toc-badge"><i class="material-icons">badge</i></span>
                <span class="toc-text"><span class="toc-num">05</span><strong>Clientes</strong><small>Registro, búsqueda, edición y eliminación</small></span>
            </a>
            <a class="manual-toc-item" href="#ordenes">
                <span class="toc-badge"><i class="material-icons">request_quote</i></span>
                <span class="toc-text"><span class="toc-num">06</span><strong>Órdenes de compra</strong><small>Solicitudes, estados y líneas de producto</small></span>
            </a>
            <a class="manual-toc-item" href="#proveedores">
                <span class="toc-badge"><i class="material-icons">storefront</i></span>
                <span class="toc-text"><span class="toc-num">07</span><strong>Proveedores</strong><small>Catálogo, contacto y RIF</small></span>
            </a>
            <a class="manual-toc-item" href="#cyber">
                <span class="toc-badge"><i class="material-icons">computer</i></span>
                <span class="toc-text"><span class="toc-num">08</span><strong>Control de Ciber</strong><small>Estaciones, sesiones, cobro e historial</small></span>
            </a>
            <a class="manual-toc-item" href="#reportes">
                <span class="toc-badge"><i class="material-icons">bar_chart</i></span>
                <span class="toc-text"><span class="toc-num">09</span><strong>Reportes</strong><small>Filtros, consultas y exportación</small></span>
            </a>
            <a class="manual-toc-item" href="#activos">
                <span class="toc-badge"><i class="material-icons">devices_other</i></span>
                <span class="toc-text"><span class="toc-num">10</span><strong>Activos fijos</strong><small>Equipos, mobiliario y estaciones cyber</small></span>
            </a>
            <a class="manual-toc-item" href="#asesorias">
                <span class="toc-badge"><i class="material-icons">gavel</i></span>
                <span class="toc-text"><span class="toc-num">11</span><strong>Asesoría legal</strong><small>Validación de documentos y registro</small></span>
            </a>
            <a class="manual-toc-item" href="#usuarios">
                <span class="toc-badge"><i class="material-icons">manage_accounts</i></span>
                <span class="toc-text"><span class="toc-num">12</span><strong>Usuarios</strong><small>Altas, roles, estado y contraseñas</small></span>
            </a>
            <a class="manual-toc-item" href="#roles">
                <span class="toc-badge"><i class="material-icons">admin_panel_settings</i></span>
                <span class="toc-text"><span class="toc-num">13</span><strong>Roles y permisos</strong><small>Permisos por módulo y asignación</small></span>
            </a>
            <a class="manual-toc-item" href="#consejos">
                <span class="toc-badge"><i class="material-icons">security</i></span>
                <span class="toc-text"><span class="toc-num">14</span><strong>Consejos y seguridad</strong><small>Buenas prácticas y referencia rápida</small></span>
            </a>
        </div>
        <p class="manual-toc-empty" id="manual-toc-empty" hidden>No se encontró ningún módulo. Prueba con otra palabra.</p>
    </div>
</div>

<!-- ===== 1. ACCESO Y NAVEGACIÓN ===== -->
<div class="manual-section card" id="acceso">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">lock_open</i></span>
            <div><span class="manual-kicker">Módulo 1</span><h3>Acceso y navegación</h3></div>
        </div>
        <p class="manual-lead">Entra con el usuario y la contraseña que te asignó el administrador. El sistema siempre abre en el Panel de control.</p>

        <div class="manual-subtitle"><i class="material-icons">login</i> Iniciar sesión</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/01-login.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/01-login.svg" alt="Pantalla de inicio de sesión de EIS System con campos Usuario y Contraseña" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 1</span> Pantalla de inicio de sesión. <a class="fig-open" href="Public/img/manual/01-login.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Abre la dirección del sistema en Chrome, Edge o Firefox actualizado.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Escribe tu <strong>Usuario</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Escribe tu <strong>Contraseña</strong>; se muestra oculta por seguridad.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Pulsa <span class="manual-chip">Iniciar Sesión</span>.</span></div>
            </div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>El icono de luna cambia entre modo claro y oscuro. Tu preferencia se guarda en el navegador.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>Si ves “credenciales incorrectas”, revisa <strong>Bloq Mayús</strong> y que tu usuario esté activo. Tras varios intentos, contacta al administrador.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">menu</i> Moverte por el sistema</div>
        <p class="manual-lead">El menú lateral izquierdo es tu ruta principal. En pantallas pequeñas se abre con el botón de menú.</p>
        <div class="manual-grid">
            <div class="manual-box"><strong>Dashboard</strong><span>Resumen general del negocio.</span></div>
            <div class="manual-box"><strong>Inventario</strong><span>Productos, stock y categorías.</span></div>
            <div class="manual-box"><strong>Ventas</strong><span>Punto de venta y cobro.</span></div>
            <div class="manual-box"><strong>Clientes</strong><span>Base de clientes compartida.</span></div>
            <div class="manual-box"><strong>Solicitudes</strong><span>Órdenes de Compra y Proveedores.</span></div>
            <div class="manual-box"><strong>Cyber Control</strong><span>Estaciones y sesiones del cibercafé.</span></div>
            <div class="manual-box"><strong>Reportes</strong><span>Consultas y exportaciones.</span></div>
            <div class="manual-box"><strong>Activos</strong><span>Bienes y equipos de la empresa.</span></div>
            <div class="manual-box"><strong>Asesoría Legal</strong><span>Registro y validación de casos.</span></div>
            <div class="manual-box"><strong>Configuración</strong><span>Usuarios del sistema.</span></div>
            <div class="manual-box"><strong>Roles y Permisos</strong><span>Acceso por perfil.</span></div>
            <div class="manual-box"><strong>Manual de Usuario</strong><span>Esta guía, siempre disponible.</span></div>
        </div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span>Pulsa <strong>Solicitudes</strong> para desplegar <em>Órdenes de Compra</em> y <em>Proveedores</em>.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span>La opción activa se resalta y el título de la página confirma dónde estás.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span>Al terminar, pulsa <span class="manual-chip">Cerrar Sesión</span>, sobre todo en equipos compartidos.</span></div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Si no ves un módulo, tu rol no tiene permiso. Pídeselo al administrador; no compartas credenciales para “probar” otro perfil.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 2. DASHBOARD ===== -->
<div class="manual-section card" id="dashboard">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">dashboard</i></span>
            <div><span class="manual-kicker">Módulo 2</span><h3>Panel de control</h3></div>
        </div>
        <p class="manual-lead">Es tu página de inicio. Resume ventas, stock, sesiones cyber y solicitudes pendientes.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/02-dashboard.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/02-dashboard.svg" alt="Panel de control con indicadores de ventas, stock crítico, sesiones cyber y solicitudes pendientes" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 2</span> Panel de control con indicadores y tablas de resumen. <a class="fig-open" href="Public/img/manual/02-dashboard.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">analytics</i> Leer los indicadores</div>
        <div class="manual-grid">
            <div class="manual-box"><strong>Ventas Hoy</strong><span>Dinero cobrado y transacciones del día.</span></div>
            <div class="manual-box"><strong>Stock Crítico</strong><span>Productos agotados o bajo el mínimo.</span></div>
            <div class="manual-box"><strong>Sesiones Cyber</strong><span>Estaciones ocupadas ahora mismo.</span></div>
            <div class="manual-box"><strong>Solicitudes Pend.</strong><span>Órdenes de compra sin recibir.</span></div>
        </div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span>Revisa los cuatro indicadores al empezar la jornada.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span>Consulta <strong>Ventas por Día (7 días)</strong> para comparar la semana.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span>En <strong>Stock Crítico</strong>, detecta qué reponer y ve a <em>Inventario</em> u <em>Órdenes de Compra</em>.</span></div>
            <div class="manual-step"><span class="step-num">4</span><span>En <strong>Actividad Reciente</strong>, revisa las últimas operaciones.</span></div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Los datos se recalculan al abrir la página. Si acabas de cobrar o cerrar una sesión, vuelve a entrar al Dashboard.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>El Dashboard es informativo. Para corregir una operación, entra al módulo correspondiente.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 3. INVENTARIO ===== -->
<div class="manual-section card" id="inventario">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">inventory_2</i></span>
            <div><span class="manual-kicker">Módulo 3</span><h3>Inventario</h3></div>
        </div>
        <p class="manual-lead">Controla productos, stock, costo y precio. Cada venta descuenta existencias automáticamente.</p>

        <div class="manual-subtitle"><i class="material-icons">search</i> Consultar y filtrar</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/03-inventario-listado.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/03-inventario-listado.svg" alt="Listado de inventario con indicadores, buscador, filtro de estado y tabla de productos" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 3</span> Indicadores, filtros y listado. <a class="fig-open" href="Public/img/manual/03-inventario-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Inventario</strong> desde el menú lateral.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Revisa <strong>Total Productos</strong>, <strong>Stock Crítico</strong>, <strong>Stock Bajo</strong> y <strong>Valor Total</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Busca por nombre o código y filtra por <strong>Estado</strong>: Todo, Stock OK, Crítico o Sin stock.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>La tabla muestra categoría, precio, stock y acciones.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">add_box</i> Registrar un producto</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/04-inventario-nuevo-producto.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/04-inventario-nuevo-producto.svg" alt="Modal Nuevo Producto con código, nombre, descripción, categoría, stock, costo y precio" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 4</span> Formulario <em>Nuevo Producto</em>. <a class="fig-open" href="Public/img/manual/04-inventario-nuevo-producto.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip">Nuevo Producto</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Completa <strong>Código</strong> (único), <strong>Nombre</strong> y <strong>Descripción</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Elige la <strong>Categoría</strong>; si no existe, créala en <em>Categorías</em>.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Indica <strong>Stock</strong>, <strong>Stock Mínimo</strong>, <strong>Costo</strong> y <strong>Precio</strong> (el precio no puede ser menor al costo).</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Pulsa <span class="manual-chip">Guardar</span>.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">edit</i> Editar o eliminar</div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span><strong>Editar:</strong> pulsa el lápiz, cambia los datos y guarda.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span><strong>Eliminar:</strong> pulsa el cesto rojo.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span>Confirma <em>¿Eliminar el producto "…"?</em>. Se desactiva, pero su historial se conserva.</span></div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span><strong>Stock Bajo</strong> = llegó al mínimo; <strong>Stock Crítico</strong> = llegó a cero. Ambos aparecen en el Dashboard.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>Eliminar un producto no borra las ventas anteriores. El sistema conserva la trazabilidad.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">category</i> Gestionar categorías</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/05-inventario-categorias.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/05-inventario-categorias.svg" alt="Modal Gestionar Categorías con formulario para crear y tabla de categorías existentes" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 5</span> Ventana <em>Gestionar Categorías</em>. <a class="fig-open" href="Public/img/manual/05-inventario-categorias.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip">Categorías</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Escribe el nombre y pulsa el botón verde <strong>+</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Para editar, usa el lápiz; para eliminar, el cesto y confirma.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Pulsa <span class="manual-chip">Cerrar</span>.</span></div>
            </div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Crea las categorías antes de los productos para mantener los reportes ordenados.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 4. VENTAS ===== -->
<div class="manual-section card" id="ventas">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">point_of_sale</i></span>
            <div><span class="manual-kicker">Módulo 4</span><h3>Ventas (Punto de Venta)</h3></div>
        </div>
        <p class="manual-lead">Registra una venta en pocos pasos. El sistema descuenta el stock automáticamente.</p>

        <div class="manual-subtitle"><i class="material-icons">add_shopping_cart</i> Agregar productos al carrito</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/06-ventas-catalogo.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/06-ventas-catalogo.svg" alt="Punto de Venta con catálogo de productos, buscador, total del carrito y botón Carrito" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 6</span> Catálogo del Punto de Venta. <a class="fig-open" href="Public/img/manual/06-ventas-catalogo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Ventas</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Busca el producto por nombre.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Haz clic en la tarjeta para agregar una unidad. Repite hasta completar la venta.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Revisa el contador y el <strong>Total carrito</strong>; luego pulsa <span class="manual-chip">Carrito</span>.</span></div>
            </div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Si no hay stock, el sistema avisa y no permite superar la cantidad disponible.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">shopping_cart</i> Revisar el carrito y el cliente</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/07-ventas-carrito.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/07-ventas-carrito.svg" alt="Carrito de Compras con productos, cantidades, total y formulario de datos del cliente" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 7</span> Carrito y datos del cliente. <a class="fig-open" href="Public/img/manual/07-ventas-carrito.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Verifica productos, precio unitario y subtotal.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Ajusta la <strong>cantidad</strong> o quita una línea con la <strong>X</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Cliente existente: selecciónalo en <strong>Cliente registrado</strong>.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Cliente nuevo: escribe <strong>Cliente</strong> y <strong>Cédula</strong>. Al salir de la cédula se precargan dirección y teléfono si existen.</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Completa <strong>Dirección</strong> y <strong>Teléfono</strong> si hace falta y revisa el <strong>TOTAL</strong>.</span></div>
            </div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>El cliente se reutiliza por cédula: no se duplica; solo se actualizan sus datos de contacto.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span><span class="manual-chip bad">Vaciar</span> elimina todo el carrito y pide confirmación. Úsalo solo para empezar de cero.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">paid</i> Procesar la venta</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/08-ventas-confirmacion.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/08-ventas-confirmacion.svg" alt="Confirmación de venta registrada con número de orden y total cobrado" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 8</span> Confirmación y comprobante. <a class="fig-open" href="Public/img/manual/08-ventas-confirmacion.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip ok">Procesar</span> y confirma <em>¿Procesar venta por $…?</em>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>El sistema genera la orden, descuenta el stock y muestra la confirmación.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>El carrito se vacía y el catálogo se actualiza.</span></div>
            </div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>No recargues la página justo después de cobrar: podrías reenviar la operación.</span></div></div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Si el cobro fue un error, no borres la venta desde aquí. Sigue el procedimiento interno y consulta al administrador.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 5. CLIENTES ===== -->
<div class="manual-section card" id="clientes">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">badge</i></span>
            <div><span class="manual-kicker">Módulo 5</span><h3>Clientes</h3></div>
        </div>
        <p class="manual-lead">Una sola base de clientes para Ventas, Cyber y Asesoría Legal. Evita duplicados.</p>

        <div class="manual-subtitle"><i class="material-icons">manage_search</i> Buscar, editar y eliminar</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/09-clientes-listado.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/09-clientes-listado.svg" alt="Listado de clientes con buscador y tabla de cédula, nombre, dirección y teléfono" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 9</span> Listado de clientes. <a class="fig-open" href="Public/img/manual/09-clientes-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Busca por nombre, cédula o teléfono.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span><strong>Editar:</strong> lápiz → cambia los datos → guarda.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span><strong>Eliminar:</strong> cesto → confirma.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">person_add</i> Registrar un cliente</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/10-clientes-nuevo.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/10-clientes-nuevo.svg" alt="Modal Nuevo Cliente con cédula, nombre, apellido, dirección y teléfono" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 10</span> Formulario <em>Nuevo Cliente</em>. <a class="fig-open" href="Public/img/manual/10-clientes-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Clientes</strong> y pulsa <span class="manual-chip">Nuevo Cliente</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Completa <strong>Cédula</strong>, <strong>Nombre</strong>, <strong>Apellido</strong>, <strong>Dirección</strong> y <strong>Teléfono</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Pulsa <span class="manual-chip">Guardar</span>. Queda disponible en todos los módulos.</span></div>
            </div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>No se puede eliminar un cliente con <strong>asesorías, ventas o sesiones cyber</strong>. El sistema conserva el historial.</span></div></div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Busca por cédula antes de crear. Un duplicado complica los reportes por cliente.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 6. ÓRDENES DE COMPRA ===== -->
<div class="manual-section card" id="ordenes">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">request_quote</i></span>
            <div><span class="manual-kicker">Módulo 6</span><h3>Órdenes de compra</h3></div>
        </div>
        <p class="manual-lead">Solicita productos a proveedores. Cada orden tiene una cabecera y una o más líneas.</p>

        <div class="manual-subtitle"><i class="material-icons">search</i> Consultar solicitudes</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/11-ordenes-listado.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/11-ordenes-listado.svg" alt="Listado de órdenes de compra con indicadores, filtros y acciones por solicitud" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 11</span> Listado de Órdenes de Compra. <a class="fig-open" href="Public/img/manual/11-ordenes-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Solicitudes → Órdenes de Compra</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Revisa <strong>Total Solicitudes</strong>, <strong>Pendientes</strong> y <strong>Recibidas</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Busca por proveedor o número y filtra por <strong>Estado</strong>.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>La tabla muestra número, proveedor, RIF, fecha, estado y acciones.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">note_add</i> Crear una orden</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/12-ordenes-nueva.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/12-ordenes-nueva.svg" alt="Modal Nueva Solicitud con número automático, fecha, proveedor y estado" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 12</span> Cabecera de una <em>Nueva Solicitud</em>. <a class="fig-open" href="Public/img/manual/12-ordenes-nueva.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip">Nueva Solicitud</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>El <strong>Número de Orden</strong> se genera solo; confirma la <strong>Fecha</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Elige <strong>Proveedor</strong> y <strong>Estado</strong> inicial.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Pulsa <span class="manual-chip">Guardar</span> para habilitar las líneas.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">playlist_add</i> Agregar productos</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/13-ordenes-detalle-lineas.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/13-ordenes-detalle-lineas.svg" alt="Detalle de orden con tabla de productos, cantidades, precios, subtotales y total" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 13</span> Líneas de producto y total. <a class="fig-open" href="Public/img/manual/13-ordenes-detalle-lineas.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>En <strong>Agregar Producto</strong>, elige el producto; el precio de compra se carga y puedes ajustarlo.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Indica la <strong>Cantidad</strong> y revisa el <strong>Precio</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Pulsa el botón verde <strong>+</strong>; la línea aparece con su subtotal.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Repite para cada producto; el <strong>Total</strong> se recalcula solo.</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Para quitar una línea, usa el cesto y confirma <em>¿Eliminar este producto de la solicitud?</em>.</span></div>
                <div class="manual-step"><span class="step-num">6</span><span>Pulsa <span class="manual-chip">Guardar</span> para cerrar la orden.</span></div>
            </div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>Guarda la cabecera antes de agregar líneas. Si cierras el modal antes, la orden puede quedar sin productos.</span></div></div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Mantén el estado actualizado (pendiente/recibida) para que los indicadores y reportes reflejen la realidad.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">visibility</i> Ver, editar o eliminar</div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span><strong>Ver:</strong> pulsa el botón gris con el ojo para consultar la orden y sus líneas.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span><strong>Editar:</strong> pulsa el lápiz; cambia la cabecera o las líneas y guarda.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span><strong>Eliminar:</strong> pulsa el cesto rojo y confirma <em>¿Eliminar la solicitud #…?</em> (también borra sus productos).</span></div>
        </div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 7. PROVEEDORES ===== -->
<div class="manual-section card" id="proveedores">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">storefront</i></span>
            <div><span class="manual-kicker">Módulo 7</span><h3>Proveedores</h3></div>
        </div>
        <p class="manual-lead">Catálogo de quienes te suministran productos. Aquí eliges el proveedor al crear una orden.</p>

        <div class="manual-subtitle"><i class="material-icons">manage_search</i> Buscar, editar y eliminar</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/14-proveedores-listado.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/14-proveedores-listado.svg" alt="Listado de proveedores con buscador y tabla de nombre, RIF, email y teléfono" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 14</span> Listado de Proveedores. <a class="fig-open" href="Public/img/manual/14-proveedores-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Busca por nombre, RIF o email.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span><strong>Editar:</strong> lápiz → ajusta → guarda.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span><strong>Eliminar:</strong> cesto rojo → confirma.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">add_business</i> Registrar un proveedor</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/15-proveedores-nuevo.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/15-proveedores-nuevo.svg" alt="Modal Nuevo Proveedor con RIF, nombre, email y teléfono" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 15</span> Formulario <em>Nuevo Proveedor</em>. <a class="fig-open" href="Public/img/manual/15-proveedores-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Solicitudes → Proveedores</strong> y pulsa <span class="manual-chip">Nuevo Proveedor</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Completa <strong>RIF</strong> y <strong>Nombre</strong> (obligatorios); agrega <strong>Email</strong> y <strong>Teléfono</strong> si los tienes.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Pulsa <span class="manual-chip">Guardar</span>.</span></div>
            </div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>No se puede eliminar un proveedor con <strong>solicitudes asociadas</strong>. El historial de compras se conserva.</span></div></div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Verifica el RIF: identifica al proveedor en las órdenes y en los reportes.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 8. CYBER ===== -->
<div class="manual-section card" id="cyber">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">computer</i></span>
            <div><span class="manual-kicker">Módulo 8</span><h3>Control de Ciber</h3></div>
        </div>
        <p class="manual-lead">Gestiona estaciones, sesiones, tiempo, cobro e historial del cibercafé.</p>

        <div class="manual-subtitle"><i class="material-icons">desktop_windows</i> Interpretar las estaciones</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/16-cyber-estaciones.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/16-cyber-estaciones.svg" alt="Panel de control de ciber con estaciones disponibles y ocupadas, filtros y botones de gestión" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 16</span> Estaciones y sus estados. <a class="fig-open" href="Public/img/manual/16-cyber-estaciones.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Cyber Control</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Revisa <strong>Disponibles</strong>, <strong>Ocupadas</strong>, <strong>Mantenimiento</strong> y <strong>Total PCs</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Filtra por <strong>Todas</strong>, <strong>Disponibles</strong> u <strong>Ocupadas</strong>.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Cada tarjeta muestra PC, tipo y estado; si está ocupada, también cliente y tiempo.</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Pulsa <span class="manual-chip">Actualizar</span> para refrescar sin recargar.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">play_circle</i> Iniciar una sesión</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/17-cyber-iniciar-sesion.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/17-cyber-iniciar-sesion.svg" alt="Modal Iniciar Sesión con datos del cliente, estación, tarifa y tiempo" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 17</span> Formulario <em>Iniciar Sesión</em>. <a class="fig-open" href="Public/img/manual/17-cyber-iniciar-sesion.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Haz clic en una estación <span class="manual-chip ok">Disponible</span> o pulsa <span class="manual-chip">Nueva Sesión</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Escribe la <strong>Cédula</strong>; se precargan nombre, teléfono y dirección si el cliente existe.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Ajusta <strong>Cliente</strong>, <strong>Teléfono</strong> y <strong>Dirección</strong>.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Elige <strong>Estación</strong> y <strong>Tarifa</strong>, e indica el <strong>Tiempo</strong> (ej. 01:30:00 = 1 h 30 min).</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Pulsa <span class="manual-chip ok">Iniciar</span>; la estación pasa a <span class="manual-chip warn">Ocupada</span> y empieza el conteo.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">stop_circle</i> Finalizar una sesión</div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span>Haz clic en la estación <span class="manual-chip warn">Ocupada</span>.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span>Confirma <em>¿Finalizar la sesión en la estación …?</em>.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span>El sistema calcula tiempo y costo, cierra la sesión y libera la estación.</span></div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Si no hay estaciones disponibles, el aviso <em>No hay estaciones disponibles</em> te indica liberar una o registrar otra PC.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">history</i> Consultar el historial</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/18-cyber-historial.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/18-cyber-historial.svg" alt="Modal Historial de Sesiones con selector de PC y tabla de cliente, tiempo, precio y estado" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 18</span> Historial por estación. <a class="fig-open" href="Public/img/manual/18-cyber-historial.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip">Historial</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Elige la PC para ver cliente, tiempo, precio y estado.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Pulsa <span class="manual-chip">Cerrar</span>.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">settings</i> Crear, editar o eliminar una PC</div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip">Nueva PC</span>.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span>Completa <strong>Marca</strong>, <strong>Descripción</strong> (ej. Intel i5, 8 GB RAM) y <strong>Tipo de PC</strong>.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span>Elige <strong>Activa</strong> o <strong>Mantenimiento</strong>; en mantenimiento no recibe sesiones.</span></div>
            <div class="manual-step"><span class="step-num">4</span><span>Pulsa <span class="manual-chip">Guardar PC</span>. Para editar usa el lápiz; para eliminar, el cesto y confirma.</span></div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>Solo puedes eliminar una PC sin sesiones. Si ya se usó, cámbiala a mantenimiento o desactívala.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 9. REPORTES ===== -->
<div class="manual-section card" id="reportes">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">bar_chart</i></span>
            <div><span class="manual-kicker">Módulo 9</span><h3>Reportes</h3></div>
        </div>
        <p class="manual-lead">Consulta datos del negocio y descárgalos en PDF, Excel o CSV. Los filtros cambian según el reporte.</p>

        <div class="manual-subtitle"><i class="material-icons">tune</i> Configurar la consulta</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/19-reportes-filtros.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/19-reportes-filtros.svg" alt="Panel de filtros estratégicos con tipo de reporte, presets de fecha, filtros contextuales y formato" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 19</span> Filtros estratégicos. <a class="fig-open" href="Public/img/manual/19-reportes-filtros.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Reportes</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Elige el <strong>Tipo de Reporte</strong>: Comercial, Inventario, Compras o Servicios.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Define el período con un preset (<strong>Hoy</strong>, <strong>Ayer</strong>, <strong>7 días</strong>, <strong>Este Mes</strong>, <strong>Mes Ant.</strong>, <strong>Este Año</strong>) o con <strong>Fecha Inicio/Fin</strong>.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Completa los filtros contextuales y elige <strong>PDF</strong>, <strong>Excel</strong> o <strong>CSV</strong>.</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Pulsa <span class="manual-chip">Consultar Reporte</span>.</span></div>
            </div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Al abrir la página se consulta automáticamente <em>Ventas por fecha</em> del <em>Este Mes</em>.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>Los reportes de inventario, stock bajo y sesiones cyber no usan fechas. La fecha inicial no puede ser posterior a la final.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">table_chart</i> Leer y exportar</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/20-reportes-resultados.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/20-reportes-resultados.svg" alt="Resultados del reporte con indicadores, tabla de datos y botón Exportar Archivo" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 20</span> Resultados y exportación. <a class="fig-open" href="Public/img/manual/20-reportes-resultados.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Revisa los indicadores de resumen sobre la tabla.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Ordena por columna o usa la paginación inferior.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Pulsa <span class="manual-chip">Exportar Archivo</span>: se descarga con el formato y los filtros activos.</span></div>
            </div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Si cambias el tipo de reporte, vuelve a consultar antes de exportar para evitar datos del reporte anterior.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 10. ACTIVOS ===== -->
<div class="manual-section card" id="activos">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">devices_other</i></span>
            <div><span class="manual-kicker">Módulo 10</span><h3>Activos fijos</h3></div>
        </div>
        <p class="manual-lead">Registra equipos, mobiliario y estaciones cyber. Un activo marcado como estación aparece en Cyber.</p>

        <div class="manual-subtitle"><i class="material-icons">inventory</i> Consultar activos</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/21-activos-listado.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/21-activos-listado.svg" alt="Listado de activos con indicadores, buscador, filtro por tipo y tabla de marca, descripción y estado" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 21</span> Listado de Activos fijos. <a class="fig-open" href="Public/img/manual/21-activos-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Activos</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Revisa <strong>Total Activos</strong>, <strong>Estaciones Cyber</strong>, <strong>Ocupadas Ahora</strong> e <strong>Inactivos</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Busca por marca, tipo o descripción y filtra por tipo.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">add_circle</i> Registrar un activo</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/22-activos-nuevo.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/22-activos-nuevo.svg" alt="Modal Nuevo Activo con marca, tipo, descripción, casilla Activo y casilla Estación de Cybercafé" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 22</span> Formulario <em>Nuevo Activo</em>. <a class="fig-open" href="Public/img/manual/22-activos-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip">Nuevo Activo</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Completa <strong>Marca</strong>, <strong>Tipo</strong> y <strong>Descripción</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Deja <strong>Activo</strong> marcado si el bien está operativo.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Marca <strong>Estación de Cybercafé</strong> solo si es una PC del módulo Cyber y pulsa <span class="manual-chip">Guardar</span>.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">edit</i> Editar o eliminar</div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span><strong>Editar:</strong> lápiz → ajusta → guarda.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span><strong>Eliminar:</strong> cesto rojo → confirma.</span></div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>No se puede eliminar un activo con sesiones cyber. Si ya no se usa, desmarca <strong>Activo</strong> y guarda.</span></div></div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Completa marca y descripción para identificar la estación en <strong>Cyber Control</strong>.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 11. ASESORÍA LEGAL ===== -->
<div class="manual-section card" id="asesorias">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">gavel</i></span>
            <div><span class="manual-kicker">Módulo 11</span><h3>Asesoría legal</h3></div>
        </div>
        <p class="manual-lead">Registra consultas jurídicas gratuitas y valida si el documento se atiende en la oficina o se deriva.</p>

        <div class="manual-subtitle"><i class="material-icons">fact_check</i> Validar el documento</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/23-asesoria-validacion.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/23-asesoria-validacion.svg" alt="Formulario de asesoría con validación de documento permitido y lista de documentos aceptados" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 23</span> Validación de un documento permitido. <a class="fig-open" href="Public/img/manual/23-asesoria-validacion.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Asesoría Legal</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Escribe o selecciona el <strong>Tipo de Documento / Asesoría</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>El botón cambia: <span class="manual-chip ok">Validar y Registrar</span> si está permitido, <span class="manual-chip bad">Derivar a Oficina Oficial</span> si no.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Revisa <strong>Documentos Permitidos</strong>: consulta laboral, civil, familiar, orientación general, revisión de contrato, documento simple y asesoría prevencional.</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Al registrar verás <strong>DOCUMENTO PERMITIDO</strong> (verde) o <strong>NO PERMITIDO</strong> (rojo) con la derivación.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">note_add</i> Registrar la asesoría</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/24-asesoria-registro.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/24-asesoria-registro.svg" alt="Formulario completo de asesoría legal e historial de casos registrados" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 24</span> Registro e historial de casos. <a class="fig-open" href="Public/img/manual/24-asesoria-registro.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Completa <strong>Nombre del Ciudadano</strong> y <strong>Cédula</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Agrega <strong>Teléfono</strong> y <strong>Dirección</strong> si están disponibles.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Escribe el <strong>Motivo de la Consulta</strong> con detalle.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Pulsa <span class="manual-chip">Validar y Registrar</span> (o <span class="manual-chip bad">Derivar a Oficina Oficial</span>). El caso aparece en el <strong>Historial</strong>.</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Busca en el historial por ciudadano, cédula o documento.</span></div>
            </div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Si la cédula ya existe, se reutiliza el cliente y se actualizan sus contactos; no se duplica.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>El historial es solo de consulta: no se edita ni elimina. Si hubo un error, documenta la corrección y consulta al administrador.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>Documentos no listados (juicios, demandas, apelaciones, herencias, materia penal) no se atienden directamente: se derivan a una oficina oficial.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 12. USUARIOS ===== -->
<div class="manual-section card" id="usuarios">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">manage_accounts</i></span>
            <div><span class="manual-kicker">Módulo 12</span><h3>Usuarios</h3></div>
        </div>
        <p class="manual-lead">Administra las cuentas. El rol de cada usuario define los módulos y acciones a los que accede.</p>

        <div class="manual-subtitle"><i class="material-icons">manage_search</i> Buscar y editar</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/25-usuarios-listado.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/25-usuarios-listado.svg" alt="Listado de usuarios con indicadores, buscador, filtro por rol y acciones de edición, estado y eliminación" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 25</span> Listado de Usuarios. <a class="fig-open" href="Public/img/manual/25-usuarios-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Busca por nombre, email o rol; filtra por <strong>Rol</strong>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Pulsa el lápiz. El nombre de usuario es solo lectura.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>La contraseña es opcional: déjala vacía para conservarla o escríbela para cambiarla. Guarda.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">person_add</i> Crear un usuario</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/26-usuarios-nuevo.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/26-usuarios-nuevo.svg" alt="Modal Nuevo Usuario con nombre, apellido, username, email, contraseña, rol y estado" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 26</span> Formulario <em>Nuevo Usuario</em>. <a class="fig-open" href="Public/img/manual/26-usuarios-nuevo.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Configuración</strong> y pulsa <span class="manual-chip">Nuevo Usuario</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Completa <strong>Nombre</strong>, <strong>Apellido</strong>, <strong>Usuario</strong> y <strong>Email</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Escribe una <strong>Contraseña</strong> de al menos 8 caracteres.</span></div>
                <div class="manual-step"><span class="step-num">4</span><span>Elige <strong>Rol</strong> y <strong>Estado</strong> (Activo o Inactivo).</span></div>
                <div class="manual-step"><span class="step-num">5</span><span>Pulsa <span class="manual-chip">Guardar</span>.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">toggle_on</i> Activar, desactivar o eliminar</div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span>Usa el botón de estado y confirma <em>¿Activar/Desactivar este usuario?</em>.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span>Un usuario inactivo conserva su historial, pero no puede iniciar sesión.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span>Para eliminar, usa el cesto rojo y confirma.</span></div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>No puedes desactivar ni eliminar tu propio usuario. Si tiene registros asociados, desactívalo en lugar de borrarlo.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>La contraseña se guarda cifrada: nadie puede verla. Si se olvida, cámbiala desde la edición del usuario.</span></div></div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Si un usuario no ve un módulo, revisa su rol en <strong>Roles y Permisos</strong> antes de crear otra cuenta.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 13. ROLES ===== -->
<div class="manual-section card" id="roles">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">admin_panel_settings</i></span>
            <div><span class="manual-kicker">Módulo 13</span><h3>Roles y permisos</h3></div>
        </div>
        <p class="manual-lead">Los roles agrupan permisos por módulo. Cada usuario hereda los accesos de su rol.</p>

        <div class="manual-subtitle"><i class="material-icons">add_moderator</i> Crear un rol</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/27-roles-listado.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/27-roles-listado.svg" alt="Listado de roles con indicadores, buscador, filtro y botones Nuevo Rol y Asignar Rol" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 27</span> Listado de Roles. <a class="fig-open" href="Public/img/manual/27-roles-listado.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Entra a <strong>Roles y Permisos</strong> y pulsa <span class="manual-chip">Nuevo Rol</span>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Escribe <strong>Nombre</strong> y <strong>Descripción</strong>.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Pulsa <span class="manual-chip">Guardar</span>.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">security</i> Configurar permisos</div>
        <div class="manual-split">
            <figure class="manual-figure">
                <a href="Public/img/manual/28-roles-permisos.svg" target="_blank" rel="noopener">
                    <img src="Public/img/manual/28-roles-permisos.svg" alt="Modal Permisos del rol con interruptores por módulo y botón Guardar Permisos" loading="lazy">
                </a>
                <figcaption><span class="fig-num">Figura 28</span> Ventana <em>Permisos: rol</em>. <a class="fig-open" href="Public/img/manual/28-roles-permisos.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
            </figure>
            <div class="manual-steps">
                <div class="manual-step"><span class="step-num">1</span><span>Pulsa el escudo del rol para abrir <em>Permisos: nombre del rol</em>.</span></div>
                <div class="manual-step"><span class="step-num">2</span><span>Activa o desactiva los módulos permitidos.</span></div>
                <div class="manual-step"><span class="step-num">3</span><span>Pulsa <span class="manual-chip">Guardar Permisos</span>. El menú se actualiza al iniciar sesión.</span></div>
            </div>
        </div>

        <div class="manual-subtitle"><i class="material-icons">supervisor_account</i> Asignar un rol</div>
        <div class="manual-steps">
            <div class="manual-step"><span class="step-num">1</span><span>Pulsa <span class="manual-chip">Asignar Rol</span>.</span></div>
            <div class="manual-step"><span class="step-num">2</span><span>Elige <strong>Usuario</strong> y <strong>Rol</strong>.</span></div>
            <div class="manual-step"><span class="step-num">3</span><span>Confirma con <span class="manual-chip">Asignar Rol</span>. Para editar nombre o descripción usa el lápiz; para eliminar, el cesto.</span></div>
        </div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>No se puede eliminar el rol <strong>Administrador</strong> ni uno con usuarios asignados. Reasigna primero esos usuarios.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>El rol Administrador está protegido y no se asigna desde <em>Asignar Rol</em>; se define al editar el usuario.</span></div></div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Revisa los permisos antes de crear un rol: un permiso de más expone módulos sensibles; uno de menos impide trabajar.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<!-- ===== 14. CONSEJOS Y SEGURIDAD ===== -->
<div class="manual-section card" id="consejos">
    <div class="card-content">
        <div class="manual-section-head">
            <span class="manual-section-icon"><i class="material-icons">security</i></span>
            <div><span class="manual-kicker">Módulo 14</span><h3>Consejos y seguridad</h3></div>
        </div>
        <p class="manual-lead">Buenas prácticas para reducir errores y proteger la información del negocio y de los clientes.</p>

        <figure class="manual-figure">
            <a href="Public/img/manual/29-consejos-seguridad.svg" target="_blank" rel="noopener">
                <img src="Public/img/manual/29-consejos-seguridad.svg" alt="Resumen de buenas prácticas de seguridad, respaldo y uso responsable del sistema" loading="lazy">
            </a>
            <figcaption><span class="fig-num">Figura 29</span> Buenas prácticas de seguridad y uso. <a class="fig-open" href="Public/img/manual/29-consejos-seguridad.svg" target="_blank" rel="noopener"><i class="material-icons">open_in_new</i> Ampliar</a></figcaption>
        </figure>

        <div class="manual-subtitle"><i class="material-icons">checklist</i> Buenas prácticas</div>
        <div class="manual-grid manual-grid-2">
            <div class="manual-box"><strong><i class="material-icons">logout</i> Cierra sesión</strong><span>Al terminar, sobre todo en equipos compartidos.</span></div>
            <div class="manual-box"><strong><i class="material-icons">password</i> Contraseñas seguras</strong><span>8+ caracteres con letras, números y símbolos.</span></div>
            <div class="manual-box"><strong><i class="material-icons">badge</i> Cuentas individuales</strong><span>Cada persona usa su propio usuario.</span></div>
            <div class="manual-box"><strong><i class="material-icons">dashboard</i> Revisa el Dashboard</strong><span>Stock crítico, solicitudes pendientes y sesiones activas.</span></div>
            <div class="manual-box"><strong><i class="material-icons">fact_check</i> Datos completos</strong><span>Cédula, nombre y contacto correctos.</span></div>
            <div class="manual-box"><strong><i class="material-icons">sync_disabled</i> No dupliques operaciones</strong><span>Espera la confirmación antes de recargar o repetir.</span></div>
            <div class="manual-box"><strong><i class="material-icons">dark_mode</i> Modo claro u oscuro</strong><span>Usa el icono de luna según la iluminación.</span></div>
            <div class="manual-box"><strong><i class="material-icons">bug_report</i> Reporta errores</strong><span>Anota módulo, acción y mensaje exacto.</span></div>
        </div>
        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>El sistema es una PWA: si la instalas, las páginas visitadas quedan en caché. Las operaciones que requieren servidor necesitan conexión.</span></div></div>
        <div class="manual-warn"><i class="material-icons">warning</i><div><strong class="callout-title">Cuidado</strong><span>No borres registros para “limpiar” el sistema. Ventas, compras, cyber y asesorías deben conservarse.</span></div></div>

        <div class="manual-subtitle"><i class="material-icons">map</i> Referencia rápida por módulo</div>
        <div class="manual-table">
            <table class="highlight">
                <thead>
                    <tr><th>Módulo</th><th>Acciones principales</th><th>Precaución clave</th></tr>
                </thead>
                <tbody>
                    <tr><td><strong>Inventario</strong></td><td>Crear, editar y eliminar productos; gestionar categorías; filtrar por estado.</td><td><span class="manual-chip warn">Precio ≥ costo</span></td></tr>
                    <tr><td><strong>Ventas (POS)</strong></td><td>Agregar productos, ajustar cantidades, identificar cliente, procesar y vaciar carrito.</td><td><span class="manual-chip bad">No recargues al cobrar</span></td></tr>
                    <tr><td><strong>Clientes</strong></td><td>Registrar, buscar, editar y eliminar clientes.</td><td><span class="manual-chip bad">No borres con historial</span></td></tr>
                    <tr><td><strong>Órdenes de compra</strong></td><td>Crear cabecera, agregar líneas, ver detalle, editar y eliminar.</td><td><span class="manual-chip warn">Guarda la cabecera</span></td></tr>
                    <tr><td><strong>Proveedores</strong></td><td>Registrar, buscar, editar y eliminar proveedores.</td><td><span class="manual-chip bad">No borres con solicitudes</span></td></tr>
                    <tr><td><strong>Cyber</strong></td><td>Iniciar y finalizar sesiones, crear/editar PCs, consultar historial y actualizar.</td><td><span class="manual-chip bad">PC con sesiones no se borra</span></td></tr>
                    <tr><td><strong>Reportes</strong></td><td>Elegir tipo, período y filtros; consultar y exportar en PDF, Excel o CSV.</td><td><span class="manual-chip warn">Reconsulta al cambiar tipo</span></td></tr>
                    <tr><td><strong>Activos</strong></td><td>Registrar, buscar, editar y eliminar activos; marcar estaciones cyber.</td><td><span class="manual-chip bad">No borres con sesiones</span></td></tr>
                    <tr><td><strong>Asesoría Legal</strong></td><td>Validar documento, registrar caso y consultar historial.</td><td><span class="manual-chip warn">Deriva lo no permitido</span></td></tr>
                    <tr><td><strong>Usuarios</strong></td><td>Crear, editar, activar/desactivar y eliminar usuarios.</td><td><span class="manual-chip bad">No te borres a ti mismo</span></td></tr>
                    <tr><td><strong>Roles</strong></td><td>Crear roles, configurar permisos y asignar rol a usuarios.</td><td><span class="manual-chip warn">Reasigna antes de borrar</span></td></tr>
                </tbody>
            </table>
        </div>

        <div class="manual-tip"><i class="material-icons">lightbulb</i><div><strong>Tip</strong><span>Las ilustraciones son representaciones de la interfaz y pueden variar según los datos, el rol y el tema claro u oscuro.</span></div></div>
        <div class="manual-tip"><i class="material-icons">menu_book</i><div><strong>Tip</strong><span>Para dudas de instalación, base de datos o servidor, consulta la documentación técnica en <em>docs/</em> o contacta al administrador.</span></div></div>
        <a class="manual-back" href="#indice"><i class="material-icons">arrow_upward</i> Volver al contenido</a>
    </div>
</div>

<script>
(function () {
    /* ===== Buscador del índice ===== */
    var input = document.getElementById('manual-toc-search');
    if (input) {
        var items = Array.prototype.slice.call(document.querySelectorAll('.manual-toc-item'));
        var empty = document.getElementById('manual-toc-empty');
        input.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var visible = 0;
            items.forEach(function (item) {
                var match = !q || item.textContent.toLowerCase().indexOf(q) !== -1;
                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (empty) empty.hidden = visible !== 0;
        });
    }

    /* ===== Exportar a PDF =====
       Abre el diálogo de impresión con la hoja @media print del manual.
       Así el PDF conserva el mismo diseño, contenido y distribución que
       la pantalla, sin el menú lateral ni la barra del sistema. */
    var btn = document.getElementById('manual-export-pdf');
    if (!btn) return;

    var root = document.documentElement;
    var prevTheme = null;

    function forceLightTheme() {
        if (prevTheme === null) {
            prevTheme = root.getAttribute('data-theme') || 'light';
        }
        root.setAttribute('data-theme', 'light');
    }

    function restoreTheme() {
        if (prevTheme !== null) {
            root.setAttribute('data-theme', prevTheme);
            prevTheme = null;
        }
    }

    /* Cubre también la impresión con Ctrl+P desde el modo oscuro. */
    window.addEventListener('beforeprint', forceLightTheme);
    window.addEventListener('afterprint', restoreTheme);

    function prepararImagenes() {
        var imagenes = Array.prototype.slice.call(document.querySelectorAll('.manual-figure img'));
        var pendientes = [];
        imagenes.forEach(function (img) {
            img.loading = 'eager';
            if (!img.complete) {
                pendientes.push(new Promise(function (resolve) {
                    img.addEventListener('load', resolve, { once: true });
                    img.addEventListener('error', resolve, { once: true });
                }));
            }
        });
        return Promise.all(pendientes);
    }

    btn.addEventListener('click', function () {
        forceLightTheme();
        var limite = new Promise(function (resolve) { setTimeout(resolve, 2500); });
        Promise.race([prepararImagenes(), limite]).then(function () {
            window.print();
            /* Si el navegador no dispara afterprint, el tema se restaura igual. */
            setTimeout(restoreTheme, 800);
        });
    });
})();
</script>
