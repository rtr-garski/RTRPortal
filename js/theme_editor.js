// Apply saved theme globally on every page load
function applyRTRTheme(theme) {
    theme = theme || JSON.parse(localStorage.getItem('rtr_theme') || 'null');
    if (!theme) return;

    let el = document.getElementById('rtr-theme-style');
    if (!el) {
        el = document.createElement('style');
        el.id = 'rtr-theme-style';
        document.head.appendChild(el);
    }

    el.textContent = [
        `.content-page { background-color: ${theme.pageBg} !important; }`,
        `.card, .card-header, .card-footer { background-color: ${theme.cardBg} !important; }`,
        `.card { color: ${theme.fontColor}; }`,
        `.card td, .card p, .card label, .card h4, .card h5, .card h6 { color: ${theme.fontColor} !important; }`,
        `table thead th { background-color: ${theme.thBg} !important; color: ${theme.thText} !important; }`,
        `.topbar { background-color: ${theme.topbarBg} !important; }`,
        `.sidenav-menu { background-color: ${theme.sidebarBg} !important; }`,
    ].join('\n');
}

applyRTRTheme();

function init_theme_editor() {
    const contentEl = document.getElementById('content');

    const PRESETS = [
        {
            name: 'Default Dark',
            icon: 'ti-moon',
            pageBg: '#313a46', fontColor: '#aab8c5',
            cardBg: '#404954', topbarBg: '#3c4655',
            thBg: '#404954',   thText: '#8391a2',
            sidebarBg: '#313a46',
        },
        {
            name: 'Clean Light',
            icon: 'ti-sun',
            pageBg: '#f0f2f5', fontColor: '#313a46',
            cardBg: '#ffffff', topbarBg: '#ffffff',
            thBg: '#e2e7f1',   thText: '#313a46',
            sidebarBg: '#ffffff',
        },
        {
            name: 'Ocean Blue',
            icon: 'ti-waves',
            pageBg: '#0d2137', fontColor: '#cfe2ff',
            cardBg: '#0f2d4a', topbarBg: '#092032',
            thBg: '#0a3d62',   thText: '#90caf9',
            sidebarBg: '#0d2137',
        },
        {
            name: 'Forest',
            icon: 'ti-trees',
            pageBg: '#1b2e1b', fontColor: '#c8e6c9',
            cardBg: '#243b24', topbarBg: '#162616',
            thBg: '#2e4f2e',   thText: '#a5d6a7',
            sidebarBg: '#1b2e1b',
        },
        {
            name: 'Deep Purple',
            icon: 'ti-crystal-ball',
            pageBg: '#1a1035', fontColor: '#e0d7f5',
            cardBg: '#231848', topbarBg: '#150d2b',
            thBg: '#2d1f5e',   thText: '#ce93d8',
            sidebarBg: '#1a1035',
        },
        {
            name: 'Sunset',
            icon: 'ti-sunset',
            pageBg: '#1c1c2e', fontColor: '#ffd8a8',
            cardBg: '#2a1f3d', topbarBg: '#14132a',
            thBg: '#4a2040',   thText: '#ffab76',
            sidebarBg: '#1c1c2e',
        },
    ];

    const FIELDS = [
        { key: 'pageBg',    colorId: 'colorPageBg',    textId: 'textPageBg'    },
        { key: 'fontColor', colorId: 'colorFontColor',  textId: 'textFontColor' },
        { key: 'cardBg',    colorId: 'colorCardBg',    textId: 'textCardBg'    },
        { key: 'topbarBg',  colorId: 'colorTopbarBg',  textId: 'textTopbarBg'  },
        { key: 'thBg',      colorId: 'colorThBg',      textId: 'textThBg'      },
        { key: 'thText',    colorId: 'colorThText',     textId: 'textThText'    },
        { key: 'sidebarBg', colorId: 'colorSidebarBg', textId: 'textSidebarBg' },
    ];

    function getCurrentValues() {
        const t = {};
        FIELDS.forEach(f => { t[f.key] = document.getElementById(f.colorId)?.value || '#000000'; });
        return t;
    }

    function populatePickers(theme) {
        FIELDS.forEach(f => {
            const colorEl = document.getElementById(f.colorId);
            const textEl  = document.getElementById(f.textId);
            if (colorEl) colorEl.value = theme[f.key] || '#000000';
            if (textEl)  textEl.value  = theme[f.key] || '';
        });
    }

    function buildPresets() {
        const container = document.getElementById('themePresets');
        if (!container) return;
        container.innerHTML = '';

        const saved = JSON.parse(localStorage.getItem('rtr_theme') || 'null');

        PRESETS.forEach(preset => {
            const isActive = saved && saved.name === preset.name;
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm d-flex flex-column align-items-center gap-1 px-3 py-2 border' + (isActive ? ' border-primary' : ' border-light');
            btn.style.cssText = `background:${preset.cardBg}; color:${preset.fontColor}; min-width:90px;`;
            btn.innerHTML = `
                <span style="display:flex;gap:3px;">
                    <span style="width:12px;height:12px;border-radius:50%;background:${preset.pageBg};border:1px solid rgba(255,255,255,.2)"></span>
                    <span style="width:12px;height:12px;border-radius:50%;background:${preset.thBg};border:1px solid rgba(255,255,255,.2)"></span>
                    <span style="width:12px;height:12px;border-radius:50%;background:${preset.topbarBg};border:1px solid rgba(255,255,255,.2)"></span>
                </span>
                <i class="ti ${preset.icon} fs-lg"></i>
                <span class="fs-11 fw-semibold">${preset.name}</span>
                ${isActive ? '<span class="badge bg-primary fs-10">Active</span>' : ''}
            `;
            btn.addEventListener('click', () => {
                populatePickers(preset);
                applyRTRTheme(preset);
            });
            container.appendChild(btn);
        });
    }

    // Wire up pickers: color → text sync and live preview
    FIELDS.forEach(f => {
        const colorEl = document.getElementById(f.colorId);
        const textEl  = document.getElementById(f.textId);
        if (!colorEl || !textEl) return;

        colorEl.addEventListener('input', () => {
            textEl.value = colorEl.value;
            applyRTRTheme(getCurrentValues());
        });

        textEl.addEventListener('input', () => {
            if (/^#[0-9a-fA-F]{6}$/.test(textEl.value)) {
                colorEl.value = textEl.value;
                applyRTRTheme(getCurrentValues());
            }
        });
    });

    // Save
    if (contentEl._themeApplyBtn) contentEl.removeEventListener('click', contentEl._themeApplyBtn);
    contentEl._themeApplyBtn = function(e) {
        if (!e.target.closest('#applyThemeBtn')) return;
        const theme = { ...getCurrentValues(), name: 'custom' };
        localStorage.setItem('rtr_theme', JSON.stringify(theme));
        applyRTRTheme(theme);
        buildPresets();
        const flash = document.getElementById('themeSaveFlash');
        if (flash) {
            flash.innerHTML = '<div class="alert alert-success py-2">Theme saved successfully.</div>';
            setTimeout(() => { flash.innerHTML = ''; }, 3000);
        }
    };
    contentEl.addEventListener('click', contentEl._themeApplyBtn);

    // Reset
    if (contentEl._themeResetBtn) contentEl.removeEventListener('click', contentEl._themeResetBtn);
    contentEl._themeResetBtn = function(e) {
        if (!e.target.closest('#resetThemeBtn')) return;
        localStorage.removeItem('rtr_theme');
        const el = document.getElementById('rtr-theme-style');
        if (el) el.remove();
        populatePickers(PRESETS[0]);
        buildPresets();
        const flash = document.getElementById('themeSaveFlash');
        if (flash) {
            flash.innerHTML = '<div class="alert alert-info py-2">Theme reset to default.</div>';
            setTimeout(() => { flash.innerHTML = ''; }, 3000);
        }
    };
    contentEl.addEventListener('click', contentEl._themeResetBtn);

    // Init: load saved theme or default
    const saved = JSON.parse(localStorage.getItem('rtr_theme') || 'null');
    populatePickers(saved || PRESETS[0]);
    buildPresets();
}
