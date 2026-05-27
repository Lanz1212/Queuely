<div class="lp-wrap">

    {{-- Brand --}}
    <div class="lp-brand">
        <div class="lp-brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
        </div>
        <span class="lp-brand-name">Queuely</span>
    </div>

    {{-- Card --}}
    <div class="lp-card">

        {{-- Title --}}
        <h1 class="lp-title">Masuk ke Queuely</h1>
        <p class="lp-sub">Sistem Antrian Muat Produk</p>

        {{-- Role Selector --}}
        <div x-data="{ role: $wire.entangle('selectedRole') }">
            <p class="lp-role-label">Login Sebagai</p>
            <div class="lp-role-grid">
                {{-- Admin --}}
                <button type="button" @click="role = 'admin'"
                    :class="role === 'admin' ? 'lp-role-btn active' : 'lp-role-btn'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span>Admin</span>
                    <div class="lp-role-dot" :style="role === 'admin' ? 'opacity:1' : 'opacity:0'"></div>
                </button>

                {{-- Operator --}}
                <button type="button" @click="role = 'operator'"
                    :class="role === 'operator' ? 'lp-role-btn active' : 'lp-role-btn'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span>Operator</span>
                    <div class="lp-role-dot" :style="role === 'operator' ? 'opacity:1' : 'opacity:0'"></div>
                </button>
            </div>
            <p class="lp-role-desc">
                <span x-show="role === 'admin'" x-transition.opacity>Akses penuh ke semua fitur sistem</span>
                <span x-show="role === 'operator'" x-transition.opacity>Dasbor, antrian, dan pemanggilan</span>
            </p>
        </div>

        <hr class="lp-divider">

        {{-- Form --}}
        <form wire:submit="authenticate">

            {{-- Email --}}
            <div class="lp-form-group">
                <label for="email" class="lp-label">Email</label>
                <div class="lp-input-wrap">
                    <input
                        wire:model="email"
                        type="email"
                        id="email"
                        autocomplete="email"
                        autofocus
                        placeholder="nama@email.com"
                        class="lp-input @error('email') error @enderror"
                    >
                </div>
                @error('email')
                    <div class="lp-error">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="lp-form-group" x-data="{ show: false }">
                <label for="password" class="lp-label">Password</label>
                <div class="lp-input-wrap">
                    <input
                        wire:model="password"
                        :type="show ? 'text' : 'password'"
                        id="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        style="padding-right: 38px;"
                        class="lp-input @error('password') error @enderror"
                    >
                    <button type="button" @click="show = !show" tabindex="-1" class="lp-input-btn">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="lp-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="lp-remember">
                <input wire:model="remember" type="checkbox" id="remember">
                <label for="remember">Ingat saya</label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="lp-btn" wire:loading.attr="disabled">
                <span wire:loading.remove>Masuk</span>
                <span wire:loading style="display:flex;align-items:center;gap:8px;">
                    <svg style="width:14px;height:14px;animation:spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>
        </form>
    </div>

    <p class="lp-footer">&copy; {{ date('Y') }} Sistem Antrian Muat Produk</p>

</div>
