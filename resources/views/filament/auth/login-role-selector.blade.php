<div
    x-data="{ role: 'admin' }"
    class="mb-6"
>
    <p class="text-xs font-medium text-center text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-widest">
        Login Sebagai
    </p>

    <div class="grid grid-cols-2 gap-3">
        {{-- Admin Card --}}
        <button
            type="button"
            @click="role = 'admin'"
            :class="role === 'admin'
                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 shadow-sm shadow-primary-500/20'
                : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20'"
            class="relative flex flex-col items-center gap-2 px-4 py-5 rounded-xl border-2 transition-all duration-200 cursor-pointer focus:outline-none"
        >
            <div
                :class="role === 'admin' ? 'bg-primary-100 dark:bg-primary-900/40' : 'bg-gray-100 dark:bg-gray-800'"
                class="w-11 h-11 rounded-xl flex items-center justify-center transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <span class="text-sm font-semibold">Admin</span>
            <span
                :class="role === 'admin' ? 'opacity-100' : 'opacity-0'"
                class="absolute top-2 right-2 w-2 h-2 rounded-full bg-primary-500 transition-opacity"
            ></span>
        </button>

        {{-- Operator Card --}}
        <button
            type="button"
            @click="role = 'operator'"
            :class="role === 'operator'
                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 shadow-sm shadow-primary-500/20'
                : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20'"
            class="relative flex flex-col items-center gap-2 px-4 py-5 rounded-xl border-2 transition-all duration-200 cursor-pointer focus:outline-none"
        >
            <div
                :class="role === 'operator' ? 'bg-primary-100 dark:bg-primary-900/40' : 'bg-gray-100 dark:bg-gray-800'"
                class="w-11 h-11 rounded-xl flex items-center justify-center transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <span class="text-sm font-semibold">Operator</span>
            <span
                :class="role === 'operator' ? 'opacity-100' : 'opacity-0'"
                class="absolute top-2 right-2 w-2 h-2 rounded-full bg-primary-500 transition-opacity"
            ></span>
        </button>
    </div>

    {{-- Dynamic description --}}
    <p
        x-show="role === 'admin'"
        x-transition.opacity
        class="mt-3 text-xs text-center text-gray-400 dark:text-gray-500"
    >
        Akses penuh: pengaturan, layanan, loket, dan semua data
    </p>
    <p
        x-show="role === 'operator'"
        x-transition.opacity
        class="mt-3 text-xs text-center text-gray-400 dark:text-gray-500"
    >
        Akses terbatas: dasbor, daftar antrian, dan pemanggilan
    </p>

    <div class="border-t border-gray-100 dark:border-white/10 mt-5"></div>
</div>
