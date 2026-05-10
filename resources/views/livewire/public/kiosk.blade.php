<div class="min-h-screen bg-gray-100 flex items-center justify-center p-4 font-sans">
    <div class="max-w-4xl w-full bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
        
        <!-- Left Side: Branding -->
        <div class="bg-blue-600 text-white p-8 md:w-1/3 flex flex-col justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <h1 class="text-3xl font-bold text-center mb-2">Sistem Antrian</h1>
            <p class="text-blue-200 text-center">Gudang & Logistik</p>
        </div>

        <!-- Right Side: Interaction -->
        <div class="p-8 md:w-2/3">
            @if($queueResult)
                <!-- SUCCESS / RECEIPT VIEW -->
                <div class="text-center" id="receipt-area">
                    <div class="mb-6">
                        <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-800 mt-2">Pendaftaran Berhasil!</h2>
                    </div>

                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-8 mb-6 inline-block w-full">
                        <p class="text-gray-500 text-sm uppercase tracking-wide">Nomor Antrian Anda</p>
                        <p class="text-6xl font-black text-blue-600 my-2">{{ $queueResult->queue_number }}</p>
                        <p class="text-gray-700 font-semibold">{{ $queueResult->service->name }}</p>
                        
                        <div class="mt-6 flex justify-center">
                            <!-- Basic QR display placeholder using a public service for demo, ideally we generate via package -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('tracking', $queueResult->qr_code_hash)) }}" alt="QR Code" class="rounded-lg shadow-sm">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Scan untuk memantau status secara realtime</p>
                    </div>

                    <div class="flex gap-4 justify-center print:hidden">
                        <button wire:click="printReceipt" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg flex items-center gap-2 transition-all">
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
                    <button wire:click="cancel" class="text-gray-500 hover:text-gray-800 mb-6 flex items-center gap-1 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Kembali
                    </button>

                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Layanan: {{ $selectedService->name }}</h2>
                    <p class="text-gray-600 mb-6">Silakan lengkapi data di bawah (Opsional) atau langsung cetak nomor antrian.</p>

                    <form wire:submit="registerQueue" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supir / Pengantar</label>
                            <input wire:model="driverName" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4" placeholder="Misal: Budi">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No Polisi / Plat Kendaraan</label>
                                <input wire:model="vehiclePlate" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 uppercase" placeholder="Misal: B 1234 CD">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Asal Ekspedisi / Perusahaan</label>
                                <input wire:model="company" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4" placeholder="Misal: PT Logistik Indah">
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-lg font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                                Ambil Nomor Antrian
                            </button>
                        </div>
                    </form>
                </div>

            @else
                <!-- SERVICE SELECTION VIEW -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Pilih Layanan</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($services as $service)
                            <button wire:click="selectService({{ $service->id }})" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 group text-left w-full h-full">
                                <span class="w-12 h-12 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform" style="background-color: {{ $service->color ?? '#3B82F6' }}20; color: {{ $service->color ?? '#3B82F6' }}">
                                    <span class="text-xl font-bold">{{ $service->code_prefix }}</span>
                                </span>
                                <span class="text-lg font-semibold text-gray-800 text-center">{{ $service->name }}</span>
                                <span class="text-sm text-gray-500 mt-1">Estimasi: {{ $service->estimated_time }} mnt</span>
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
                window.print();
            });
        });
    </script>
    
    <style>
        @media print {
            body * { visibility: hidden; }
            #receipt-area, #receipt-area * { visibility: visible; }
            #receipt-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 20px; box-shadow: none; border: none; }
        }
    </style>
</div>
