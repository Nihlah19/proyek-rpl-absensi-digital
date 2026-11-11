<?php
$currentAiConfig = class_exists('AppSetting') ? AppSetting::getAiConfig() : ['api_key' => '', 'model' => 'llama-3.3-70b-versatile', 'provider' => 'groq'];
$savedApiKey   = $currentAiConfig['api_key'] ?? '';
$savedModel    = $currentAiConfig['model'] ?? 'llama-3.3-70b-versatile';
$savedProvider = $currentAiConfig['provider'] ?? 'groq';
?>
<!-- ── MODAL KONFIGURASI AI CHATBOT (MULTI-PROVIDER: GROQ, xAI GROK & GEMINI) ── -->
<div id="aiConfigModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-4" onclick="if(event.target===this) closeAiConfigModal()">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-xl max-h-[92vh] flex flex-col overflow-hidden animate-fade-in">
        <!-- Header Modal -->
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/80 dark:bg-slate-800/90">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base border border-emerald-200 dark:border-emerald-800/40">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Pengaturan Integrasi Chatbot</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Atur Provider (Groq / xAI Grok / Gemini), API Key, dan Model AI</p>
                </div>
            </div>
            <button type="button" onclick="closeAiConfigModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1">
            <!-- Pilihan Provider -->
            <div>
                <label for="ai_provider_select" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Provider AI <span class="text-rose-500">*</span>
                </label>
                <select id="ai_provider_select" onchange="onProviderChanged()" class="vh-input w-full text-xs font-semibold">
                    <option value="gemini" <?= ($savedProvider === 'gemini') ? 'selected' : '' ?>>Google Gemini (Gemini 3.5 Flash, 3.8 Flash) — Cepat &amp; Cerdas</option>
                    <option value="groq" <?= ($savedProvider === 'groq') ? 'selected' : '' ?>>Groq Cloud (Llama 3.3, Llama 3.1) — Cepat &amp; Efisien</option>
                    <option value="grok" <?= ($savedProvider === 'grok') ? 'selected' : '' ?>>xAI Grok (Grok 2, Grok Beta)</option>
                    <option value="auto">Deteksi Otomatis dari Awalan API Key</option>
                </select>
            </div>

            <!-- Input API Key (Menggunakan text dengan CSS masking agar tidak pernah memicu popup password manager browser) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="ai_input_api_key" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        API Key <span class="text-rose-500">*</span>
                    </label>
                    <span id="keyFormatHint" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono font-medium">
                        <?= ($savedProvider === 'groq') ? 'Key: gsk_...' : (($savedProvider === 'grok') ? 'Key: xai-...' : 'Key: AQ... / AIza...') ?>
                    </span>
                </div>
                <div class="relative">
                    <input type="text" id="ai_input_api_key" name="ai_credential_token"
                           placeholder="Masukkan API Key (misal: AQ... atau AIza... atau gsk_...)"
                           value="<?= htmlspecialchars($savedApiKey) ?>"
                           autocomplete="off"
                           autocorrect="off"
                           autocapitalize="off"
                           spellcheck="false"
                           data-lpignore="true"
                           data-1p-ignore="true"
                           data-bwignore="true"
                           data-form-type="other"
                           data-masked="true"
                           style="-webkit-text-security: disc; -moz-text-security: disc;"
                           oninput="onApiKeyInput(this.value)"
                           class="vh-input w-full pr-10 text-xs font-mono">
                    <button type="button" onclick="toggleApiKeyVisibility()" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-1" title="Lihat / Sembunyikan Key">
                        <i id="eyeIconApiKey" class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                    <span>Mendukung Groq Cloud (console.groq.com), xAI (x.ai), atau Gemini.</span>
                    <button type="button" onclick="fetchLiveModels()" id="btnFetchModels" class="text-emerald-600 hover:text-emerald-700 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate"></i> Muat Model Tersedia
                    </button>
                </div>
            </div>

            <!-- Model Selection -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Pilih Model AI Chatbot:
                    </label>
                    <span id="modelLoadingIndicator" class="hidden text-[10px] text-emerald-600 font-medium">
                        <i class="fa-solid fa-spinner fa-spin"></i> Memeriksa model...
                    </span>
                </div>

                <div class="space-y-2" id="aiModelListContainer">
                    <!-- Default Model List disajikan dinamis -->
                    <?php
                    $defaultModels = AppSetting::getDefaultModels($savedProvider);
                    foreach ($defaultModels as $dm):
                        $isChecked = ($savedModel === $dm['id'] || (empty($savedModel) && !empty($dm['is_recommended'])));
                    ?>
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer bg-slate-50/50 dark:bg-slate-900/40 transition">
                        <input type="radio" name="selected_ai_model" value="<?= htmlspecialchars($dm['id']) ?>" <?= $isChecked ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white font-mono"><?= htmlspecialchars($dm['id']) ?></span>
                                <?php if (!empty($dm['badge'])): ?>
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300"><?= htmlspecialchars($dm['badge']) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars($dm['name'] ?? $dm['id']) ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Optional Custom Model -->
            <div class="pt-1">
                <label for="ai_custom_model" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                    Atau ketik model kustom secara manual:
                </label>
                <input type="text" id="ai_custom_model" placeholder="contoh: llama-3.3-70b-versatile atau grok-2-latest"
                       value=""
                       class="vh-input w-full text-xs font-mono">
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2 text-xs">
            <button type="button" onclick="closeAiConfigModal()" class="vh-btn-secondary text-xs px-4 py-2">
                Batal
            </button>
            <button type="button" onclick="saveAiConfig()" id="btnSaveAi" class="vh-btn-primary text-xs flex items-center gap-1.5 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-xs cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Konfigurasi</span>
            </button>
        </div>
    </div>
