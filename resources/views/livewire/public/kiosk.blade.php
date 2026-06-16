<div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex items-center justify-center p-4 font-sans" style="width: 100%;">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden flex flex-col md:flex-row relative" style="width: 100%; max-width: 56rem;">
        
        <!-- Back to Admin Button -->
        <a href="{{ url('/admin') }}" class="absolute top-4 left-4 z-10 bg-white/20 hover:bg-white/30 text-white rounded-lg p-2 transition-colors backdrop-blur-sm print:hidden flex items-center gap-1 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="hidden sm:inline">Admin</span>
        </a>

        <!-- Left Side: Branding -->
        <div class="bg-blue-600 text-white p-6 md:p-8 flex flex-col justify-center items-center md:w-1/3 md:flex-shrink-0" style="flex-shrink: 0;">
            <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl bg-white/20 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-center mb-2">Sistem Antrian</h1>
            <p class="text-blue-100 text-center text-sm md:text-base">Gudang & Logistik</p>
        </div>

        <!-- Right Side: Interaction -->
        <div class="p-6 md:p-8 md:w-2/3 md:flex-1" style="flex: 1 1 auto;">
            @if($queueResult)
                <!-- SUCCESS / RECEIPT VIEW -->
                <div class="text-center" id="receipt-area">
                    <div class="mb-6">
                        <div class="w-16 h-16 mx-auto rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Pendaftaran Berhasil!</h2>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/50 border-2 border-dashed border-gray-200 dark:border-white/10 rounded-xl p-6 md:p-8 mb-6 inline-block w-full">
                        <p class="text-gray-500 dark:text-gray-400 text-xs md:text-sm uppercase tracking-wide">Nomor Antrian Anda</p>
                        <p class="text-5xl md:text-6xl font-black text-blue-600 dark:text-blue-400 my-2">{{ $queueResult->queue_number }}</p>
                        <p class="text-gray-700 dark:text-gray-300 font-semibold">{{ $queueResult->service->name }}</p>
                        
                        <div class="mt-6 flex justify-center">
                            @if($qrCodeSvg)
                                <div class="rounded-lg shadow-sm" style="width: 150px; height: 150px;">
                                    {!! $qrCodeSvg !!}
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Scan untuk memantau status secara realtime</p>
                    </div>

                    <div class="flex gap-4 justify-center print:hidden">
                        <button wire:click="printReceipt" class="px-4 md:px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm shadow-blue-500/20 flex items-center gap-2 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                            </svg>
                            Cetak Struk & Selesai
                        </button>
                    </div>
                </div>

            @elseif($selectedService)
                <!-- CONFIRMATION & INPUT VIEW -->
                <div>
                    <button wire:click="cancel" class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 mb-6 flex items-center gap-1 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Kembali
                    </button>

                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-2">Layanan: {{ $selectedService->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Silakan lengkapi seluruh data di bawah ini sebelum mengambil nomor antrian.</p>

                    <form wire:submit="registerQueue" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Supir / Pengantar <span class="text-red-500">*</span></label>
                            <input wire:model="driverName" type="text" required class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 @error('driverName') border-red-500 @enderror" placeholder="Misal: Budi">
                            @error('driverName') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Telepon <span class="text-red-500">*</span></label>
                            <input wire:model="phone" type="text" required class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 @error('phone') border-red-500 @enderror" placeholder="Misal: 08123456789">
                            @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No Polisi / Plat Kendaraan <span class="text-red-500">*</span></label>
                                <input wire:model="vehiclePlate" type="text" required class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 uppercase @error('vehiclePlate') border-red-500 @enderror" placeholder="Misal: B 1234 CD">
                                @error('vehiclePlate') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asal Ekspedisi / Perusahaan <span class="text-red-500">*</span></label>
                                <input wire:model="company" type="text" required class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 @error('company') border-red-500 @enderror" placeholder="Misal: PT Logistik Indah">
                                @error('company') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm shadow-blue-500/20 text-lg font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                                Ambil Nomor Antrian
                            </button>
                        </div>
                    </form>
                </div>

            @else
                <!-- SERVICE SELECTION VIEW -->
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-6">Pilih Layanan</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($services as $service)
                            <button wire:click="selectService({{ $service->id }})" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 dark:border-white/10 rounded-xl hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 group text-left w-full h-full">
                                <span class="w-12 h-12 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform" style="background-color: {{ $service->color ?? '#3B82F6' }}20; color: {{ $service->color ?? '#3B82F6' }}">
                                    <span class="text-xl font-bold">{{ $service->code_prefix }}</span>
                                </span>
                                <span class="text-base md:text-lg font-semibold text-gray-900 dark:text-white text-center">{{ $service->name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">Estimasi: {{ $service->estimated_time }} mnt</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('print-receipt', () => {
                // Wait briefly for DOM to be ready, then trigger native browser print
                setTimeout(() => {
                    window.print();
                    // After print dialog closes (user prints or cancels), reset to service selection
                    setTimeout(() => {
                        Livewire.dispatch('reset-kiosk');
                    }, 500);
                }, 100);
            });
        });
    </script>

    <style>
        @media print {
            @page {
                size: auto;
                margin: 10mm;
            }
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                overflow: visible !important;
                color: #111 !important;
            }
            /* Hide everything by visibility (preserves layout for ancestors of #receipt-area) */
            body * {
                visibility: hidden !important;
                background: transparent !important;
                box-shadow: none !important;
                border-color: transparent !important;
            }
            /* Show only receipt-area and its descendants */
            #receipt-area,
            #receipt-area * {
                visibility: visible !important;
                color: #111 !important;
            }
            /* Position receipt at top of page */
            #receipt-area {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                right: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 20mm !important;
                background: white !important;
                text-align: center !important;
            }
            /* Ensure SVG QR code is sized properly */
            #receipt-area svg {
                width: 150px !important;
                height: 150px !important;
                display: inline-block !important;
                margin: 12px auto !important;
            }
            /* The queue number stays large and prominent */
            #receipt-area .text-5xl,
            #receipt-area .text-6xl {
                color: #2563eb !important;
            }
            /* Hide the action buttons row inside receipt */
            #receipt-area button,
            #receipt-area .print\:hidden {
                display: none !important;
            }
        }
    </style>
</div>
