<div wire:poll.3s class="min-h-screen bg-gray-900 text-white font-sans flex flex-col">
    <!-- Header -->
    <header class="bg-gray-800 p-6 flex justify-between items-center shadow-lg border-b border-gray-700">
        <div class="flex items-center gap-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <div>
                <h1 class="text-3xl font-black tracking-tight text-white">Sistem Antrian Gudang</h1>
                <p class="text-gray-400">Pusat Distribusi Logistik</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-5xl font-black text-blue-400" id="clock" wire:ignore>00:00:00</p>
            <p class="text-gray-400 uppercase tracking-widest text-sm mt-1" id="date" wire:ignore>---</p>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="flex-1 p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($gateDisplays as $display)
            <div class="bg-gray-800 rounded-2xl border-t-4 shadow-xl flex flex-col overflow-hidden relative
                @if($display['gate']->status == 'maintenance') border-red-500 opacity-75
                @elseif($display['gate']->status == 'busy') border-yellow-500
                @else border-blue-500 @endif
            ">
                <div class="px-6 py-4 bg-gray-800 border-b border-gray-700 flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-white">{{ $display['gate']->name }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                        @if($display['gate']->status == 'maintenance') bg-red-900 text-red-300
                        @elseif($display['gate']->status == 'busy') bg-yellow-900 text-yellow-300
                        @else bg-green-900 text-green-300 @endif
                    ">
                        {{ strtoupper($display['gate']->status) }}
                    </span>
                </div>
                
                <div class="flex-1 flex flex-col items-center justify-center p-8 bg-gradient-to-b from-gray-800 to-gray-900 relative">
                    @if($display['queue'])
                        <p class="text-gray-400 uppercase tracking-widest font-bold mb-2">{{ $display['queue']->service->name }}</p>
                        <h3 class="text-7xl lg:text-8xl font-black text-yellow-400 drop-shadow-lg tracking-tighter mb-4">{{ $display['queue']->queue_number }}</h3>
                        <p class="text-xl px-6 py-2 rounded-lg font-bold bg-gray-700 text-gray-300">
                            @if($display['queue']->status == 'called') MEMANGGIL...
                            @elseif($display['queue']->status == 'heading_to_gate') MENUJU GATE
                            @elseif($display['queue']->status == 'loading') PROSES LOADING
                            @endif
                        </p>
                        @if($display['queue']->status == 'called')
                            <div class="absolute inset-0 border-4 border-yellow-500 animate-pulse rounded-b-2xl pointer-events-none"></div>
                        @endif
                    @else
                        <h3 class="text-5xl font-black text-gray-600 mb-2">KOSONG</h3>
                        <p class="text-gray-500">Menunggu antrian berikutnya...</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Video Placeholder & Call Notification -->
    <div class="bg-gray-800 border-t border-gray-700 p-4 text-center overflow-hidden h-24 flex items-center justify-center">
        <marquee scrollamount="10" class="text-2xl text-blue-300 font-semibold tracking-wide">
            Mohon persiapkan dokumen pengiriman Anda. Harap mengantri dengan tertib sesuai urutan panggilan. Tetap perhatikan keselamatan kerja (K3) selama di area gudang.
        </marquee>
    </div>

    <!-- Audio Player -->
    <audio id="bell" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script>
        // Clock script
        function updateClock() {
            const now = new Date();
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', timeOptions);
            document.getElementById('date').innerText = now.toLocaleDateString('id-ID', dateOptions);
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Audio Queue System
        const audioQueue = [];
        let isPlaying = false;

        document.addEventListener('livewire:init', () => {
            Livewire.on('play-call', (data) => {
                const callText = `Truk dengan nomor antrian, ${data.queueNumber}, silakan masuk ke ${data.gateName}`;
                audioQueue.push(callText);
                playNextInQueue();
            });
        });

        function playNextInQueue() {
            if (isPlaying || audioQueue.length === 0) return;
            isPlaying = true;

            const textToSpeak = audioQueue.shift();
            const bell = document.getElementById('bell');

            bell.currentTime = 0;
            bell.play().then(() => {
                setTimeout(() => {
                    const utterance = new SpeechSynthesisUtterance(textToSpeak);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.85; // slightly slower for clarity
                    
                    utterance.onend = () => {
                        // Play ending bell
                        bell.currentTime = 0;
                        bell.play().then(() => {
                            setTimeout(() => {
                                isPlaying = false;
                                playNextInQueue();
                            }, 2000);
                        });
                    };
                    
                    window.speechSynthesis.speak(utterance);
                }, 1500); // Wait for intro bell to finish roughly
            }).catch(e => {
                console.log("Audio play blocked by browser, user interaction needed");
                isPlaying = false;
            });
        }
        
        // Initial click to unlock audio context in browsers
        document.body.addEventListener('click', function() {
            // Unmute trick if necessary
        }, { once: true });
    </script>
</div>
