<div wire:poll.5s class="min-h-screen bg-gray-50 flex justify-center font-sans">
    <div class="max-w-md w-full bg-white shadow-xl min-h-screen flex flex-col relative overflow-hidden">
        
        <!-- Header -->
        <div class="bg-blue-600 p-6 text-white text-center pb-12 rounded-b-3xl relative z-10">
            <h1 class="text-xl font-bold">Status Antrian Anda</h1>
            <p class="text-blue-200 text-sm opacity-80">{{ \Carbon\Carbon::parse($queue->registered_at)->translatedFormat('l, d F Y') }}</p>
        </div>

        <!-- Main Card -->
        <div class="-mt-8 mx-6 bg-white rounded-2xl shadow-lg p-6 relative z-20 text-center border border-gray-100">
            <p class="text-gray-500 uppercase tracking-widest text-xs font-bold mb-1">{{ $queue->service->name }}</p>
            <h2 class="text-6xl font-black text-blue-600 my-2 tracking-tighter">{{ $queue->queue_number }}</h2>
            
            <div class="mt-4 py-3 rounded-lg border 
                @if($queue->status == 'waiting') bg-yellow-50 border-yellow-200 text-yellow-700
                @elseif($queue->status == 'called' || $queue->status == 'heading_to_gate') bg-green-50 border-green-200 text-green-700
                @elseif($queue->status == 'loading') bg-blue-50 border-blue-200 text-blue-700
                @else bg-gray-50 border-gray-200 text-gray-700 @endif">
                <span class="font-bold uppercase tracking-wide text-sm flex items-center justify-center gap-2">
                    @if($queue->status == 'waiting')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-pulse" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                        Menunggu
                    @elseif($queue->status == 'called')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-bounce" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        Dipanggil ke {{ $queue->gate->name ?? 'Gate' }}
                    @elseif($queue->status == 'heading_to_gate')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-pulse" viewBox="0 0 20 20" fill="currentColor"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" /><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7h-3v7h.05a2.5 2.5 0 004.9 0H17a1 1 0 001-1V9l-2-2h-2z" /></svg>
                        Menuju {{ $queue->gate->name ?? 'Gate' }}
                    @elseif($queue->status == 'loading')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin-slow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" /></svg>
                        Proses Loading
                    @else
                        Selesai
                    @endif
                </span>
            </div>
        </div>

        <!-- Details -->
        <div class="px-6 py-8 flex-1">
            @if($queue->status == 'waiting')
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-100 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Sisa Antrian</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $queuesAhead }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Estimasi</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $estimatedMinutes }} <span class="text-sm font-normal">mnt</span></p>
                    </div>
                </div>
            @endif

            <div class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent">
                <!-- Status Timeline -->
                
                <!-- Registered -->
                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 bg-blue-600 border-blue-600 text-white shadow shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-gray-100 bg-white shadow-sm ml-4 md:ml-0 md:mr-4">
                        <h3 class="font-bold text-gray-800">Tiket Diambil</h3>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($queue->registered_at)->format('H:i') }}</p>
                    </div>
                </div>

                <!-- Called -->
                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group {{ in_array($queue->status, ['called', 'heading_to_gate', 'loading', 'completed']) ? 'is-active' : '' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 shrink-0 {{ in_array($queue->status, ['called', 'heading_to_gate', 'loading', 'completed']) ? 'bg-blue-600 border-blue-600 text-white shadow' : 'bg-white border-gray-300 text-gray-300' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-gray-100 bg-white shadow-sm ml-4 md:ml-0 md:mr-4 {{ in_array($queue->status, ['called', 'heading_to_gate', 'loading', 'completed']) ? '' : 'opacity-50' }}">
                        <h3 class="font-bold {{ in_array($queue->status, ['called', 'heading_to_gate', 'loading', 'completed']) ? 'text-gray-800' : 'text-gray-400' }}">Dipanggil</h3>
                        @if($queue->called_at) <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($queue->called_at)->format('H:i') }}</p> @endif
                    </div>
                </div>

                <!-- Loading -->
                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group {{ in_array($queue->status, ['loading', 'completed']) ? 'is-active' : '' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 shrink-0 {{ in_array($queue->status, ['loading', 'completed']) ? 'bg-blue-600 border-blue-600 text-white shadow' : 'bg-white border-gray-300 text-gray-300' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-gray-100 bg-white shadow-sm ml-4 md:ml-0 md:mr-4 {{ in_array($queue->status, ['loading', 'completed']) ? '' : 'opacity-50' }}">
                        <h3 class="font-bold {{ in_array($queue->status, ['loading', 'completed']) ? 'text-gray-800' : 'text-gray-400' }}">Proses Loading</h3>
                    </div>
                </div>
                
                <!-- Completed -->
                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group {{ $queue->status == 'completed' ? 'is-active' : '' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 shrink-0 {{ $queue->status == 'completed' ? 'bg-green-500 border-green-500 text-white shadow' : 'bg-white border-gray-300 text-gray-300' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-gray-100 bg-white shadow-sm ml-4 md:ml-0 md:mr-4 {{ $queue->status == 'completed' ? '' : 'opacity-50' }}">
                        <h3 class="font-bold {{ $queue->status == 'completed' ? 'text-gray-800' : 'text-gray-400' }}">Selesai</h3>
                        @if($queue->completed_at) <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($queue->completed_at)->format('H:i') }}</p> @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
