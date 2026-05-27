<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Queuely</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { box-sizing: border-box; }
        body { background: #0f1117; margin: 0; }

        .lp-wrap {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: #0f1117;
        }

        .lp-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 1.75rem; }
        .lp-brand-icon {
            width: 36px; height: 36px;
            background: #2563eb;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(37,99,235,0.4);
        }
        .lp-brand-icon svg { width: 18px; height: 18px; color: #fff; }
        .lp-brand-name { font-size: 15px; font-weight: 700; color: #e6edf3; letter-spacing: -0.02em; }

        .lp-card {
            width: 100%; max-width: 400px;
            background: #161b22;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        }

        .lp-title { font-size: 16px; font-weight: 700; color: #e6edf3; text-align: center; margin: 0 0 3px; }
        .lp-sub   { font-size: 12px; color: #848d97; text-align: center; margin: 0 0 20px; }

        .lp-role-label { font-size: 10px; color: #565f6c; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; text-align: center; margin-bottom: 10px; }
        .lp-role-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
        .lp-role-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px;
            padding: 10px 8px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 9px;
            color: #565f6c;
            cursor: pointer;
            transition: all 0.15s ease;
            outline: none;
            position: relative;
        }
        .lp-role-btn:hover { border-color: rgba(255,255,255,0.2); color: #848d97; }
        .lp-role-btn.active {
            background: rgba(37,99,235,0.18);
            border-color: #3b82f6;
            color: #60a5fa;
        }
        .lp-role-btn svg  { width: 18px; height: 18px; }
        .lp-role-btn span { font-size: 11px; font-weight: 600; }
        .lp-role-dot {
            position: absolute; top: 7px; right: 7px;
            width: 6px; height: 6px;
            background: #3b82f6; border-radius: 50%;
            transition: opacity 0.15s;
        }
        .lp-role-desc { font-size: 11px; color: #565f6c; text-align: center; margin-bottom: 0; min-height: 16px; }

        .lp-divider { border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 18px 0; }

        .lp-form-group { margin-bottom: 14px; }
        .lp-label { display: block; font-size: 12px; font-weight: 500; color: #b0bac4; margin-bottom: 6px; }
        .lp-input-wrap { position: relative; }
        .lp-input {
            display: block; width: 100%;
            padding: 8px 12px;
            background: #21262d;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #e6edf3;
            font-size: 13px;
            outline: none;
            transition: border-color 0.15s;
        }
        .lp-input::placeholder { color: #4a5568; }
        .lp-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
        .lp-input.error { border-color: #f85149; }
        .lp-input-btn {
            position: absolute; right: 0; top: 0; bottom: 0;
            display: flex; align-items: center; padding: 0 10px;
            color: #4a5568; cursor: pointer; background: none; border: none; outline: none;
        }
        .lp-input-btn:hover { color: #848d97; }
        .lp-input-btn svg { width: 15px; height: 15px; }
        .lp-error { margin-top: 5px; font-size: 11px; color: #f85149; display: flex; align-items: center; gap: 4px; }
        .lp-error svg { width: 12px; height: 12px; flex-shrink: 0; }

        .lp-remember { display: flex; align-items: center; gap: 7px; margin-bottom: 16px; }
        .lp-remember input { width: 13px; height: 13px; accent-color: #3b82f6; cursor: pointer; }
        .lp-remember label { font-size: 12px; color: #565f6c; cursor: pointer; user-select: none; }

        .lp-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 10px 16px;
            background: #2563eb;
            color: #fff;
            font-size: 13px; font-weight: 600;
            border: none; border-radius: 8px;
            cursor: pointer;
            transition: background 0.15s, box-shadow 0.15s;
        }
        .lp-btn:hover:not(:disabled) { background: #1d4ed8; box-shadow: 0 4px 14px rgba(37,99,235,0.4); }
        .lp-btn:disabled { opacity: 0.65; cursor: not-allowed; }
        .lp-btn svg { width: 14px; height: 14px; }

        .lp-footer { margin-top: 20px; font-size: 11px; color: #3d444d; text-align: center; }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
