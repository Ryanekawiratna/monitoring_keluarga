<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — WA Keuangan Bot</title>

    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> --}}

    <style>
        :root {
            --brand-500: #22c55e;
            --brand-600: #16a34a;
            --brand-700: #15803d;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: radial-gradient(circle at 20% 20%, #1e293b, #0f172a 60%);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(34, 197, 94, .25), transparent 70%);
            top: -150px;
            left: -150px;
            border-radius: 50%;
        }

        body::after {
            content: "";
            position: absolute;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(34, 197, 94, .15), transparent 70%);
            bottom: -150px;
            right: -150px;
            border-radius: 50%;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            background: #1e293b;
            border-radius: 20px;
            padding: 2.25rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .5);
            border: 1px solid rgba(255, 255, 255, .06);
            position: relative;
            z-index: 1;
        }

        .logo-box {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 20px rgba(34, 197, 94, .4);
        }

        .form-label {
            color: #cbd5e1;
            font-size: .85rem;
            font-weight: 500;
        }

        .form-control {
            background: #334155;
            border: 1px solid #475569;
            color: #fff;
            border-radius: 10px;
            padding: .65rem 1rem;
            font-size: .9rem;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            background: #334155;
            border-color: var(--brand-500);
            box-shadow: 0 0 0 .2rem rgba(34, 197, 94, .25);
            color: #fff;
        }

        .input-group-text {
            background: #334155;
            border: 1px solid #475569;
            color: #94a3b8;
            border-radius: 10px 0 0 10px;
        }

        .btn-brand {
            background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: .65rem;
            font-size: .9rem;
            transition: filter .15s ease;
        }

        .btn-brand:hover {
            filter: brightness(1.08);
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <div class="text-center mb-4">
            <div class="logo-box">
                <i class="bi bi-whatsapp text-white fs-3"></i>
            </div>
            <h1 class="text-white fs-4 fw-bold mb-1">WA Keuangan Bot</h1>
            <p class="text-secondary small mb-0">Login ke dashboard admin</p>
        </div>

        @if ($errors->has('login'))
            <div class="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first('login') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus
                        class="form-control" placeholder="admin">
                </div>
                @error('username')
                    <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" required class="form-control" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn btn-brand w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>

    </div>
</body>

</html>
