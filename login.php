<?php
session_start();
require_once "db.php";

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['user'])) {
    header("Location: /food/index.php");
    exit();
}

// Tạo CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ tên đăng nhập và mật khẩu!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION["user"] = [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'id' => $user['id']
                ];
                header("Location: /food/index.php");
                exit();
            } else {
                $_SESSION['error'] = "Sai tên đăng nhập hoặc mật khẩu!";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $_SESSION['error'] = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Bún Thịt Nướng Bama</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #fafafa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.5;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 48px 40px 36px;
            box-shadow: 
                0 2px 4px rgba(0, 0, 0, 0.1),
                0 8px 16px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
        }

        .login-card:hover {
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.12),
                0 12px 28px rgba(0, 0, 0, 0.15);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .material-logo {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
        }

        .logo-layers {
            position: relative;
            width: 56px;
            height: 56px;
        }

        .layer {
            position: absolute;
            border-radius: 50%;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .layer-1 {
            width: 56px;
            height: 56px;
            background: #1976d2;
            box-shadow: 0 2px 4px rgba(25, 118, 210, 0.3);
        }

        .layer-2 {
            width: 40px;
            height: 40px;
            top: 8px;
            left: 8px;
            background: #2196f3;
            box-shadow: 0 1px 3px rgba(33, 150, 243, 0.3);
        }

        .layer-3 {
            width: 24px;
            height: 24px;
            top: 16px;
            left: 16px;
            background: #64b5f6;
            box-shadow: 0 1px 2px rgba(100, 181, 246, 0.3);
        }

        .material-logo:hover .layer-1 {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(25, 118, 210, 0.4);
        }

        .material-logo:hover .layer-2 {
            transform: scale(1.15);
            box-shadow: 0 2px 6px rgba(33, 150, 243, 0.4);
        }

        .material-logo:hover .layer-3 {
            transform: scale(1.2);
            box-shadow: 0 2px 4px rgba(100, 181, 246, 0.4);
        }

        .login-header h2 {
            color: #212121;
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 8px;
            letter-spacing: 0.15px;
        }

        .login-header p {
            color: #757575;
            font-size: 14px;
            font-weight: 400;
        }

        /* Material Form Groups */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .input-wrapper input {
            background: transparent;
            border: none;
            border-bottom: 2px solid #e0e0e0;
            border-radius: 0;
            padding: 16px 0 8px;
            color: #212121;
            font-size: 16px;
            font-weight: 400;
            transition: all 0.2s ease;
            width: 100%;
            outline: none;
            position: relative;
            z-index: 3;
        }

        .input-wrapper input:focus {
            border-bottom-color: #1976d2;
        }

        .input-wrapper input:focus + label,
        .input-wrapper input:valid + label {
            transform: translateY(-24px) scale(0.75);
            color: #1976d2;
            font-weight: 500;
        }

        .input-wrapper label {
            position: absolute;
            left: 0;
            top: 16px;
            color: #757575;
            font-size: 16px;
            font-weight: 400;
            transition: all 0.2s ease;
            pointer-events: none;
            transform-origin: left top;
            z-index: 4;
        }

        .input-line {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #1976d2;
            transition: width 0.2s ease;
            z-index: 5;
        }

        .input-wrapper input:focus ~ .input-line {
            width: 100%;
        }

        /* Material Ripple Effects */
        .ripple-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(25, 118, 210, 0.2);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 56px;
        }

        .password-toggle {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: #757575;
            transition: all 0.2s ease;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            z-index: 10;
        }

        .password-toggle:hover {
            background: rgba(0, 0, 0, 0.04);
            color: #1976d2;
        }

        .toggle-ripple {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            overflow: hidden;
            z-index: 1;
        }

        .toggle-icon {
            display: block;
            width: 20px;
            height: 20px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23757575' stroke-width='1.5'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/%3e%3c/svg%3e");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transition: background-image 0.2s ease;
            position: relative;
            z-index: 2;
            flex-shrink: 0;
        }

        .password-toggle:hover .toggle-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231976d2' stroke-width='1.5'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/%3e%3c/svg%3e");
        }

        .toggle-icon.show-password {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23757575' stroke-width='1.5'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88'/%3e%3c/svg%3e");
        }

        .password-toggle:hover .toggle-icon.show-password {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231976d2' stroke-width='1.5'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88'/%3e%3c/svg%3e");
        }

        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .checkbox-wrapper input[type="checkbox"] {
            display: none;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
            color: #424242;
            font-size: 14px;
            font-weight: 400;
        }

        .checkbox-material {
            position: relative;
            width: 18px;
            height: 18px;
            border: 2px solid #757575;
            border-radius: 2px;
            transition: all 0.2s ease;
            overflow: hidden;
            flex-shrink: 0;
        }

        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-label .checkbox-material {
            background: #1976d2;
            border-color: #1976d2;
        }

        .checkbox-ripple {
            position: absolute;
            top: -12px;
            left: -12px;
            right: -12px;
            bottom: -12px;
            border-radius: 50%;
            overflow: hidden;
        }

        .checkbox-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            fill: white;
            opacity: 0;
            transition: all 0.2s ease;
        }

        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-label .checkbox-icon {
            opacity: 1;
        }

        .checkbox-path {
            stroke-dasharray: 16;
            stroke-dashoffset: 16;
            transition: stroke-dashoffset 0.3s ease;
        }

        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-label .checkbox-path {
            stroke-dashoffset: 0;
        }

        .forgot-password {
            color: #1976d2;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.25px;
        }

        .forgot-password:hover {
            color: #0d47a1;
        }

        /* Material Button */
        .material-btn {
            width: 100%;
            background: #1976d2;
            border: none;
            border-radius: 4px;
            padding: 12px 24px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            min-height: 48px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .material-btn:hover {
            background: #1565c0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
            transform: translateY(-1px);
        }

        .material-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-ripple {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
        }

        .btn-text {
            position: relative;
            z-index: 1;
            transition: opacity 0.2s ease;
        }

        .btn-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .loader-circle {
            width: 20px;
            height: 20px;
            animation: rotate 2s linear infinite;
        }

        .loader-path {
            stroke-dasharray: 90, 150;
            stroke-dashoffset: 0;
            stroke-linecap: round;
            animation: dash 1.5s ease-in-out infinite;
        }

        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }

        @keyframes dash {
            0% { stroke-dasharray: 1, 150; stroke-dashoffset: 0; }
            50% { stroke-dasharray: 90, 150; stroke-dashoffset: -35; }
            100% { stroke-dasharray: 90, 150; stroke-dashoffset: -124; }
        }

        .material-btn.loading .btn-text {
            opacity: 0;
        }

        .material-btn.loading .btn-loader {
            opacity: 1;
        }

        /* Divider */
        .divider {
            text-align: center;
            margin: 24px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
            transform: translateY(-50%);
        }

        .divider span {
            background: #ffffff;
            color: #757575;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        /* Social Login */
        .social-login {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .social-btn {
            background: #ffffff;
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 12px 16px;
            color: #3c4043;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
            min-height: 48px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .social-btn:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }

        .social-ripple {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
        }

        .social-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .social-icon svg {
            width: 100%;
            height: 100%;
        }

        /* Signup Link */
        .signup-link {
            text-align: center;
        }

        .signup-link p {
            color: #757575;
            font-size: 14px;
            font-weight: 400;
        }

        .create-account {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .create-account:hover {
            color: #0d47a1;
        }

        /* Error States */
        .error-message {
            color: #d32f2f;
            font-size: 12px;
            font-weight: 400;
            margin-top: 6px;
            opacity: 0;
            transform: translateY(-8px);
            transition: all 0.2s ease;
        }

        .error-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .form-group.error .input-wrapper input {
            border-bottom-color: #d32f2f;
            color: #d32f2f;
        }

        .form-group.error .input-wrapper label {
            color: #d32f2f;
        }

        .form-group.error .input-line {
            background: #d32f2f;
        }

        /* Success Message */
        .success-message {
            display: none;
            text-align: center;
            padding: 32px 20px;
            opacity: 0;
            transform: translateY(16px);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .success-message.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .success-elevation {
            background: #ffffff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .success-icon {
            width: 48px;
            height: 48px;
            background: #4caf50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
        }

        .success-icon svg {
            width: 24px;
            height: 24px;
            fill: white;
        }

        .success-message h3 {
            color: #212121;
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .success-message p {
            color: #757575;
            font-size: 14px;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }
            
            .login-card {
                padding: 32px 24px 24px;
            }
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .logo-layers {
                width: 48px;
                height: 48px;
            }
            
            .layer-1 {
                width: 48px;
                height: 48px;
            }
            
            .layer-2 {
                width: 32px;
                height: 32px;
                top: 8px;
                left: 8px;
            }
            
            .layer-3 {
                width: 16px;
                height: 16px;
                top: 16px;
                left: 16px;
            }
        }
        .back-btn {
    display: inline-flex
;
    align-items: center;
    color: var(--primary-color);
    text-decoration: none;
    margin-bottom: 20px;
    font-weight: 500;
    transition: all 0.2s 
ease;
}
    </style>
</head>
<body>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <div class="login-container">
 <a href="/food/index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại trang chủ
        </a>        <div class="login-card">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message show" style="text-align: center; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <div class="login-header">
                <div class="material-logo">
                    <div class="logo-layers">
                        <div class="layer layer-1"></div>
                        <div class="layer layer-2"></div>
                        <div class="layer layer-3"></div>
                    </div>
                </div>
                <h2>Sign in</h2>
                <p>to continue to your account</p>
            </div>
            
            <form class="login-form" id="loginForm" method="POST" action="#" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" required autocomplete="username">
                        <label for="username">Username</label>
                        <div class="input-line"></div>
                        <div class="ripple-container"></div>
                    </div>
                    <span class="error-message" id="usernameError"></span>
                </div>

                <div class="form-group">
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <label for="password">Password</label>
                        <div class="input-line"></div>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <div class="toggle-ripple"></div>
                            <span class="toggle-icon"></span>
                        </button>
                        <div class="ripple-container"></div>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" class="checkbox-label">
                            <div class="checkbox-material">
                                <div class="checkbox-ripple"></div>
                                <svg class="checkbox-icon" viewBox="0 0 24 24">
                                    <path class="checkbox-path" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                </svg>
                            </div>
                            Keep me signed in
                        </label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn material-btn">
                    <div class="btn-ripple"></div>
                    <span class="btn-text">SIGN IN</span>
                    <div class="btn-loader">
                        <svg class="loader-circle" viewBox="0 0 50 50">
                            <circle class="loader-path" cx="25" cy="25" r="12" fill="none" stroke="currentColor" stroke-width="3"/>
                        </svg>
                    </div>
                </button>
            </form>

            <div class="divider">
                <span>or</span>
            </div>

            <div class="social-login">
                <button type="button" class="social-btn google-material">
                    <div class="social-ripple"></div>
                    <div class="social-icon google-icon">
                        <svg viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </div>
                    <span>Continue with Google</span>
                </button>
                
                <button type="button" class="social-btn facebook-material">
                    <div class="social-ripple"></div>
                    <div class="social-icon facebook-icon">
                        <svg viewBox="0 0 24 24">
                            <path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <span>Continue with Facebook</span>
                </button>
            </div>

            <div class="signup-link">
                <p>Don't have an account? <a href="/food/resiger.php" class="create-account">Create account</a></p>
            </div>

            <div class="success-message" id="successMessage">
                <div class="success-elevation">
                    <div class="success-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    </div>
                    <h3>Welcome back!</h3>
                    <p>Signing you in...</p>
                </div>
            </div>
        </div>
    </div>
    <script>
class BasicLoginForm {
  constructor() {
    this.form = document.getElementById('loginForm');
    this.usernameInput = document.getElementById('username');
    this.passwordInput = document.getElementById('password');
    this.passwordToggle = document.getElementById('passwordToggle');
    this.successMessage = document.getElementById('successMessage');
    
    this.init();
  }
  
  init() {
    // FormUtils.addSharedAnimations(); // Bỏ nếu không cần
    // FormUtils.setupFloatingLabels(this.form); // Bỏ nếu không cần
    this.setupPasswordToggle();
    
    this.form.addEventListener('submit', this.handleSubmit.bind(this));
    this.usernameInput.addEventListener('input', () => this.validateField('username'));
    this.passwordInput.addEventListener('input', () => this.validateField('password'));
    
    // FormUtils.addEntranceAnimation(this.form.closest('.login-card'), 100); // Bỏ nếu không cần
  }
  
  setupPasswordToggle() {
    this.passwordToggle.addEventListener('click', () => {
      const isPassword = this.passwordInput.type === 'password';
      this.passwordInput.type = isPassword ? 'text' : 'password';
      this.passwordToggle.querySelector('.toggle-icon').classList.toggle('show-password', isPassword);
    });
  }
  
  validateField(fieldName) {
    const input = document.getElementById(fieldName);
    const value = input.value.trim();
    let validation;
    
    // FormUtils.clearError(fieldName); // Thay bằng logic dưới
    const errorElement = document.getElementById(`${fieldName}Error`);
    errorElement.classList.remove('show');
    input.closest('.form-group').classList.remove('error');
    
    if (fieldName === 'username') {
      validation = value.length >= 3 ? { isValid: true } : { isValid: false, message: 'Username phải có ít nhất 3 ký tự' };
    } else if (fieldName === 'password') {
      validation = value.length >= 6 ? { isValid: true } : { isValid: false, message: 'Mật khẩu phải có ít nhất 6 ký tự' };
    }
    
    if (!validation.isValid && value !== '') {
      errorElement.textContent = validation.message;
      errorElement.classList.add('show');
      input.closest('.form-group').classList.add('error');
      return false;
    } else if (validation.isValid) {
      return true;
    }
    
    return true;
  }
  
  async handleSubmit(e) {
    e.preventDefault();
    
    const username = this.usernameInput.value.trim();
    const password = this.passwordInput.value.trim();
    
    const usernameValid = this.validateField('username');
    const passwordValid = this.validateField('password');
    
    if (!usernameValid || !passwordValid) {
      alert('Vui lòng sửa các lỗi bên dưới');
      return;
    }
    
    const submitBtn = this.form.querySelector('.login-btn');
    submitBtn.classList.add('loading');
    
    try {
      // Giả lập đăng nhập
      await new Promise(resolve => setTimeout(resolve, 1000));
      this.form.submit();
    } catch (error) {
      alert(error.message);
    } finally {
      submitBtn.classList.remove('loading');
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new BasicLoginForm();
});
</script>
    <script src="/food/shared/js/form-utils.js"></script>
    <script src="/food/js/script.js"></script>
</body>
</html>