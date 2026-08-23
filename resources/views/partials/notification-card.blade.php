<div id="globalNotificationCard" class="pointer-events-none fixed inset-x-0 top-6 z-200 flex justify-center px-4" aria-live="polite">
    <div
        id="globalNotificationCardPanel"
        class="pointer-events-auto hidden w-full max-w-sm scale-95 opacity-0 rounded-3xl bg-white p-5 text-center shadow-2xl transition-all duration-200 ease-out"
        role="status"
    >
        <button type="button" onclick="hideNotificationCard()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600" aria-label="Tutup notifikasi">
            <i class="fas fa-xmark"></i>
        </button>

        <div class="relative mx-auto flex h-14 w-14 items-center justify-center">
            <svg class="absolute inset-0 h-full w-full" viewBox="0 0 80 80" fill="none" aria-hidden="true">
                <path id="globalNotificationCardBlob" d="M40 4c9 0 12 7 20 9s16 3 16 13-8 12-9 21-4 19-16 19-15-8-24-8-17 2-21-9 4-16 4-25S22 4 40 4Z" fill="#EAF7EF"/>
            </svg>
            <span id="globalNotificationCardIconWrap" class="relative flex h-10 w-10 items-center justify-center rounded-full">
                <i id="globalNotificationCardIcon" class="fas text-lg text-white"></i>
            </span>
        </div>

        <h2 id="globalNotificationCardTitle" class="mt-3 text-lg font-bold text-slate-950"></h2>
        <p id="globalNotificationCardMessage" class="mt-1 text-sm leading-6 text-slate-500"></p>

        <button type="button" id="globalNotificationCardAction" class="mt-4 inline-flex h-10 w-full items-center justify-center rounded-full bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            Lanjutkan
        </button>
    </div>
</div>

<script>
let globalNotificationCardTimer = null;

function showNotificationCard(message, tone = 'success', options = {}) {
    const panel = document.getElementById('globalNotificationCardPanel');
    const iconWrap = document.getElementById('globalNotificationCardIconWrap');
    const icon = document.getElementById('globalNotificationCardIcon');
    const blob = document.getElementById('globalNotificationCardBlob');
    const title = document.getElementById('globalNotificationCardTitle');
    const messageEl = document.getElementById('globalNotificationCardMessage');
    const actionBtn = document.getElementById('globalNotificationCardAction');
    if (!panel) return;

    const isSuccess = tone === 'success';
    title.textContent = options.title || (isSuccess ? 'Berhasil' : 'Gagal');
    messageEl.textContent = message || '';
    iconWrap.className = `relative flex h-10 w-10 items-center justify-center rounded-full ${isSuccess ? 'bg-green-600' : 'bg-red-500'}`;
    if (blob) blob.setAttribute('fill', isSuccess ? '#EAF7EF' : '#FDEDED');
    icon.className = `fas text-lg text-white ${isSuccess ? 'fa-check' : 'fa-exclamation'}`;
    actionBtn.textContent = options.actionLabel || (isSuccess ? 'Lanjutkan' : 'Coba Lagi');
    actionBtn.className = `mt-4 inline-flex h-10 w-full items-center justify-center rounded-full px-6 text-sm font-semibold text-white shadow-sm transition ${isSuccess ? 'bg-green-600 hover:bg-green-700' : 'bg-red-500 hover:bg-red-600'}`;
    actionBtn.onclick = options.onAction || hideNotificationCard;

    panel.classList.remove('hidden');
    requestAnimationFrame(() => {
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
    });

    clearTimeout(globalNotificationCardTimer);
    const duration = options.duration ?? 2000;
    if (duration > 0) {
        globalNotificationCardTimer = setTimeout(hideNotificationCard, duration);
    }
}

function hideNotificationCard() {
    const panel = document.getElementById('globalNotificationCardPanel');
    if (!panel) return;
    clearTimeout(globalNotificationCardTimer);
    panel.classList.remove('scale-100', 'opacity-100');
    panel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => panel.classList.add('hidden'), 200);
}

window.showNotificationCard = showNotificationCard;
window.hideNotificationCard = hideNotificationCard;

@if(session('success'))
    document.addEventListener('DOMContentLoaded', () => showNotificationCard(@js(session('success')), 'success'));
@endif
@if(session('error'))
    document.addEventListener('DOMContentLoaded', () => showNotificationCard(@js(session('error')), 'error'));
@endif
</script>
