<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: ApplySessionSettings
 * Membaca nilai session_lifetime dari tabel settings di database
 * dan mengaplikasikannya ke konfigurasi session sebelum session dimulai.
 */
class ApplySessionSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $lifetime = (int) Setting::where('key', 'session_lifetime')->value('value');
            if ($lifetime > 0) {
                config(['session.lifetime' => $lifetime]);
            }
        } catch (\Throwable $e) {
            // Jika tabel belum ada (migrasi belum dijalankan), gunakan nilai default
        }

        return $next($request);
    }
}
