@props(['currentStep' => 1])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'VeloCRM') }} Installer</title>

    {{-- Google Fonts — No Vite needed --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* VeloCRM installer, standalone CSS for shared-hosting setup. */

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.12);
            --success: #059669;
            --success-bg: #ecfdf5;
            --danger: #e11d48;
            --danger-bg: #fff1f2;
            --warning: #d97706;
            --warning-bg: #fffbeb;
            --bg-primary: #f9fafb;
            --bg-secondary: #f3f4f6;
            --bg-card: #fffffe;
            --bg-input: #fffffe;
            --border: #e5e7eb;
            --border-focus: #4f46e5;
            --text: #111827;
            --text-muted: #6b7280;
            --text-dim: #9ca3af;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 1px 2px rgba(17, 24, 39, 0.05);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Quiet ambient background */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.18;
            z-index: 0;
            pointer-events: none;
        }
        body::before {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #4f46e5, transparent 70%);
            top: -200px;
            right: -100px;
        }
        body::after {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #0284c7, transparent 70%);
            bottom: -150px;
            left: -100px;
        }

        /* ---- Logo ---- */
        .installer-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }
        .installer-logo-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.25rem;
            color: #fff;
            box-shadow: none;
        }
        .installer-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .installer-logo-text span {
            color: var(--primary-light);
        }

        /* ---- Step Progress Bar ---- */
        .step-progress {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 1;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid var(--border);
            color: var(--text-dim);
            background: var(--bg-secondary);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .step-circle.active {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
            box-shadow: 0 0 0 4px var(--primary-glow);
        }
        .step-circle.completed {
            border-color: var(--success);
            background: var(--success);
            color: #fff;
        }
        .step-line {
            width: 40px;
            height: 2px;
            background: var(--border);
            transition: background 0.3s ease;
        }
        .step-line.completed {
            background: var(--success);
        }
        @media (max-width: 600px) {
            .step-line { width: 20px; }
            .step-circle { width: 30px; height: 30px; font-size: 0.7rem; }
        }

        /* ---- Card Container ---- */
        .installer-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 640px;
            padding: 2.5rem;
            position: relative;
            z-index: 1;
            animation: fadeInUp 220ms cubic-bezier(0.22, 1, 0.36, 1);
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .installer-card h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .installer-card .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        /* ---- Requirement Table ---- */
        .req-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        .req-table thead th {
            text-align: left;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-dim);
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        .req-table tbody td {
            padding: 0.7rem 1rem;
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
            font-size: 0.875rem;
        }
        .req-table tbody tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-success {
            background: var(--success-bg);
            color: var(--success);
        }
        .badge-danger {
            background: var(--danger-bg);
            color: var(--danger);
        }

        /* ---- Forms ---- */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .form-input::placeholder {
            color: var(--text-dim);
        }
        textarea.form-input,
        select.form-input {
            appearance: none;
        }
        .form-hint {
            font-size: 0.75rem;
            color: var(--text-dim);
            margin-top: 0.35rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 500px) {
            .form-row { grid-template-columns: 1fr; }
        }

        /* ---- Alerts ---- */
        .alert {
            padding: 0.875rem 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            line-height: 1.5;
        }
        .alert-success {
            background: var(--success-bg);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
        }
        .alert-danger {
            background: var(--danger-bg);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }
        .alert-warning {
            background: var(--warning-bg);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }
        .alert-icon {
            flex-shrink: 0;
            font-size: 1.1rem;
        }

        /* ---- Buttons ---- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
        }
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .btn-secondary {
            background: var(--bg-input);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover {
            background: var(--border);
            color: var(--text);
        }
        .btn-success {
            background: var(--success);
            color: #fff;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.5);
        }
        .btn-block {
            width: 100%;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* ---- List Checks ---- */
        .check-list {
            list-style: none;
            padding: 0;
            margin-bottom: 1.5rem;
        }
        .check-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            font-size: 0.9rem;
            color: var(--text-muted);
            border-bottom: 1px solid rgba(51, 65, 85, 0.3);
        }
        .check-list li:last-child {
            border-bottom: none;
        }
        .check-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            flex-shrink: 0;
        }
        .check-icon.pass {
            background: var(--success-bg);
            color: var(--success);
        }
        .check-icon.fail {
            background: var(--danger-bg);
            color: var(--danger);
        }
        .check-icon.warn {
            background: var(--warning-bg);
            color: var(--warning);
        }

        /* ---- Loading Spinner ---- */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top: 2.5px solid #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ---- Success Animation ---- */
        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--success), #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
            animation: bounceIn 0.6s ease;
        }
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* ---- Footer ---- */
        .installer-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-dim);
            position: relative;
            z-index: 1;
        }

        /* ---- Setup Progress ---- */
        .setup-steps {
            margin-bottom: 1.5rem;
        }
        .setup-step {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: var(--bg-input);
            border-radius: var(--radius-sm);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .setup-step-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
            background: var(--primary-glow);
            color: var(--primary-light);
        }
        .section-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 1.75rem 0 0.85rem;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .summary-card {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1rem;
        }
        .summary-card-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-dim);
        }
        .summary-card-value {
            margin-top: 0.35rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
        }
        @media (max-width: 640px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    {{-- Logo --}}
    <div class="installer-logo">
        <div class="installer-logo-icon">V</div>
        <div class="installer-logo-text">Velo<span>CRM</span></div>
    </div>

    <div class="step-progress">
        @for ($i = 1; $i <= 6; $i++)
            <div class="step-item">
                <div class="step-circle {{ $i < $currentStep ? 'completed' : ($i === $currentStep ? 'active' : '') }}">
                    @if ($i < $currentStep)
                        ✓
                    @else
                        {{ $i }}
                    @endif
                </div>
            </div>
            @if ($i < 6)
                <div class="step-line {{ $i < $currentStep ? 'completed' : '' }}"></div>
            @endif
        @endfor
    </div>

    {{-- Card Content --}}
    <div class="installer-card">
        @if (session('success'))
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <span class="alert-icon">✕</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{ $slot }}
    </div>

    {{-- Footer --}}
    <div class="installer-footer">
        VeloCRM v1.0.0 &middot; &copy; {{ date('Y') }} All rights reserved
    </div>
</body>
</html>
