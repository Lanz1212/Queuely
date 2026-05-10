<x-filament-panels::page>
    <div wire:poll.2s class="font-sans text-gray-800 dark:text-gray-200 space-y-6">
        
        <!-- Top Section: Header & Gates -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 p-6 transition-colors">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                        <x-heroicon-s-speaker-wave class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">Loket Panggilan Antrian</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Sistem manajemen antrian terintegrasi</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Live Update</span>
                </div>
            </div>

            <!-- Gates -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($gates as $gate)
                    @php
                        $isActive = $selectedGateId == $gate->id;
                    @endphp
                    <button wire:click="selectGate({{ $gate->id }})" class="px-4 py-5 rounded-xl border transition-all text-center flex flex-col items-center justify-center gap-1.5
                        @if($isActive) bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30
                        @else bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-white/10 hover:border-blue-300 dark:hover:border-blue-500 @endif
                    ">
                        <h3 class="text-base font-bold">{{ $gate->name }}</h3>
                        <p class="text-xs font-medium @if($isActive) text-blue-100 @else text-gray-500 dark:text-gray-400 @endif leading-tight">
                            {{ $gate->notes ?: 'Layanan Umum' }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full @if($gate->status == 'ready') bg-emerald-400 @elseif($gate->status == 'busy') bg-yellow-400 @else bg-red-400 @endif"></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider @if($isActive) text-white @else text-gray-500 dark:text-gray-400 @endif">
                                @if($gate->status == 'ready') Aktif @elseif($gate->status == 'busy') Sibuk @else Tutup @endif
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Current Active Queue / Audio -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm relative overflow-hidden flex flex-col items-center text-center min-h-[320px] justify-center transition-colors" x-data="voiceController()">
                    @if($activeQueue)
                        <div x-show="!isPlaying" class="flex flex-col items-center w-full">
                            <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center mb-4">
                                <x-heroicon-s-user class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h2 class="text-7xl font-black text-gray-900 dark:text-white tracking-tighter mb-1">{{ $activeQueue->queue_number }}</h2>
                            <p class="text-lg font-bold text-gray-500 dark:text-gray-400 mb-6">{{ $activeQueue->service->name }}</p>
                            
                            <span class="px-4 py-1.5 rounded-full text-xs font-bold tracking-widest uppercase bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800 mb-8">
                                SEDANG DILAYANI
                            </span>
                            
                            <div class="flex flex-wrap gap-3 justify-center w-full max-w-md">
                                <button wire:click="changeStatus({{ $activeQueue->id }}, 'completed')" class="flex-1 py-3.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 font-bold flex items-center justify-center gap-2 transition border border-red-100 dark:border-red-800/50">
                                    <x-heroicon-s-check-circle class="w-5 h-5" />
                                    Selesai
                                </button>
                                <button @click="playCall('{{ $activeQueue->queue_number }}', '{{ $gates->find($selectedGateId)?->name }}')" wire:click="recall({{ $activeQueue->id }})" class="flex-1 py-3.5 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 font-bold flex items-center justify-center gap-2 transition border border-blue-100 dark:border-blue-800/50">
                                    <x-heroicon-s-arrow-path class="w-5 h-5" />
                                    Panggil Ulang
                                </button>
                            </div>
                        </div>

                        <!-- Smooth Sonar/Ripple Visualizer Animation -->
                        <div x-show="isPlaying" style="display: none;" class="w-full flex flex-col items-center justify-center">
                            <h2 class="text-5xl font-black text-blue-600 dark:text-blue-400 mb-10">{{ $activeQueue->queue_number }}</h2>
                            
                            <div class="relative flex items-center justify-center w-32 h-32 mb-10">
                                <div class="absolute inset-0 bg-blue-500 rounded-full opacity-20 animate-ping" style="animation-duration: 2s;"></div>
                                <div class="absolute inset-2 bg-blue-500 rounded-full opacity-30 animate-ping" style="animation-duration: 2s; animation-delay: 0.5s;"></div>
                                <div class="absolute inset-4 bg-blue-500 rounded-full flex items-center justify-center shadow-xl shadow-blue-500/50">
                                    <x-heroicon-s-speaker-wave class="w-12 h-12 text-white animate-pulse" />
                                </div>
                            </div>

                            <p class="text-sm font-bold text-blue-600 dark:text-blue-400 animate-pulse">
                                Sedang memanggil pasien...
                            </p>
                        </div>
                        
                    @else
                        <!-- Empty State Antrian Saat Ini -->
                        <div class="flex-1 flex flex-col items-center justify-center">
                            <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-6">
                                <x-heroicon-s-clock class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300 mb-2">Tidak ada pasien yang sedang dipanggil</h2>
                            <p class="text-sm text-gray-400 dark:text-gray-500">Klik tombol "Panggil Antrian Selanjutnya" untuk memulai</p>
                        </div>
                    @endif

                    <!-- Hidden Audio Element -->
                    <audio id="caller-bell" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
                </div>

                <!-- Daftar Antrian Block -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden p-6 transition-colors">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">Daftar Antrian</h3>
                        <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-3 py-1 rounded-full text-xs font-bold border border-blue-100 dark:border-blue-800/50">
                            {{ $waitingQueues->count() }} Menunggu
                        </span>
                    </div>

                    <button wire:click="callNext" @if(!$selectedGateId || $waitingQueues->isEmpty() || ($gates->find($selectedGateId)?->status !== 'ready')) disabled @endif class="w-full py-4 rounded-xl text-sm font-bold shadow-sm flex justify-center items-center gap-2 transition-all mb-6
                        @if($selectedGateId && $waitingQueues->isNotEmpty() && ($gates->find($selectedGateId)?->status === 'ready')) bg-blue-600 text-white hover:bg-blue-700 shadow-blue-500/20
                        @else bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed @endif">
                        <x-heroicon-s-speaker-wave class="w-5 h-5" />
                        Panggil Antrian Selanjutnya
                    </button>

                    <!-- Waiting List -->
                    <div class="space-y-3">
                        @forelse($waitingQueues->take(5) as $q)
                            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                                        {{ substr($q->queue_number, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $q->queue_number }}</p>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                            {{ $q->vehicle_plate ?: 'Tanpa Plat' }} &bull; {{ $q->service->name }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-xs font-bold text-gray-400">{{ \Carbon\Carbon::parse($q->registered_at)->format('H:i') }}</span>
                                    <button wire:click="callSpecific({{ $q->id }})" class="opacity-0 group-hover:opacity-100 p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition">
                                        <x-heroicon-s-play class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mb-4">
                                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                                </div>
                                <p class="text-sm text-gray-400 dark:text-gray-500">Daftar antrian kosong</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Right Column (Status & Stats) -->
            <div class="space-y-6">
                
                <!-- Status Loket -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm p-6 flex flex-col transition-colors">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white mb-8">Status Loket</h3>
                    
                    @php $currentGate = $gates->find($selectedGateId); @endphp
                    @if($currentGate)
                        <div class="flex flex-col items-center text-center flex-1">
                            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-6
                                @if($currentGate->status == 'ready') bg-emerald-500
                                @elseif($currentGate->status == 'busy') bg-yellow-500
                                @else bg-red-500 @endif
                            ">
                                @if($currentGate->status == 'ready')
                                    <x-heroicon-o-check-circle class="w-10 h-10 text-white" />
                                @elseif($currentGate->status == 'busy')
                                    <x-heroicon-o-minus-circle class="w-10 h-10 text-white" />
                                @else
                                    <x-heroicon-o-x-circle class="w-10 h-10 text-white" />
                                @endif
                            </div>
                            
                            <p class="text-xs font-medium text-gray-400 mb-2">{{ $currentGate->name }} - {{ $currentGate->notes ?: 'Layanan Umum' }}</p>
                            <h2 class="text-lg font-black tracking-wide mb-8
                                @if($currentGate->status == 'ready') text-emerald-600 dark:text-emerald-400
                                @elseif($currentGate->status == 'busy') text-yellow-600 dark:text-yellow-400
                                @else text-red-600 dark:text-red-400 @endif
                            ">
                                @if($currentGate->status == 'ready') SEDANG BUKA
                                @elseif($currentGate->status == 'busy') SIBUK
                                @else TUTUP @endif
                            </h2>
                            
                            <button wire:click="toggleGateStatus" class="w-full mt-auto py-3.5 rounded-xl font-bold text-white transition text-sm
                                @if($currentGate->status == 'ready') bg-red-500 hover:bg-red-600
                                @else bg-emerald-500 hover:bg-emerald-600 @endif
                            ">
                                @if($currentGate->status == 'ready') Tutup Loket @else Buka Loket @endif
                            </button>
                        </div>
                    @else
                        <div class="py-16 text-center text-gray-400 text-sm">Pilih gate terlebih dahulu.</div>
                    @endif
                </div>

                <!-- Statistik Hari Ini -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm p-6 transition-colors">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white mb-6">Statistik Hari Ini</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-blue-50/50 dark:bg-blue-900/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center shadow-sm shadow-blue-500/20">
                                    <x-heroicon-s-users class="w-5 h-5 text-white" />
                                </div>
                                <span class="font-bold text-sm text-gray-700 dark:text-gray-300">Total Pasien</span>
                            </div>
                            <span class="text-xl font-black text-gray-900 dark:text-white">{{ $totalToday }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-900/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center shadow-sm shadow-emerald-500/20">
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-white" />
                                </div>
                                <span class="font-bold text-sm text-gray-700 dark:text-gray-300">Selesai</span>
                            </div>
                            <span class="text-xl font-black text-gray-900 dark:text-white">{{ $completedToday }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('voiceController', () => ({
                    isPlaying: false,
                    
                    init() {
                        Livewire.on('trigger-play-call', (data) => {
                            this.playCall(data[0].queueNumber, data[0].gateName);
                        });
                    },
                    
                    playCall(queueNumber, gateName) {
                        if (this.isPlaying) return;
                        this.isPlaying = true;
                        
                        const text = `Nomor antrian, ${queueNumber}, silakan menuju ke ${gateName}`;
                        const bell = document.getElementById('caller-bell');
                        
                        bell.currentTime = 0;
                        bell.play().then(() => {
                            setTimeout(() => {
                                const utterance = new SpeechSynthesisUtterance(text);
                                utterance.lang = 'id-ID';
                                utterance.rate = 0.85;
                                
                                utterance.onend = () => {
                                    bell.currentTime = 0;
                                    bell.play().then(() => {
                                        setTimeout(() => {
                                            this.isPlaying = false;
                                        }, 2000);
                                    });
                                };
                                
                                window.speechSynthesis.speak(utterance);
                            }, 1500);
                        }).catch(e => {
                            console.error("Audio block", e);
                            this.isPlaying = false;
                        });
                    }
                }));
            });
        </script>
    </div>
</x-filament-panels::page>
