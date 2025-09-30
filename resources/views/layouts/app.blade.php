<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', 'Azure User Management System')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js" defer></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

    <!-- Theme + Custom Styles -->
    <style>
        /* ---------- Theme Variables ---------- */
        :root {
            --primary-color: #0b66ff;
            --primary-contrast: #ffffff;
            --secondary-color: #0ea5a4;
            --bg: #e9edf2;          /* softer background */
            --surface: #ffffff;
            --muted: #6b7280;
            --text: #0f172a;
            --danger: #ef4444;
            --success: #10b981;
            --radius: 10px;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 8px 30px rgba(15, 23, 42, 0.08);
            --focus-ring: 3px rgba(11, 102, 255, 0.15);
            --max-width: 1320px;
            --nav-width: 260px;
            --mobile-nav-height: 64px;
        }

        /* ---------- Reset + Defaults ---------- */
        * { box-sizing: border-box; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: "Inter", "Figtree", "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 16px;
            line-height: 1.45;
        }

        /* ---------- Layout ---------- */
        .app-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ---------- Main Content ---------- */
        .main {
            padding: 28px;
            max-width: var(--max-width);
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
        }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform .08s ease, box-shadow .12s ease;
        }
        .btn:focus-visible { outline: none; box-shadow: 0 0 0 4px var(--focus-ring); }
        .btn-primary { background: var(--primary-color); color: var(--primary-contrast); box-shadow: var(--shadow-sm); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary-color); color: var(--primary-contrast); }
        .btn-ghost { background: transparent; color: var(--text); border: 1px solid rgba(15,23,42,0.06); }

        /* ---------- Cards ---------- */
        .card-surface {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15,23,42,0.04);
        }

        /* ---------- Loading Spinner ---------- */
        .loading-spinner {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.98);
            padding: 16px 18px;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            text-align: center;
            min-width: 160px;
        }
        .loading-spinner[aria-hidden="false"] { display: block; }
        .spinner {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 4px solid rgba(15,23,42,0.06);
            border-top-color: var(--primary-color);
            margin: 0 auto 10px;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---------- Responsive ---------- */
        @media (max-width: 991px) {
            .main { padding: 18px; }
        }

        .sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        @yield('additional-styles');
    </style>

    @stack('styles')
</head>
<body>
    <div class="app-shell" role="application">
        <!-- Main -->
        <main class="main" role="main">
            <header class="page-header">
                <div class="page-title">
                    <i class="fas fa-folder-open" style="color:var(--primary-color);"></i>
                    <div>
                        <div>@yield('page-title', 'Azure User Management System')</div>
                        @hasSection('page-subtitle')
                            <div class="muted">@yield('page-subtitle')</div>
                        @endif
                    </div>
                </div>
                <div>@yield('header-actions')</div>
            </header>

            <section>
                @yield('content')
            </section>
        </main>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner" aria-hidden="true">
        <div class="spinner"></div>
        <div id="loadingText">Processing…</div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Axios defaults
            if (window.axios) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
                axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            }

            // Loading helpers
            window.showLoading = () => {
                const el = document.getElementById('loadingSpinner');
                el?.setAttribute('aria-hidden', 'false');
                el.style.display = 'block';
            };
            window.hideLoading = () => {
                const el = document.getElementById('loadingSpinner');
                el?.setAttribute('aria-hidden', 'true');
                el.style.display = 'none';
            };

            // SweetAlert helpers
            window.showAlert = (type, title, message) => {
                if (window.Swal) {
                    Swal.fire({ icon: type, title, text: message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                } else {
                    alert(title + "\n\n" + message);
                }
            };
            window.confirmAction = (title, text, callback) => {
                if (window.Swal) {
                    Swal.fire({
                        title, text, icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, proceed!'
                    }).then(result => {
                        if (result.isConfirmed && typeof callback === 'function') callback();
                    });
                } else if (confirm(title + "\n\n" + text)) {
                    callback?.();
                }
            };

            // Laravel flash + validation messages
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `<ul style="text-align:left;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`
                });
            @endif
            @if (session('success'))
                showAlert('success', 'Success', '{{ session("success") }}');
            @endif
            @if (session('error'))
                showAlert('error', 'Error', '{{ session("error") }}');
            @endif
        });
    </script>

    @stack('scripts')
    @yield('additional-scripts')
</body>
</html>