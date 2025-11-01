<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>VisitLapas</title>
    <base href="{{ asset('') }}">
    <link rel="shortcut icon" href="assets/media/logos/favicon.ico" />
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!-- Vendor Stylesheets (as needed) -->
    <link href="assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
    <!-- Global Stylesheets Bundle -->
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <style>
        html, body { height: 100%; }
        .display-header { position: sticky; top: 0; z-index: 5; }
        .brand-title { font-size: clamp(32px, 4vw, 72px); font-weight: 800; letter-spacing: .01em; }
        .display-date { font-size: clamp(16px, 2vw, 28px); opacity: .9; font-weight: 600; }
        .display-clock { font-size: clamp(48px, 6vw, 112px); line-height: 1; font-weight: 800; }
        .ticket-number { font-size: clamp(40px, 8vw, 96px); font-weight: 800; letter-spacing: .02em; }
        .counter-code { font-size: clamp(20px, 3vw, 40px); font-weight: 700; }
        .since-time { font-size: 14px; opacity: .85; }
        .next-up .item { font-size: clamp(24px, 3vw, 40px); font-weight: 700; padding: .5rem 0; border-bottom: 1px dashed rgba(0,0,0,.09); }
        [data-bs-theme="dark"] .next-up .item { border-color: rgba(255,255,255,.15); }
        .conn-indicator { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-left: 8px; }
        .conn-ok { background: #16a34a; }
        .conn-bad { background: #dc2626; }
        /* Ticker footer */
        .ticker { position: fixed; left: 0; right: 0; bottom: 0; padding: .5rem 0; z-index: 1040; width: 100%; }
        [data-bs-theme="light"] .ticker { background: rgba(255,255,255,.95); color: #111; border-top: 1px solid rgba(0,0,0,.08); }
        [data-bs-theme="dark"] .ticker { background: rgba(2,6,23,.95); color: #f8fafc; border-top: 1px solid rgba(255,255,255,.12); }
        .ticker .content { white-space: nowrap; overflow: hidden; }
        .ticker .text { display: inline-block; padding-left: 100%; animation: ticker 25s linear infinite; font-size: clamp(18px, 2.2vw, 28px); line-height: 1.6; font-weight: 700; }
        @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-100%); } }
    </style>
    <script>
        // Theme mode setup (as per Metronic)
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
</head>

<body>
    <div class="container-fluid py-4" id="displayPage">
        <div class="display-header mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/media/logos/logo_with_text.jpg') }}" alt="VisitLapas" style="height:56px;" />
                    <div class="text-muted">|</div>
                    <div class="display-date" id="displayDate"></div>
                    <span id="connDot" class="conn-indicator conn-ok" title="Connected"></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    @auth
                    <button type="button" class="btn btn-sm btn-secondary" id="btnOpenSettings" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="bi bi-gear-fill me-1"></i> Settings
                    </button>
                    @endauth
                    <div class="display-clock" id="displayClock">--:--</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm now-calling">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Now Calling</h3>
                    </div>
                    <div class="card-body">
                        <div id="nowCalling" class="row g-4">
                            <!-- Cards injected here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm next-up h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Next Up</h3>
                    </div>
                    <div class="card-body">
                        <div id="nextUpList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @auth
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="settingsForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Display Settings</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Theme</label>
                            <select id="s_theme" class="form-select">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                            </select>
                            <div class="invalid-feedback" data-field="theme"></div>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="s_voice">
                            <label class="form-check-label" for="s_voice">Enable Voice</label>
                            <div class="invalid-feedback d-block" data-field="voice_enabled"></div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Ticker Text</label>
                            <textarea id="s_ticker" class="form-control" rows="3" maxlength="255"></textarea>
                            <div class="invalid-feedback" data-field="ticker_text"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endauth

    <div class="ticker">
        <div class="content">
            <div id="tickerText" class="text fw-semibold"></div>
        </div>
    </div>

    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>
    <script>
    (function(){
        const initialTheme = @json($theme ?? 'light');
        const initialVoice = @json($voice_enabled ?? false);
        const initialTicker = @json($ticker_text ?? '');
        const CSRF = '{{ csrf_token() }}';

        function applyTheme(theme){
            // Apply our own body classes for custom styles
            document.body.classList.remove('display-light','display-dark');
            document.body.classList.add(theme === 'dark' ? 'display-dark' : 'display-light');
            // Also sync Metronic theme attribute so components/variables follow
            document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
        }
        applyTheme(initialTheme);

        const dateEl = document.getElementById('displayDate');
        const clockEl = document.getElementById('displayClock');
        function updateClock(){
            const now = new Date();
            const hh = String(now.getHours()).padStart(2,'0');
            const mm = String(now.getMinutes()).padStart(2,'0');
            clockEl.textContent = `${hh}:${mm}`;
            const locale = now.toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
            dateEl.textContent = locale;
        }
        updateClock(); setInterval(updateClock, 1000);

        const tickerEl = document.getElementById('tickerText');
        function setTicker(text){ tickerEl.textContent = text || ''; }
        setTicker(initialTicker);

        const nowCallingWrap = document.getElementById('nowCalling');
        const nextUpWrap = document.getElementById('nextUpList');
        function renderNowCalling(items){
            nowCallingWrap.innerHTML = '';
            if (!items || !items.length){ nowCallingWrap.innerHTML = '<div class="col-12 text-muted">Tidak ada panggilan</div>'; return; }
            const frag = document.createDocumentFragment();
            items.forEach(it => {
                const col = document.createElement('div'); col.className = 'col-12 col-sm-6 col-xl-4';
                const card = document.createElement('div'); card.className = 'card border ' + (it.status === 'serving' ? 'border-success' : 'border-info') + ' appear';
                const body = document.createElement('div'); body.className = 'card-body';
                body.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <div class="counter-code">${escapeHtml(it.counter_code || '-')}</div>
                      <span class="badge ${it.status==='serving' ? 'bg-success' : 'bg-info'}">${escapeHtml(capitalize(it.status))}</span>
                    </div>
                    <div class="ticket-number">${escapeHtml(it.ticket_number || '-')}</div>
                    <div class="since-time mt-2">Sejak ${escapeHtml(it.since || '-')}</div>
                `;
                card.appendChild(body); col.appendChild(card); frag.appendChild(col);
            });
            nowCallingWrap.appendChild(frag);
        }
        function renderNextUp(items){
            nextUpWrap.innerHTML = '';
            if (!items || !items.length){ nextUpWrap.innerHTML = '<div class="text-muted">-</div>'; return; }
            const frag = document.createDocumentFragment();
            items.forEach(it => { const div = document.createElement('div'); div.className = 'item'; div.textContent = it.ticket_number; frag.appendChild(div); });
            nextUpWrap.appendChild(frag);
        }
        function escapeHtml(s){ return (s||'').toString().replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }
        function capitalize(s){ s = (s||'').toString(); return s.charAt(0).toUpperCase() + s.slice(1); }

        const connDot = document.getElementById('connDot');
        let lastAnnouncedKeys = new Set();
        let voiceEnabled = !!initialVoice;
        function setConn(ok){ connDot.classList.toggle('conn-ok', !!ok); connDot.classList.toggle('conn-bad', !ok); }

        async function fetchData(){
            try {
                const today = new Date().toISOString().slice(0,10);
                const res = await fetch(`{{ route('display.data') }}?date=${today}`, { cache:'no-store' });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Request failed');
                applyTheme(json.settings?.theme || 'light');
                voiceEnabled = !!json.settings?.voice_enabled;
                setTicker(json.settings?.ticker_text || '');
                renderNowCalling(json.current || []);
                renderNextUp(json.next || []);
                if ('speechSynthesis' in window && voiceEnabled){
                    const newKeys = new Set();
                    (json.current || []).forEach(it => {
                        const key = `${it.ticket_number}|${it.counter_code}|${it.status}|${it.called_at||''}`;
                        newKeys.add(key);
                        if (!lastAnnouncedKeys.has(key) && it.status === 'called'){
                            speak(`Nomor antrian ${it.ticket_number} atas nama ${it.visitor}, menuju loket nomor ${it.counter_code}.`);
                        }
                    });
                    lastAnnouncedKeys = newKeys;
                }
                setConn(true);
            } catch (e) { setConn(false); }
        }
        fetchData(); setInterval(fetchData, 5000);

        function speak(text){ try { const utter = new SpeechSynthesisUtterance(text); utter.lang = 'id-ID'; utter.rate=1.0; utter.pitch=1.0; utter.volume=1.0; window.speechSynthesis.cancel(); window.speechSynthesis.speak(utter);} catch(_){} }

        @auth
        const settingsModal = (window.bootstrap && document.getElementById('settingsModal')) ? new bootstrap.Modal(document.getElementById('settingsModal')) : null;
        const settingsForm = document.getElementById('settingsForm');
        const sTheme = document.getElementById('s_theme');
        const sVoice = document.getElementById('s_voice');
        const sTicker = document.getElementById('s_ticker');
        sTheme.value = initialTheme; sVoice.checked = !!initialVoice; sTicker.value = initialTicker;
        document.getElementById('btnOpenSettings')?.addEventListener('click', (e)=>{
            e.preventDefault(); if (settingsModal){ settingsModal.show(); return; }
            const el = document.getElementById('settingsModal'); if (!el) return; el.classList.add('show'); el.style.display='block'; document.body.classList.add('modal-open'); document.body.style.overflow='hidden';
        });
        document.querySelectorAll('#settingsModal [data-bs-dismiss="modal"]').forEach(btn=>{ btn.addEventListener('click', ()=>{ if (settingsModal) return; const el = document.getElementById('settingsModal'); if (!el) return; el.classList.remove('show'); el.style.display='none'; document.body.classList.remove('modal-open'); document.body.style.overflow=''; }); });
        settingsForm.addEventListener('submit', async function(e){
            e.preventDefault(); clearInvalid(settingsForm);
            const fd = new FormData(); fd.append('theme', sTheme.value); fd.append('voice_enabled', sVoice.checked ? '1':'0'); fd.append('ticker_text', sTicker.value || ''); fd.append('_method','PATCH');
            const res = await fetch(`{{ route('display.settings.update') }}`, { method:'POST', headers:{'X-CSRF-TOKEN': CSRF, 'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body: fd, credentials:'same-origin' });
            const data = await res.json().catch(()=>({})); if (!res.ok){ if (res.status===422 && data.errors){ Object.keys(data.errors).forEach(k=>{ const fb = settingsForm.querySelector(`.invalid-feedback[data-field="${k}"]`); if (fb) fb.textContent = data.errors[k][0]; }); } toastr?.error?.(data.message||'Gagal menyimpan'); return; }
            applyTheme(data.data?.theme || sTheme.value); voiceEnabled = !!data.data?.voice_enabled; setTicker(data.data?.ticker_text || sTicker.value || ''); settingsModal?.hide(); toastr?.success?.(data.message || 'Tersimpan');
        });
        function clearInvalid(form){ form.querySelectorAll('.invalid-feedback').forEach(el=> el.textContent=''); }
        @endauth
    })();
    </script>
    <script>
      (function(){
        const theme = @json($theme ?? 'light');
        document.body.classList.add(theme==='dark' ? 'display-dark' : 'display-light');
        document.documentElement.setAttribute('data-bs-theme', theme==='dark' ? 'dark' : 'light');
      })();
    </script>
</body>

</html>
