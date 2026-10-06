<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Rumanesia</title>

    
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('assets/images/favicon-32x32.png')); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('assets/images/favicon-16x16.png')); ?>">
    <link rel="shortcut icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('assets/images/logo-icon-rounded.png')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/bootstrap.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/bootstrap.icon.css')); ?>">


    <script src="<?php echo e(asset('assets/js/jquery.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/jquery-ui.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/jquery-ui.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/bootbox.js')); ?>"></script>

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/sweetalert2.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/jquery-ui.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/jquery-ui.css')); ?>">

    <script src="<?php echo e(asset('assets/js/sweetalert2.all.min.js')); ?>"></script>


    <link rel="stylesheet" href="<?php echo e(asset('assets/css/inter.css')); ?>">
    <script src="<?php echo e(asset('assets/js/helper.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/js/users.js')); ?>"></script>

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

        /* CSS Tambahan untuk tombol mata di kanan */
        .input-group-text-right {
            background: #334155;
            border: 1px solid #475569;
            color: #94a3b8;
            border-radius: 0 10px 10px 0;
            /* Melengkung di kanan */
            cursor: pointer;
            transition: color 0.2s;
        }

        .input-group-text-right:hover {
            color: #fff;
        }

        /* Mematikan lengkungan border input bagian kanan agar menyatu */
        .input-password-group .form-control {
            border-radius: 0;
        }

        .auth-link {
            color: var(--brand-500);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .auth-link:hover {
            color: var(--brand-600);
        }
    </style>
</head>

<body>

    <div class="login-card">

        <div class="text-center mb-4">
            <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Rumanesia" class="img-fluid mb-2" style="max-height: 56px; width: auto;">
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 small py-2 mb-3" style="color: #22c55e;"
                role="alert">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 small py-2 mb-3" style="color: #ef4444;"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
                        class="form-control" placeholder="admin@email.com">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" required class="form-control"
                        placeholder="••••••••">
                    <span class="input-group-text-right px-3 d-flex align-items-center" id="togglePassword"
                        onclick="users.showPassword(this)">
                        <i class="bi bi-eye-slash-fill" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-brand w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>

            <div class="text-center small text-secondary" style="margin-top:20px;">
                Belum punya akun? <a href="<?php echo e(route('register')); ?>" class="auth-link">Register di sini</a>
            </div>
        </form>

    </div>
</body>

</html>
<?php /**PATH C:\Users\user\Downloads\rumanesia\resources\views/auth/login.blade.php ENDPATH**/ ?>