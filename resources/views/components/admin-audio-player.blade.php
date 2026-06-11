{{-- Global Admin Audio Player --}}
{{-- Di-inject ke semua halaman Admin Panel via renderHook --}}
{{-- Polling endpoint /admin-audio-poll setiap 2 detik untuk mendeteksi panggilan antrian baru --}}
<div id="admin-audio-player" style="display:none">
    <audio id="global-bell-open"  src="{{ asset('sounds/bell-open.mp3') }}"  preload="auto"></audio>
    <audio id="global-bell-close" src="{{ asset('sounds/bell-close.mp3') }}" preload="auto"></audio>
</div>

{{-- Tombol speaker minimalis: pojok kanan bawah --}}
<style>
    #queuely-speaker-btn { position:fixed;bottom:14px;right:14px;z-index:9999;width:34px;height:34px;border-radius:50%;border:none;outline:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s,box-shadow .2s; }
    #queuely-speaker-btn.qs-locked  { background:rgba(245,158,11,.18);box-shadow:0 0 0 0 rgba(245,158,11,.5);animation:qsPulse 2s ease-in-out infinite; }
    #queuely-speaker-btn.qs-active  { background:rgba(16,185,129,.15);box-shadow:none;cursor:default; }
    @keyframes qsPulse { 0%,100%{box-shadow:0 0 0 0 rgba(245,158,11,.45)} 50%{box-shadow:0 0 0 6px rgba(245,158,11,0)} }
</style>
<button id="queuely-speaker-btn" class="qs-locked" title="Klik untuk aktifkan speaker" onclick="window.__queuelyAudioUnlock()">
    {{-- Icon muted (default) --}}
    <svg id="qs-icon-muted" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#d97706" style="width:17px;height:17px">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
    </svg>
    {{-- Icon active (tersembunyi sampai unlock) --}}
    <svg id="qs-icon-active" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#059669" style="width:17px;height:17px;display:none">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
    </svg>
</button>

<script>
(function () {
    'use strict';

    /* ─── State ─────────────────────────────────────────── */
    let lastLogId     = parseInt(localStorage.getItem('queuely_audio_last_id') || '0', 10);
    let audioUnlocked = false;
    let isPlaying     = false;
    const callQueue   = [];

    /* ─── BroadcastChannel: koordinasi antar tab ─────────── */
    let bc = null;
    try {
        bc = new BroadcastChannel('queuely_audio');
        bc.onmessage = function (e) {
            if (e.data && e.data.type === 'claimed' && e.data.logId > lastLogId) {
                lastLogId = e.data.logId;
                localStorage.setItem('queuely_audio_last_id', lastLogId);
            }
        };
    } catch (err) {}

    /* ─── UI tombol speaker ──────────────────────────────── */
    function setBadge(unlocked) {
        const btn       = document.getElementById('queuely-speaker-btn');
        const iconMuted = document.getElementById('qs-icon-muted');
        const iconActive= document.getElementById('qs-icon-active');
        if (!btn) return;
        if (unlocked) {
            btn.className   = 'qs-active';
            btn.title       = 'Speaker Aktif';
            if (iconMuted)  iconMuted.style.display  = 'none';
            if (iconActive) iconActive.style.display = '';
        } else {
            btn.className   = 'qs-locked';
            btn.title       = 'Klik untuk aktifkan speaker';
            if (iconMuted)  iconMuted.style.display  = '';
            if (iconActive) iconActive.style.display = 'none';
        }
    }

    /* ─── Fungsi unlock ──────────────────────────────────── */
    function doUnlock(onSuccess, onFail) {
        const el = document.getElementById('global-bell-open');
        if (!el) { if (onSuccess) onSuccess(); return; }
        el.volume = 0;
        el.play().then(function () {
            el.pause(); el.currentTime = 0; el.volume = 1;
            audioUnlocked = true;
            localStorage.setItem('queuely_audio_unlocked', 'true');
            setBadge(true);
            if (onSuccess) onSuccess();
        }).catch(function () {
            audioUnlocked = false;
            setBadge(false);
            if (onFail) onFail();
        });
    }

    /* ─── Auto-restore unlock dari sesi sebelumnya ────────── */
    if (localStorage.getItem('queuely_audio_unlocked') === 'true') {
        /* Coba unlock diam-diam — sukses jika browser masih memberi izin */
        doUnlock(
            function () { playNextInQueue(); },
            null /* gagal: tampil badge muted, tunggu klik user */
        );
    }

    /* ─── Manual unlock via klik tombol ─────────────────── */
    window.__queuelyAudioUnlock = function () {
        if (audioUnlocked) return;
        doUnlock(function () { playNextInQueue(); });
    };

    /* Auto-unlock juga saat klik pertama di mana saja */
    document.addEventListener('click', function () {
        if (!audioUnlocked) window.__queuelyAudioUnlock();
    }, { once: true });

    /* ─── Memutar antrian audio ──────────────────────────── */
    function playNextInQueue() {
        if (isPlaying || callQueue.length === 0 || !audioUnlocked) return;
        isPlaying = true;

        const item      = callQueue.shift();
        const bellOpen  = document.getElementById('global-bell-open');
        const bellClose = document.getElementById('global-bell-close');
        const text      = 'Nomor antrian, ' + item.queueNumber + ', silakan menuju ke ' + item.gateName;

        window.dispatchEvent(new CustomEvent('queuely:audio-start', { detail: item }));

        function onDone() {
            isPlaying = false;
            window.dispatchEvent(new CustomEvent('queuely:audio-end', { detail: item }));
            playNextInQueue();
        }

        bellOpen.currentTime = 0;
        bellOpen.play().then(function () {
            bellOpen.onended = function () {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang  = 'id-ID';
                utterance.rate  = 0.85;
                utterance.onend = function () {
                    bellClose.currentTime = 0;
                    bellClose.play().then(function () {
                        bellClose.onended = onDone;
                    }).catch(onDone);
                };
                window.speechSynthesis.speak(utterance);
            };
        }).catch(function () {
            audioUnlocked = false;
            localStorage.removeItem('queuely_audio_unlocked');
            isPlaying = false;
            setBadge(false);
            callQueue.unshift(item);
        });
    }

    /* ─── Polling endpoint ───────────────────────────────── */
    function pollForCalls() {
        fetch('/admin-audio-poll?after=' + lastLogId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) {
            if (!data || !data.calls || data.calls.length === 0) return;
            data.calls.forEach(function (call) {
                if (call.id <= lastLogId) return;
                const storedId = parseInt(localStorage.getItem('queuely_audio_last_id') || '0', 10);
                if (call.id <= storedId) { lastLogId = storedId; return; }
                lastLogId = call.id;
                localStorage.setItem('queuely_audio_last_id', lastLogId);
                if (bc) bc.postMessage({ type: 'claimed', logId: call.id });
                if (call.queueNumber && call.gateName) {
                    callQueue.push({ queueNumber: call.queueNumber, gateName: call.gateName });
                    playNextInQueue();
                }
            });
        })
        .catch(function () {});
    }

    pollForCalls();
    setInterval(pollForCalls, 2000);

})();
</script>