</div>

<script>
function openAiConfigModal() {
    const modal = document.getElementById('aiConfigModal');
    if (modal) modal.classList.remove('hidden');
}

function closeAiConfigModal() {
    const modal = document.getElementById('aiConfigModal');
    if (modal) modal.classList.add('hidden');
    if (window.location.hash === '#ai-config') {
        history.replaceState(null, null, ' ');
    }
}

function toggleApiKeyVisibility() {
    const inp = document.getElementById('ai_input_api_key');
    const ico = document.getElementById('eyeIconApiKey');
    if (!inp || !ico) return;
    const isMasked = (inp.getAttribute('data-masked') === 'true' || inp.style.webkitTextSecurity !== 'none');
    if (isMasked) {
        inp.style.webkitTextSecurity = 'none';
        inp.style.mozTextSecurity = 'none';
        inp.setAttribute('data-masked', 'false');
        ico.className = 'fa-solid fa-eye-slash';
    } else {
        inp.style.webkitTextSecurity = 'disc';
        inp.style.mozTextSecurity = 'disc';
        inp.setAttribute('data-masked', 'true');
        ico.className = 'fa-solid fa-eye';
    }
}

function onProviderChanged() {
    const prov = document.getElementById('ai_provider_select').value;
    const hint = document.getElementById('keyFormatHint');
    if (prov === 'groq') {
        hint.textContent = 'Key: gsk_...';
    } else if (prov === 'grok') {
        hint.textContent = 'Key: xai-...';
    } else if (prov === 'gemini') {
        hint.textContent = 'Key: AQ... / AIza...';
    } else {
        hint.textContent = 'Otomatis';
    }
}

function onApiKeyInput(val) {
    val = val.trim();
    const select = document.getElementById('ai_provider_select');
    if (val.startsWith('gsk_')) {
        select.value = 'groq';
    } else if (val.startsWith('xai-')) {
        select.value = 'grok';
    } else if (val.startsWith('AIza') || val.startsWith('AQ.')) {
        select.value = 'gemini';
    }
    onProviderChanged();
}

function getSelectedAiModel() {
    const custom = document.getElementById('ai_custom_model')?.value.trim();
    if (custom) return custom;
    const radios = document.getElementsByName('selected_ai_model');
    for (let r of radios) {
        if (r.checked) return r.value;
    }
    return 'llama-3.3-70b-versatile';
}

async function fetchLiveModels() {
    const btn = document.getElementById('btnFetchModels');
    const orig = btn.innerHTML;
    const apiKey = document.getElementById('ai_input_api_key').value.trim();
    const provider = document.getElementById('ai_provider_select').value;
    const loader = document.getElementById('modelLoadingIndicator');

    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menghubungi API...`;
    if (loader) loader.classList.remove('hidden');

    try {
        const fd = new FormData();
        fd.append('api_key', apiKey);
        fd.append('provider', provider);

        const res = await fetch('<?= BASEURL ?>/dashboard/fetch_ai_models', {
            method: 'POST',
            body: fd
        });
        const json = await res.json();

        if (json.models && json.models.length > 0) {
            renderModelsList(json.models);
            alert(json.message || 'Berhasil memuat daftar model aktif!');
        } else {
            alert(json.message || 'Tidak ada model yang ditemukan.');
        }
    } catch (e) {
        alert('Gagal mengambil model dari server. Menggunakan daftar rekomendasi.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
        if (loader) loader.classList.add('hidden');
    }
}

function renderModelsList(models) {
    const container = document.getElementById('aiModelListContainer');
    if (!container) return;

    let html = '';
    models.forEach((m, idx) => {
        const isChecked = (idx === 0);
        const badge = m.badge ? `<span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">${m.badge}</span>` : '';
        html += `
            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer bg-slate-50/50 dark:bg-slate-900/40 transition">
                <input type="radio" name="selected_ai_model" value="${m.id}" ${isChecked ? 'checked' : ''} class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">${m.id}</span>
                        ${badge}
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">${m.name || m.id}</p>
                </div>
            </label>
        `;
    });

    container.innerHTML = html;
}

async function saveAiConfig() {
    const btn = document.getElementById('btnSaveAi');
    const orig = btn.innerHTML;
    const apiKey = document.getElementById('ai_input_api_key').value.trim();
    const provider = document.getElementById('ai_provider_select').value;
    const model = getSelectedAiModel();

    if (!apiKey) {
        alert('API Key tidak boleh kosong.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

    try {
        const fd = new FormData();
        fd.append('api_key', apiKey);
        fd.append('provider', provider);
        fd.append('model', model);

        const res = await fetch('<?= BASEURL ?>/dashboard/save_ai_config', {
            method: 'POST',
            body: fd
        });
        const json = await res.json();

        if (json.success) {
            alert(json.message || 'Pengaturan Integrasi Chatbot berhasil disimpan!');
            closeAiConfigModal();
        } else {
            alert(json.message || 'Gagal menyimpan konfigurasi.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan saat menyimpan.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
}

// Auto open modal jika URL hash #ai-config
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#ai-config') {
        openAiConfigModal();
    }
});
window.addEventListener('hashchange', function() {
    if (window.location.hash === '#ai-config') {
        openAiConfigModal();
    }
});
</script>
