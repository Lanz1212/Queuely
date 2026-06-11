<div wire:poll.3s class="min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 font-sans flex flex-col">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 p-4 md:p-6 flex justify-between items-center shadow-sm border-b border-gray-200 dark:border-white/10">
        <div class="flex items-center gap-3 md:gap-4">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-6 md:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-gray-900 dark:text-white leading-tight">Sistem Antrian Gudang</h1>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Pusat Distribusi Logistik</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-3xl md:text-5xl font-black text-blue-600 dark:text-blue-400" id="clock" wire:ignore>00:00:00</p>
            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-1" id="date" wire:ignore>---</p>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="flex-1 p-4 md:p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
        @foreach($gateDisplays as $display)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm flex flex-col overflow-hidden relative
                @if($display['gate']->status == 'maintenance') border-t-4 border-red-500 opacity-75
                @elseif($display['gate']->status == 'busy') border-t-4 border-yellow-500
                @else border-t-4 border-blue-500 @endif
            ">
                <div class="px-4 md:px-6 py-3 md:py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-white/10 flex justify-between items-center">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white">{{ $display['gate']->name }}</h2>
                    <span class="px-2 md:px-3 py-1 rounded-full text-[10px] md:text-xs font-bold uppercase tracking-wider
                        @if($display['gate']->status == 'maintenance') bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800
                        @elseif($display['gate']->status == 'busy') bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800
                        @else bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800 @endif
                    ">
                        {{ strtoupper($display['gate']->status) }}
                    </span>
                </div>
                
                <div class="flex-1 flex flex-col items-center justify-center p-4 md:p-8 bg-gray-50 dark:bg-gray-900/50 relative">
                    @if($display['queue'])
                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 uppercase tracking-widest font-bold mb-2">{{ $display['queue']->service->name }}</p>
                        <h3 class="text-5xl md:text-7xl lg:text-8xl font-black text-blue-600 dark:text-blue-400 tracking-tighter mb-4">{{ $display['queue']->queue_number }}</h3>
                        <p class="text-base md:text-xl px-4 md:px-6 py-2 rounded-lg font-bold
                            @if($display['queue']->status == 'called') bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800
                            @elseif($display['queue']->status == 'loading') bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800
                            @else bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 @endif
                        ">
                            @if($display['queue']->status == 'called') MEMANGGIL...
                            @elseif($display['queue']->status == 'loading') SEDANG MUAT
                            @elseif($display['queue']->status == 'heading_to_gate') MENUJU GATE
                            @else PROSES LOADING
                            @endif
                        </p>
                        @if($display['queue']->status == 'called')
                            <div class="absolute inset-0 border-4 border-blue-500 animate-pulse rounded-b-2xl pointer-events-none"></div>
                        @endif
                    @else
                        <h3 class="text-4xl md:text-5xl font-black text-gray-400 dark:text-gray-600 mb-2">KOSONG</h3>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400">Menunggu antrian berikutnya...</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Video Placeholder & Call Notification -->
    <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-white/10 p-3 md:p-4 text-center overflow-hidden h-20 md:h-24 flex items-center justify-center">
        <marquee scrollamount="8" class="text-lg md:text-2xl text-blue-600 dark:text-blue-400 font-semibold tracking-wide">
            Mohon persiapkan dokumen pengiriman Anda. Harap mengantri dengan tertib sesuai urutan panggilan. Tetap perhatikan keselamatan kerja (K3) selama di area gudang.
        </marquee>
    </div>

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
    </script>
</div>
