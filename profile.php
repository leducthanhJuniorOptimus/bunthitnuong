<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: /food/login.php");
    exit();
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT username, email, phone, diachi, avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Cá Nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/food/css/style.css">
    <style>
        /* Material Design Profile Page với màu chủ đạo #ff6b35 */
        :root {
            --primary-color: #ff6b35;
            --primary-light: #ff9e6d;
            --primary-dark: #c53a0a;
            --text-primary: #212121;
            --text-secondary: #757575;
            --divider-color: #e0e0e0;
            --background: #fafafa;
            --surface: #ffffff;
            --error: #d32f2f;
            --success: #388e3c;
        }
        
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            min-height: 100vh;
            color: var(--text-primary);
        }

        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 15px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .back-btn:hover {
            color: var(--primary-dark);
            transform: translateX(-3px);
        }

        .profile-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.05),
                0 12px 28px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
        }

        .profile-card:hover {
            box-shadow: 
                0 8px 16px rgba(0, 0, 0, 0.08),
                0 16px 32px rgba(0, 0, 0, 0.12);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .profile-header h2 {
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .profile-header p {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
        }

        .avatar-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 24px;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: var(--surface);
            padding: 4px;
            transition: transform 0.3s ease;
        }

        .avatar:hover {
            transform: scale(1.05);
        }

        .profile-info {
            margin-bottom: 32px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--divider-color);
            transition: background-color 0.2s ease;
        }

        .info-item:hover {
            background-color: rgba(255, 107, 53, 0.03);
            border-radius: 8px;
            padding-left: 12px;
            padding-right: 12px;
        }

        .info-item i {
            color: var(--primary-color);
            margin-right: 16px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .info-label {
            font-weight: 500;
            color: var(--text-primary);
            width: 150px;
            flex-shrink: 0;
        }

        .info-value {
            color: var(--text-secondary);
            flex-grow: 1;
        }

        .profile-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .material-btn {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            min-height: 48px;
            box-shadow: 0 2px 6px rgba(255, 107, 53, 0.3);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .material-btn:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(255, 107, 53, 0.4);
            transform: translateY(-2px);
            color: white;
        }

        .material-btn.danger {
            background: var(--error);
            box-shadow: 0 2px 6px rgba(211, 47, 47, 0.3);
        }

        .material-btn.danger:hover {
            background: #b71c1c;
            box-shadow: 0 4px 10px rgba(211, 47, 47, 0.4);
        }

        .material-btn.secondary {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            box-shadow: none;
        }

        .material-btn.secondary:hover {
            background: rgba(255, 107, 53, 0.08);
            box-shadow: 0 2px 6px rgba(255, 107, 53, 0.2);
        }

        .ripple-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
            border-radius: inherit;
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
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

        /* Badge cho trạng thái */
        .status-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(56, 142, 60, 0.1);
            color: var(--success);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 8px;
        }

        .status-badge i {
            margin-right: 4px;
            font-size: 0.7rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-container {
                margin: 20px auto;
                padding: 0 10px;
            }

            .profile-card {
                padding: 24px;
            }

            .profile-header h2 {
                font-size: 1.5rem;
            }

            .avatar-container {
                width: 100px;
                height: 100px;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .info-label {
                width: auto;
            }

            .profile-actions {
                flex-direction: column;
            }
            
            .material-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .profile-card {
                padding: 20px;
            }
            
            .avatar-container {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="/food/index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại trang chủ
        </a>

        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-container">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="avatar">
                    <?php else: ?>
                        <div class="avatar d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-user-tie fa-3x" style="color: #ff6b35;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p>Quản lý thông tin cá nhân của bạn</p>
                <div class="status-badge">
                    <i class="fa-solid fa-circle-check"></i> Tài khoản đang hoạt động
                </div>
            </div>
            <div class="profile-info">
                <div class="info-item">
                    <i class="fa-solid fa-user"></i>
                    <span class="info-label">Tên người dùng</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fa-solid fa-phone"></i>
                    <span class="info-label">Số điện thoại</span>
                    <span class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span style="color: #9e9e9e;">Chưa cung cấp</span>'; ?></span>
                </div>
                <div class="info-item">
                    <i class="fa-solid fa-map-marker-alt"></i>
                    <span class="info-label">Địa chỉ</span>
                    <span class="info-value"><?php echo !empty($user['diachi']) ? htmlspecialchars($user['diachi']) : '<span style="color: #9e9e9e;">Chưa cung cấp</span>'; ?></span>
                </div>
            </div>
            <div class="profile-actions">
                <a href="/food/edit-profile.php" class="material-btn">
                    <div class="ripple-container"></div>
                    <i class="fa-solid fa-pen-to-square me-2"></i> Chỉnh Sửa Hồ Sơ
                </a>

                <a href="/food/logout.php" class="material-btn danger">
                    <div class="ripple-container"></div>
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng Xuất
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-ppd8umHYYP29giO3AEYYAO9X/7eHpLvVLnXAYPX4FcLzzxdDpjcD" crossorigin="anonymous"></script>
    <script>
        // Thêm hiệu ứng ripple cho các nút
        document.querySelectorAll('.material-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
                ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
                button.querySelector('.ripple-container').appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });
    </script>
</body>
</html>