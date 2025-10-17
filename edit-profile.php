<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: /food/login.php");
    exit();
}

// Lấy thông tin người dùng hiện tại
$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT username, email, phone, diachi, avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"]);
    $diachi = trim($_POST["diachi"]);
    $avatar_path = $user['avatar'];

    // Xử lý upload avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_type = mime_content_type($_FILES['avatar']['tmp_name']);
        $file_size = $_FILES['avatar']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Chỉ hỗ trợ file JPEG, PNG hoặc GIF!";
        } elseif ($file_size > $max_size) {
            $_SESSION['error'] = "File ảnh không được vượt quá 2MB!";
        } else {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/food/uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatar_path = '/uploads/avatars/user_' . $user_id . '_' . time() . '.' . $file_ext;
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/food' . $avatar_path;

            error_log("Uploading to: $destination");
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $_SESSION['error'] = "Lỗi khi tải lên file ảnh!";
                error_log("Upload failed for: $destination");
            }
        }
    }

    // Cập nhật thông tin vào cơ sở dữ liệu
    if (!isset($_SESSION['error'])) {
        $stmt = $conn->prepare("UPDATE users SET phone = ?, diachi = ?, avatar = ? WHERE id = ?");
        $stmt->bind_param("sssi", $phone, $diachi, $avatar_path, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Cập nhật thông tin thành công!";
            error_log("Avatar updated: $avatar_path");
            header("Location: /food/profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật thông tin: " . $stmt->error;
            error_log("SQL Error: " . $stmt->error);
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Hồ Sơ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/food/css/style.css">
    <style>
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #fafafa;
            min-height: 100vh;
        }

        .edit-profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 0 15px;
        }

        .edit-profile-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.1),
                0 12px 28px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .edit-profile-card:hover {
            box-shadow: 
                0 8px 16px rgba(0, 0, 0, 0.12),
                0 16px 32px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .input-wrapper {
            position: relative;
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
        }

        .input-line {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #1976d2;
            transition: width 0.2s ease;
        }

        .input-wrapper input:focus ~ .input-line {
            width: 100%;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .material-btn {
            background: #1976d2;
            border: none;
            border-radius: 4px;
            padding: 12px 24px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            min-height: 48px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .material-btn:hover {
            background: #1565c0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
            transform: translateY(-1px);
        }

        .material-btn.secondary {
            background: #757575;
        }

        .material-btn.secondary:hover {
            background: #616161;
        }

        .ripple-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
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

        .error-message, .success-message {
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 4px;
        }

        .error-message {
            background: #d32f2f;
            color: white;
        }

        .success-message {
            background: #4caf50;
            color: white;
        }

        @media (max-width: 576px) {
            .edit-profile-container {
                margin: 20px auto;
                padding: 0 10px;
            }

            .edit-profile-card {
                padding: 24px;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="edit-profile-container">
        <a href="/food/index.php"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="edit-profile-card">
            <h2 class="text-center mb-4">Chỉnh Sửa Hồ Sơ</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <form method="POST" action="#" enctype="multipart/form-data">
                <div class="form-group mb-4">
                    <img src="/food<?php echo htmlspecialchars($user['avatar'] ?? '/image/avatar.png') . '?t=' . time(); ?>" alt="Avatar Preview" class="avatar-preview">
                    <div class="input-wrapper">
                        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                        <label for="avatar">Tải lên Avatar</label>
                        <div class="input-line"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        <label for="phone">Số điện thoại</label>
                        <div class="input-line"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="text" id="diachi" name="diachi" value="<?php echo htmlspecialchars($user['diachi'] ?? ''); ?>">
                        <label for="diachi">Địa chỉ</label>
                        <div class="input-line"></div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="material-btn">
                        <div class="ripple-container"></div>
                        Lưu
                    </button>
                    <a href="/food/profile.php" class="material-btn secondary">
                        <div class="ripple-container"></div>
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-ppd8umHYYP29giO3AEYYAO9X/7eHpLvVLnXAYPX4FcLzzxdDpjcD" crossorigin="anonymous"></script>
    <script>
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