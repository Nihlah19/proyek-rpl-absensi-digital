<?php
$currentAiConfig = class_exists('AppSetting') ? AppSetting::getAiConfig() : ['api_key' => '', 'model' => 'gemini-2.5-flash'];
$savedApiKey = $currentAiConfig['api_key'] ?? '';
$savedModel = $currentAiConfig['model'] ?? 'gemini-2.5-flash';
?>
<!-- ── MODAL KONFIGURASI GOOGLE GEMINI AI CHATBOT ── -->
<div id="aiConfigModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-4" onclick="if(event.target===this) closeAiConfigModal()">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden animate-fade-in">
        <!-- Header Modal -->
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/80 dark:bg-slate-800/90">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base border border-purple-200 dark:border-purple-800/40">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Konfigurasi Google Gemini AI</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih model AI dan atur API Key untuk asisten virtual sekolah</p>
                </div>
            </div>
            <button type="button" onclick="closeAiConfigModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1">
            <!-- Input API Key -->
            <div>
                <label for="ai_input_api_key" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Google Gemini API Key <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="ai_input_api_key" placeholder="AIzaSy..."
                           value="<?= htmlspecialchars($savedApiKey) ?>"
                           class="vh-input w-full pr-10 text-xs font-mono">
                    <button type="button" onclick="toggleApiKeyVisibility()" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-1" title="Lihat / Sembunyikan Key">
                        <i id="eyeIconApiKey" class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Dapatkan API Key gratis di Google AI Studio (aistudio.google.com).</p>
            </div>

            <!-- Model Selection (Semua Model Dikeluarkan Langsung) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Pilih Model Google Gemini:
                </label>
                <div class="space-y-2.5" id="aiModelListContainer">
                    
                    <!-- 1. gemini-2.5-flash (Default & Rekomendasi) -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer bg-slate-50/50 dark:bg-slate-900/40 transition">
                        <input type="radio" name="selected_ai_model" value="gemini-2.5-flash" <?= ($savedModel === 'gemini-2.5-flash' || empty($savedModel)) ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">gemini-2.5-flash</span>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">Rekomendasi</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Sangat cepat, responsif, dan hemat kuota untuk konsultasi presensi harian.</p>
                        </div>
                    </label>

                    <!-- 2. gemini-2.5-pro -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer bg-slate-50/50 dark:bg-slate-900/40 transition">
                        <input type="radio" name="selected_ai_model" value="gemini-2.5-pro" <?= ($savedModel === 'gemini-2.5-pro') ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">gemini-2.5-pro</span>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">Penalaran Tinggi</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Penalaran tingkat lanjut, sangat akurat untuk rekap data dan pertanyaan kompleks.</p>
                        </div>
                    </label>

                    <!-- 3. gemini-2.0-flash -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer bg-slate-50/50 dark:bg-slate-900/40 transition">
                        <input type="radio" name="selected_ai_model" value="gemini-2.0-flash" <?= ($savedModel === 'gemini-2.0-flash') ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">gemini-2.0-flash</span>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300">Generasi 2.0</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Model generasi 2.0 dengan kecepatan transmisi tinggi dan performa seimbang.</p>
                        </div>
                    </label>

                    <!-- 4. gemini-1.5-flash -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer bg-slate-50/50 dark:bg-slate-900/40 transition">
                        <input type="radio" name="selected_ai_model" value="gemini-1.5-flash" <?= ($savedModel === 'gemini-1.5-flash') ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">gemini-1.5-flash</span>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">Stabil</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Model stabil generasi 1.5 untuk percakapan asisten sekolah sehari-hari.</p>
                        </div>
                    </label>

                    <!-- 5. gemini-1.5-pro -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 cursor-pointer bg-slate-50/50 dark:bg-slate-900/40 transition">
                        <input type="radio" name="selected_ai_model" value="gemini-1.5-pro" <?= ($savedModel === 'gemini-1.5-pro') ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white font-mono">gemini-1.5-pro</span>
                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">Konteks Besar</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Dukungan jendela konteks besar untuk analisis riwayat absensi panjang.</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Optional Custom Model -->
            <div class="pt-1">
                <label for="ai_custom_model" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                    Atau gunakan nama model lain (kustom):
                </label>
                <input type="text" id="ai_custom_model" placeholder="contoh: gemini-2.0-flash-exp"
                       value="<?= (!in_array($savedModel, ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-pro']) && !empty($savedModel)) ? htmlspecialchars($savedModel) : '' ?>"
                       class="vh-input w-full text-xs font-mono">
            </div>
        </div>

        <!-- Footer Modal: Langsung Tombol Simpan -->
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2 text-xs">
            <button type="button" onclick="closeAiConfigModal()" class="vh-btn-secondary text-xs px-4 py-2">
                Batal
            </button>
            <button type="button" onclick="saveGeminiConfig()" id="btnSaveGemini" class="vh-btn-primary text-xs flex items-center gap-1.5 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-xs cursor-pointer">
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
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'fa-solid fa-eye-slash';
    } else {
        inp.type = 'password';
        ico.className = 'fa-solid fa-eye';
    }
}

function getSelectedAiModel() {
    const custom = document.getElementById('ai_custom_model')?.value.trim();
    if (custom) return custom;
    const radios = document.getElementsByName('selected_ai_model');
    for (let r of radios) {
        if (r.checked) return r.value;
    }
    return 'gemini-2.5-flash';
}

async function saveGeminiConfig() {
    const btn = document.getElementById('btnSaveGemini');
    const orig = btn.innerHTML;
    const apiKey = document.getElementById('ai_input_api_key').value.trim();
    const model = getSelectedAiModel();

    if (!apiKey) {
        alert('API Key Google Gemini tidak boleh kosong.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

    try {
        const fd = new FormData();
        fd.append('api_key', apiKey);
        fd.append('model', model);

        const res = await fetch('<?= BASEURL ?>/dashboard/save_ai_config', {
            method: 'POST',
            body: fd
        });
        const json = await res.json();

        if (json.success) {
            alert(json.message || 'Konfigurasi Google Gemini AI berhasil disimpan!');
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
