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

    /* ─── Pre-recorded audio ────────────────────────────── */
    const AUDIO_BASE    = '/sounds/queue/';
    let usePreRecorded  = false;

    (function checkAudioMode() {
        fetch(AUDIO_BASE + 'phrase_nomor-antrian.mp3', { method: 'HEAD', cache: 'no-store' })
            .then(function (r) { usePreRecorded = r.ok; })
            .catch(function () { usePreRecorded = false; });
    })();

    function queueNumberToSegments(queueNumber) {
        var segs = [];
        var cleaned = queueNumber.replace(/[^a-zA-Z0-9]/g, '');
        for (var i = 0; i < cleaned.length; i++) {
            var ch = cleaned[i].toLowerCase();
            if (ch >= 'a' && ch <= 'z') {
                segs.push(AUDIO_BASE + 'letter_' + ch + '.mp3');
            } else if (ch >= '0' && ch <= '9') {
                segs.push(AUDIO_BASE + 'digit_' + ch + '.mp3');
            }
        }
        return segs;
    }

    function buildPlaylist(item) {
        var list = [AUDIO_BASE + 'phrase_nomor-antrian.mp3'];
        list = list.concat(queueNumberToSegments(item.queueNumber));
        list.push(AUDIO_BASE + 'phrase_silakan-menuju-ke.mp3');
        if (item.gateId) {
            list.push(AUDIO_BASE + 'gate_' + item.gateId + '.mp3');
        }
        return list;
    }

    /* ─── Trim silence from decoded AudioBuffer ────────── */
    function trimBuffer(ctx, buffer) {
        var data      = buffer.getChannelData(0);
        var threshold = 0.007;
        var pad       = Math.floor(buffer.sampleRate * 0.015); // 15 ms
        var start = 0, end = data.length - 1;
        for (var i = 0; i < data.length; i++) {
            if (Math.abs(data[i]) > threshold) { start = Math.max(0, i - pad); break; }
        }
        for (var i = data.length - 1; i > start; i--) {
            if (Math.abs(data[i]) > threshold) { end = Math.min(data.length - 1, i + pad); break; }
        }
        if (end <= start) return null;
        var len = end - start + 1;
        var out = ctx.createBuffer(1, len, buffer.sampleRate);
        out.getChannelData(0).set(data.subarray(start, end + 1));
        return out;
    }

    /* ─── Web Audio API seamless chain (primary) ─────────── */
    function playSegmentChainSeamless(urls, onComplete) {
        var ACtx = window.AudioContext || window.webkitAudioContext;
        if (!ACtx) { playSegmentChainLegacy(urls, onComplete); return; }
        var ctx;
        try { ctx = new ACtx(); } catch (e) { playSegmentChainLegacy(urls, onComplete); return; }

        Promise.all(urls.map(function (url) {
            return fetch(url)
                .then(function (r) { return r.ok ? r.arrayBuffer() : null; })
                .catch(function () { return null; });
        })).then(function (rawBuffers) {
            var valid = rawBuffers.filter(function (b) { return b !== null; });
            if (!valid.length) { ctx.close(); onComplete(); return Promise.reject('empty'); }

            return Promise.all(valid.map(function (ab) {
                return new Promise(function (resolve) {
                    ctx.decodeAudioData(ab, resolve, function () { resolve(null); });
                });
            }));

        }).then(function (decoded) {
            var buffers = decoded
                .filter(function (b) { return b !== null; })
                .map(function (b) { return trimBuffer(ctx, b); })
                .filter(function (b) { return b !== null; });

            if (!buffers.length) { ctx.close(); onComplete(); return; }

            var sr  = buffers[0].sampleRate;
            var GAP = Math.floor(sr * 0.05); // 50 ms gap between words
            var totalLen = buffers.reduce(function (s, b) { return s + b.length; }, 0)
                         + Math.max(0, buffers.length - 1) * GAP;

            var combined = ctx.createBuffer(1, totalLen, sr);
            var dest = combined.getChannelData(0);
            var off  = 0;
            buffers.forEach(function (b, i) {
                dest.set(b.getChannelData(0), off);
                off += b.length;
                if (i < buffers.length - 1) off += GAP;
            });

            var src = ctx.createBufferSource();
            src.buffer  = combined;
            src.connect(ctx.destination);
            src.onended = function () { try { ctx.close(); } catch (_) {} onComplete(); };
            src.start(0);

        }).catch(function (err) {
            if (err !== 'empty') { try { ctx.close(); } catch (_) {} }
            onComplete();
        });
    }

    /* ─── Legacy sequential fallback ────────────────────── */
    function playSegmentChainLegacy(urls, onComplete) {
        var index = 0;
        function playNext() {
            if (index >= urls.length) { onComplete(); return; }
            var url   = urls[index++];
            var audio = new Audio(url);
            audio.onended = playNext;
            audio.onerror = playNext;
            audio.play().catch(playNext);
        }
        playNext();
    }

    function playWithRecorded(item, bellClose, onDone) {
        playSegmentChainSeamless(buildPlaylist(item), function () {
            bellClose.currentTime = 0;
            bellClose.play().then(function () {
                bellClose.onended = onDone;
            }).catch(onDone);
        });
    }

    /* ─── TTS fallback (fixed — no deadlock) ────────────── */
    var cachedVoice = null;

    function pickVoice() {
        if (cachedVoice) return cachedVoice;
        if (!window.speechSynthesis) return null;
        var voices = window.speechSynthesis.getVoices();
        if (!voices.length) return null;
        cachedVoice =
            voices.find(function (v) { return (v.lang === 'id-ID' || v.lang === 'id_ID') && v.localService; }) ||
            voices.find(function (v) { return v.localService; }) ||
            null;
        return cachedVoice;
    }

    if (window.speechSynthesis) {
        window.speechSynthesis.addEventListener('voiceschanged', function () { cachedVoice = null; });
    }

    function playWithTTS(item, bellClose, onDone) {
        if (!window.speechSynthesis) { onDone(); return; }

        var text      = 'Nomor antrian, ' + item.queueNumber + ', silakan menuju ke ' + item.gateName;
        var utterance = new SpeechSynthesisUtterance(text);
        var voice     = pickVoice();
        if (voice) utterance.voice = voice;
        utterance.lang = voice ? voice.lang : 'id-ID';
        utterance.rate = 0.85;

        var ttsTimeout = setTimeout(function () {
            window.speechSynthesis.cancel();
            finishTTS();
        }, 12000);

        function finishTTS() {
            clearTimeout(ttsTimeout);
            bellClose.currentTime = 0;
            bellClose.play().then(function () {
                bellClose.onended = onDone;
            }).catch(onDone);
        }

        utterance.onend  = finishTTS;
        utterance.onerror = function () { finishTTS(); };

        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utterance);
    }

    /* ─── Memutar antrian audio ──────────────────────────── */
    function playNextInQueue() {
        if (isPlaying || callQueue.length === 0 || !audioUnlocked) return;
        isPlaying = true;

        var item      = callQueue.shift();
        var bellOpen  = document.getElementById('global-bell-open');
        var bellClose = document.getElementById('global-bell-close');

        window.dispatchEvent(new CustomEvent('queuely:audio-start', { detail: item }));

        function onDone() {
            isPlaying = false;
            window.dispatchEvent(new CustomEvent('queuely:audio-end', { detail: item }));
            playNextInQueue();
        }

        bellOpen.currentTime = 0;
        bellOpen.play().then(function () {
            bellOpen.onended = function () {
                if (usePreRecorded) {
                    playWithRecorded(item, bellClose, onDone);
                } else {
                    playWithTTS(item, bellClose, onDone);
                }
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
                    callQueue.push({ queueNumber: call.queueNumber, gateName: call.gateName, gateId: call.gateId });
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
