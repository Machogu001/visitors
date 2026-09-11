<style>
    /*
       Admin panel border stability.

       Filament renders many visible borders as Tailwind rings using box-shadow
       variables such as --tw-ring-shadow and --tw-ring-color. In Firefox, these
       1px box-shadow/ring based borders can render inconsistently during zoom,
       especially together with border-radius.

       For the affected admin panel surfaces and controls we use real CSS borders
       in all themes. This keeps visible edges stable while keeping the override
       limited to the Filament admin panel theme partial.
    */

    /*
       Light theme border fallback.
    */
    .fi-section:not(.fi-section-not-contained):not(.fi-aside),
    .fi-section:not(.fi-section-not-contained).fi-aside > .fi-section-content-ctn,
    .fi-dropdown-panel,
    .fi-modal-window {
        border: 2px solid rgb(209 213 219 / 1) !important;
        box-sizing: border-box;
        --tw-ring-shadow: 0 0 #0000 !important;
    }

    .fi-input-wrp {
        border: 2px solid rgb(209 213 219 / 1) !important;
        box-sizing: border-box;
        overflow: visible;
        --tw-ring-shadow: 0 0 #0000 !important;
    }

    .fi-input-wrp:not(.fi-disabled):not(:has(.fi-ac-action:focus)):focus-within {
        border-color: rgb(245 158 11 / 1) !important;
        --tw-ring-shadow: 0 0 #0000 !important;
    }

    .fi-input-wrp.fi-invalid {
        border-color: rgb(239 68 68 / 1) !important;
    }

    .fi-input-wrp .fi-input,
    .fi-input-wrp .fi-select-input,
    .fi-input-wrp .fi-textarea {
        background-color: transparent !important;
    }

    /*
       Dark theme border fallback.
    */
    html.dark .fi-section:not(.fi-section-not-contained):not(.fi-aside),
    html[data-theme="dark"] .fi-section:not(.fi-section-not-contained):not(.fi-aside),
    html.dark .fi-section:not(.fi-section-not-contained).fi-aside > .fi-section-content-ctn,
    html[data-theme="dark"] .fi-section:not(.fi-section-not-contained).fi-aside > .fi-section-content-ctn,
    html.dark .fi-dropdown-panel,
    html[data-theme="dark"] .fi-dropdown-panel,
    html.dark .fi-modal-window,
    html[data-theme="dark"] .fi-modal-window {
        border-color: rgb(64 64 64 / 1) !important;
    }

    html.dark .fi-input-wrp,
    html[data-theme="dark"] .fi-input-wrp {
        border-color: rgb(82 82 91 / 1) !important;
    }

    html.dark .fi-input-wrp:not(.fi-disabled):not(:has(.fi-ac-action:focus)):focus-within,
    html[data-theme="dark"] .fi-input-wrp:not(.fi-disabled):not(:has(.fi-ac-action:focus)):focus-within {
        border-color: rgb(245 158 11 / 1) !important;
    }

    /*
       True Black theme colors.
    */
    html[data-theme="true-black"] {
        color-scheme: dark;
    }

    html[data-theme="true-black"] body,
    html[data-theme="true-black"] .fi-layout,
    html[data-theme="true-black"] .fi-main,
    html[data-theme="true-black"] .fi-sidebar,
    html[data-theme="true-black"] .fi-topbar,
    html[data-theme="true-black"] .fi-header {
        background-color: #000 !important;
    }

    html[data-theme="true-black"] .fi-section:not(.fi-section-not-contained):not(.fi-aside),
    html[data-theme="true-black"] .fi-section:not(.fi-section-not-contained).fi-aside > .fi-section-content-ctn,
    html[data-theme="true-black"] .fi-dropdown-panel,
    html[data-theme="true-black"] .fi-modal-window {
        background-color: #050505 !important;
        border-color: rgb(64 64 64 / 1) !important;
    }

    html[data-theme="true-black"] .fi-ta-record {
        background-color: #050505 !important;
        border-color: rgb(64 64 64 / 1) !important;
    }

    html[data-theme="true-black"] .fi-input-wrp {
        background-color: #050505 !important;
        border-color: rgb(82 82 91 / 1) !important;
    }

    html[data-theme="true-black"] .fi-input-wrp:not(.fi-disabled):not(:has(.fi-ac-action:focus)):focus-within {
        border-color: rgb(245 158 11 / 1) !important;
    }

    html[data-theme="true-black"] .fi-input-wrp .fi-input,
    html[data-theme="true-black"] .fi-input-wrp .fi-select-input,
    html[data-theme="true-black"] .fi-input-wrp .fi-textarea {
        background-color: transparent !important;
    }

    html[data-theme="true-black"] .fi-input-wrp .fi-input-wrp-prefix:not(.fi-inline),
    html[data-theme="true-black"] .fi-input-wrp .fi-input-wrp-suffix:not(.fi-inline) {
        border-color: rgb(38 38 38 / 1) !important;
    }

    /*
       GitHub repository button:
       Use a real border for this project-specific button in all themes.
       This avoids Firefox zoom artifacts caused by Filament/Tailwind ring borders.
    */
    .bp-github-link-button {
        border: 2px solid rgb(209 213 219 / 1) !important;
        box-sizing: border-box;
        background-clip: padding-box;
        --tw-ring-shadow: 0 0 #0000 !important;
    }

    .bp-github-link-button:hover {
        border-color: rgb(156 163 175 / 1) !important;
    }

    .bp-github-link-button:focus-visible {
        border-color: rgb(245 158 11 / 1) !important;
        outline: none !important;
        --tw-ring-shadow: 0 0 #0000 !important;
    }

    html.dark .bp-github-link-button,
    html[data-theme="dark"] .bp-github-link-button {
        border-color: rgb(82 82 91 / 1) !important;
    }

    html.dark .bp-github-link-button:hover,
    html[data-theme="dark"] .bp-github-link-button:hover {
        border-color: rgb(113 113 122 / 1) !important;
    }

    html[data-theme="true-black"] .bp-github-link-button {
        border-color: rgb(82 82 91 / 1) !important;
    }

    html[data-theme="true-black"] .bp-github-link-button:hover {
        border-color: rgb(113 113 122 / 1) !important;
    }

    /*
       True Black theme switcher active state.
    */
    html[data-theme="true-black"] .fi-theme-switcher-btn.fi-active {
        background-color: rgb(139 147 255 / 0.18) !important;
        color: rgb(199 210 254 / 1) !important;
    }

    /*
       Filament admin sidebar navigation states.

       - Active/current item keeps one stable background.
       - Hover only affects non-active items.
       - Text and icon colors stay controlled by Filament.
    */

    .fi-sidebar-nav .fi-sidebar-item.fi-active > :is(a, button),
    .fi-sidebar-nav .fi-sidebar-item.fi-sidebar-item-active > :is(a, button),
    .fi-sidebar-nav .fi-sidebar-item > :is(a, button).fi-active,
    .fi-sidebar-nav .fi-sidebar-item > :is(a, button)[aria-current="page"],
    .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button.fi-active,
    .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button[aria-current="page"] {
        background-color: rgb(243 244 246 / 1) !important;
    }

    .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > :is(a, button):not(.fi-active):not([aria-current="page"]):hover,
    .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:not(.fi-active):not([aria-current="page"]):hover {
        background-color: rgb(229 231 235 / 1) !important;
    }

    html.dark .fi-sidebar-nav .fi-sidebar-item.fi-active > :is(a, button),
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item.fi-active > :is(a, button),
    html.dark .fi-sidebar-nav .fi-sidebar-item.fi-sidebar-item-active > :is(a, button),
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item.fi-sidebar-item-active > :is(a, button),
    html.dark .fi-sidebar-nav .fi-sidebar-item > :is(a, button).fi-active,
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item > :is(a, button).fi-active,
    html.dark .fi-sidebar-nav .fi-sidebar-item > :is(a, button)[aria-current="page"],
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item > :is(a, button)[aria-current="page"],
    html.dark .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button.fi-active,
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button.fi-active,
    html.dark .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button[aria-current="page"],
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button[aria-current="page"] {
        background-color: rgb(45 45 50 / 1) !important;
    }

    html.dark .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > :is(a, button):not(.fi-active):not([aria-current="page"]):hover,
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > :is(a, button):not(.fi-active):not([aria-current="page"]):hover,
    html.dark .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:not(.fi-active):not([aria-current="page"]):hover,
    html[data-theme="dark"] .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:not(.fi-active):not([aria-current="page"]):hover {
        background-color: rgb(34 34 38 / 1) !important;
    }

    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item.fi-active > :is(a, button),
    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item.fi-sidebar-item-active > :is(a, button),
    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item > :is(a, button).fi-active,
    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item > :is(a, button)[aria-current="page"],
    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button.fi-active,
    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item > .fi-sidebar-item-button[aria-current="page"] {
        background-color: rgb(38 38 43 / 1) !important;
    }

    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > :is(a, button):not(.fi-active):not([aria-current="page"]):hover,
    html[data-theme="true-black"] .fi-sidebar-nav .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:not(.fi-active):not([aria-current="page"]):hover {
        background-color: rgb(24 24 28 / 1) !important;
    }

    /*
       Filament admin primary action buttons.

       Use the same amber tone as Filament's warning/edit actions in all themes,
       while forcing near-black text for contrast. This also fixes light mode,
       where Filament may otherwise render brown/orange text on amber.
    */
    .fi-btn.fi-color-primary {
        background-color: rgb(251 191 36 / 1) !important;
        border-color: rgb(251 191 36 / 1) !important;
        color: rgb(17 24 39 / 1) !important;
    }

    .fi-btn.fi-color-primary:hover,
    .fi-btn.fi-color-primary:focus-visible {
        background-color: rgb(245 158 11 / 1) !important;
        border-color: rgb(245 158 11 / 1) !important;
        color: rgb(17 24 39 / 1) !important;
    }

    .fi-btn.fi-color-primary :is(.fi-btn-label, .fi-btn-icon, svg),
    .fi-btn.fi-color-primary:hover :is(.fi-btn-label, .fi-btn-icon, svg),
    .fi-btn.fi-color-primary:focus-visible :is(.fi-btn-label, .fi-btn-icon, svg) {
        color: rgb(17 24 39 / 1) !important;
    }

    /*
       Filament table toolbar layout.

       Filament places the first toolbar group on the left and
       the search/tools group on the right. For this admin panel, align all
       toolbar groups to the right so create buttons sit directly before the
       search field.
    */
    .fi-ta-header-toolbar,
    .fi-ta-toolbar {
        justify-content: flex-end !important;
        align-items: center !important;
        gap: 1rem !important;
    }

    .fi-ta-header-toolbar > *,
    .fi-ta-toolbar > * {
        margin-inline-start: 0 !important;
    }

    .fi-ta-header-toolbar .fi-actions,
    .fi-ta-toolbar .fi-actions {
        flex-shrink: 0;
    }

    /*
       Operations and reporting pages.
       These views live entirely inside Filament and therefore use this panel
       stylesheet instead of the public application Tailwind bundle.
    */
    .bp-admin-page {
        display: grid;
        gap: 1.5rem;
    }

    .bp-report-filter {
        display: grid;
        grid-template-columns: minmax(10rem, 1fr) minmax(10rem, 1fr) auto;
        align-items: end;
        gap: 1rem;
    }

    .bp-field {
        display: grid;
        gap: 0.5rem;
    }

    .bp-field-label,
    .bp-stat-label {
        color: rgb(75 85 99 / 1);
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25rem;
    }

    .bp-report-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .bp-section-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .bp-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .bp-stat-card-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .bp-stat-icon {
        display: grid;
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 2.75rem;
        place-items: center;
        border-radius: 0.5rem;
        background: rgb(249 250 251 / 1);
        color: rgb(75 85 99 / 1);
    }

    .bp-stat-icon svg {
        width: 1.5rem;
        height: 1.5rem;
    }

    .bp-stat-icon.fi-color-primary { background: rgb(255 251 235 / 1); color: rgb(180 83 9 / 1); }
    .bp-stat-icon.fi-color-info { background: rgb(239 246 255 / 1); color: rgb(37 99 235 / 1); }
    .bp-stat-icon.fi-color-success { background: rgb(240 253 244 / 1); color: rgb(22 163 74 / 1); }
    .bp-stat-icon.fi-color-danger { background: rgb(254 242 242 / 1); color: rgb(220 38 38 / 1); }

    .bp-stat-value {
        margin-top: 0.125rem;
        color: rgb(17 24 39 / 1);
        font-size: 1.875rem;
        font-weight: 700;
        line-height: 2.25rem;
    }

    .bp-report-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
        gap: 1.5rem;
        align-items: start;
    }

    .bp-table-scroll {
        max-width: 100%;
        overflow-x: auto;
    }

    .bp-table-scroll:focus-visible {
        outline: 2px solid rgb(245 158 11 / 1);
        outline-offset: 2px;
    }

    .bp-data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        line-height: 1.25rem;
        text-align: left;
    }

    .bp-site-table {
        min-width: 34rem;
    }

    .bp-roll-call-table {
        min-width: 50rem;
    }

    .bp-data-table th {
        padding: 0 1rem 0.75rem;
        color: rgb(75 85 99 / 1);
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .bp-data-table th:first-child,
    .bp-data-table td:first-child {
        padding-left: 0;
    }

    .bp-data-table th:last-child,
    .bp-data-table td:last-child {
        padding-right: 0;
    }

    .bp-data-table td {
        padding: 0.875rem 1rem;
        border-top: 1px solid rgb(229 231 235 / 1);
        color: rgb(55 65 81 / 1);
        vertical-align: middle;
    }

    .bp-table-primary,
    .bp-table-number {
        color: rgb(17 24 39 / 1) !important;
        font-weight: 600;
    }

    html.dark .bp-field-label,
    html[data-theme="dark"] .bp-field-label,
    html.dark .bp-stat-label,
    html[data-theme="dark"] .bp-stat-label,
    html[data-theme="true-black"] .bp-field-label,
    html[data-theme="true-black"] .bp-stat-label,
    html.dark .bp-data-table th,
    html[data-theme="dark"] .bp-data-table th,
    html[data-theme="true-black"] .bp-data-table th {
        color: rgb(161 161 170 / 1);
    }

    html.dark .bp-stat-value,
    html[data-theme="dark"] .bp-stat-value,
    html[data-theme="true-black"] .bp-stat-value,
    html.dark .bp-table-primary,
    html[data-theme="dark"] .bp-table-primary,
    html[data-theme="true-black"] .bp-table-primary,
    html.dark .bp-table-number,
    html[data-theme="dark"] .bp-table-number,
    html[data-theme="true-black"] .bp-table-number {
        color: rgb(250 250 250 / 1) !important;
    }

    html.dark .bp-stat-icon,
    html[data-theme="dark"] .bp-stat-icon,
    html[data-theme="true-black"] .bp-stat-icon {
        background: rgb(39 39 42 / 1);
    }

    html.dark .bp-stat-icon.fi-color-primary,
    html[data-theme="dark"] .bp-stat-icon.fi-color-primary,
    html[data-theme="true-black"] .bp-stat-icon.fi-color-primary { color: rgb(251 191 36 / 1); }
    html.dark .bp-stat-icon.fi-color-info,
    html[data-theme="dark"] .bp-stat-icon.fi-color-info,
    html[data-theme="true-black"] .bp-stat-icon.fi-color-info { color: rgb(96 165 250 / 1); }
    html.dark .bp-stat-icon.fi-color-success,
    html[data-theme="dark"] .bp-stat-icon.fi-color-success,
    html[data-theme="true-black"] .bp-stat-icon.fi-color-success { color: rgb(74 222 128 / 1); }
    html.dark .bp-stat-icon.fi-color-danger,
    html[data-theme="dark"] .bp-stat-icon.fi-color-danger,
    html[data-theme="true-black"] .bp-stat-icon.fi-color-danger { color: rgb(248 113 113 / 1); }

    html.dark .bp-data-table td,
    html[data-theme="dark"] .bp-data-table td,
    html[data-theme="true-black"] .bp-data-table td {
        border-color: rgb(63 63 70 / 1);
        color: rgb(212 212 216 / 1);
    }

    @media (max-width: 1023px) {
        .bp-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .bp-report-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 639px) {
        .bp-report-filter,
        .bp-stat-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .bp-report-actions,
        .bp-report-actions .fi-btn {
            width: 100%;
        }

        .bp-section-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .bp-section-actions .fi-btn {
            flex: 1 1 auto;
        }
    }

    @media print {
        .fi-sidebar,
        .fi-topbar,
        .fi-header,
        .bp-stat-grid,
        .bp-section-actions {
            display: none !important;
        }

        .fi-main,
        .fi-page,
        .bp-admin-page {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .bp-roll-call-table {
            min-width: 0;
        }
    }

    /*
       Logo switching for Filament Admin.
    */
    .fi-logo.logo-dark {
        display: none;
    }

    .fi-logo.logo-light {
        display: inline-block;
    }

    html[data-theme="dark"] .fi-logo.logo-light,
    html[data-theme="true-black"] .fi-logo.logo-light {
        display: none;
    }

    html[data-theme="dark"] .fi-logo.logo-dark,
    html[data-theme="true-black"] .fi-logo.logo-dark {
        display: inline-block;
    }

    @media (prefers-color-scheme: dark) {
        html[data-theme="system"] .fi-logo.logo-light {
            display: none;
        }

        html[data-theme="system"] .fi-logo.logo-dark {
            display: inline-block;
        }
    }
</style>
