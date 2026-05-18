<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - E-OFFICE PDAM</title>
    
    <link rel="icon" href="data:;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABmJLR0T///////8JWPfcAAAACXBIWXMAAABIAAAASABGyWs+AAAAF0lEQVRIx2NgGAWjYBSMglEwCkbBSAcACBAAAeaR9cIAAAAASUVORK5CYII=">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    ```

Setelah Anda menyisipkan baris `link rel="icon"` tersebut, lakukan commit dan push ke Railway seperti biasa. Konsol browser Anda dipastikan akan langsung bersih total dan status aplikasi Anda sudah aman untuk digunakan!
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .left-side {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .left-side::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -50px;
            right: -50px;
        }
        
        .left-side::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -30px;
            left: -30px;
        }
        
        .brand-logo {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }
        
        .brand-logo i {
            font-size: 3rem;
        }
        
        .left-side h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .left-side p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .features-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }
        
        .feature-item i {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .right-side {
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }
        
        .login-header {
            margin-bottom: 2.5rem;
        }
        
        .login-header h2 {
            color: #1a202c;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #718096;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .input-group-custom {
            position: relative;
        }
        
        .input-group-custom i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f7fafc;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4e73df;
            background: white;
            box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-check-input {
            width: 1.1em;
            height: 1.1em;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            accent-color: #4e73df;
        }
        
        .form-check-label {
            color: #718096;
            cursor: pointer;
            user-select: none;
        }
        
        .forgot-link {
            color: #4e73df;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .forgot-link:hover {
            color: #224abe;
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            color: #a0aec0;
            font-size: 0.85rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            padding: 0 1rem;
        }
        
        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-social {
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 10px;
            font-weight: 600;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn-social:hover {
            border-color: #4e73df;
            background: #f7fafc;
            transform: translateY(-2px);
        }
        
        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: #a0aec0;
            font-size: 0.85rem;
        }
        
        .invalid-feedback {
            display: none;
            color: #e53e3e;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            padding-left: 2.75rem;
        }
        
        .form-control.is-invalid {
            border-color: #e53e3e;
        }
        
        .form-control.is-invalid + .invalid-feedback {
            display: block;
        }
        
        @media (max-width: 991px) {
            .login-box {
                grid-template-columns: 1fr;
                max-width: 450px;
            }
            
            .left-side {
                display: none;
            }
            
            .right-side {
                padding: 3rem 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 1rem;
            }
            
            .right-side {
                padding: 2rem 1.5rem;
            }
            
            .social-login {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="left-side d-none d-lg-flex">
                <div>
                    <div class="brand-logo">
                        <i class="fas fa-tint"></i>
                    </div>
                    <h1>E-OFFICE PDAM</h1>
                    <p>Sistem Manajemen Kantor Digital Terintegrasi untuk meningkatkan efisiensi dan produktivitas kerja.</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Aman & Terpercaya</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Akses Cepat & Mudah</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Terintegrasi Penuh</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="right-side">
                <div class="login-header">
                    <h2>Selamat Datang</h2>
                    <p>Silakan masuk untuk melanjutkan</p>
                </div>
                
                <form method="POST" action="{{ secure_url('/login') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-group-custom">
                            <i class="fas fa-user"></i>
                            <input type="text" 
                                   id="username" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   name="username" 
                                   value="{{ old('username') }}"
                                   placeholder="Masukkan username Anda"
                                   required autofocus>
                        </div>
                        @error('username')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock"></i>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" 
                                   placeholder="••••••••"
                                   required 
                                   autocomplete="current-password">
                        </div>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="remember" id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Ingat Saya
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">
                                Lupa Password?
                            </a>
                        @endif
                    </div>
                    
                    <button type="submit" class="btn-login">
                        Masuk
                    </button>
                </form>
                
                <div class="divider">
                    <span>atau masuk dengan</span>
                </div>
                
                <div class="social-login">
                    <button class="btn-social">
                        <i class="fab fa-google text-danger"></i> Google
                    </button>
                    <button class="btn-social">
                        <i class="fab fa-microsoft text-primary"></i> Microsoft
                    </button>
                </div>
                
                <div class="footer-text">
                    &copy; {{ date('Y') }} PDAM. All rights reserved.
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>