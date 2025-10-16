<?php
session_start();
require_once "../db.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Chống spam: Chỉ cho gửi mỗi 30 giây
    if (isset($_SESSION['last_sent']) && time() - $_SESSION['last_sent'] < 30) {
        echo 'Vui lòng chờ 30 giây trước khi gửi lại.';
        exit;
    }

    $_SESSION['last_sent'] = time();

    $ten = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $noidung = htmlspecialchars($_POST['message'] ?? '');
    // Gửi mail
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; // Đảm bảo hiển thị tiếng Việt

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'leducthanh261@gmail.com';
        $mail->Password = 'yedx dvyt gbdm vshf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('leducthanh261@gmail.com', 'Sender Name');
        $mail->addAddress('leducthanh261@gmail.com', 'Your Name');

        $mail->isHTML(true);
        $mail->Subject = 'Thông tin từ khách hàng';

        $mail->Body = '
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thông tin tư vấn từ khách hàng</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5; background: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px;">
        <h2 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;">Thông tin tư vấn từ khách hàng</h2>

        <p><strong style="color: #2980b9;">Tên khách hàng:</strong> ' . $ten . '</p>
        <p><strong style="color: #2980b9;">Email khách hàng:</strong> ' . $email . '</p>
        <p><strong style="color: #2980b9;">Nội Dung Khách Hàng Gửi Tới:</strong> ' . $noidung . '</p>

        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #999; margin-top: 40px;">Email được gửi tự động từ hệ thống của bạn. Vui lòng không trả lời email này.</p>
    </div>
</body>
</html>';

        $mail->send();
        echo "<script>alert('Thông tin đã được gửi thành công!');</script>";
        header("/food/lien-he-bun-thit-nuong-bama/");
    } catch (Exception $e) {
        echo "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ Bún Thịt Nướng Bama</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="../style.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            padding: 40px 0;
        }

        .contact-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35, #ff8c5a);
            border-radius: 2px;
        }

        .section-title p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-top: 20px;
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .contact-info-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%);
            transform: translateX(10px);
        }

        .info-item:hover .info-icon {
            background: white;
            color: #ff6b35;
        }

        .info-item:hover .info-text h4,
        .info-item:hover .info-text p {
            color: white;
        }

        .info-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .info-icon i {
            font-size: 24px;
            color: white;
        }

        .info-text h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }

        .info-text p {
            color: #7f8c8d;
            margin: 0;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .contact-form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .contact-form-card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Roboto', sans-serif;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .map-container {
            margin-top: 50px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .map-container h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .map-container iframe {
            border-radius: 15px;
            width: 100%;
            height: 400px;
            border: none;
        }

        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .contact-info-card,
            .contact-form-card {
                padding: 25px;
            }

            .info-item {
                flex-direction: column;
                text-align: center;
            }

            .info-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .contact-info-card,
        .contact-form-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .contact-form-card {
            animation-delay: 0.2s;
        }
    </style>
</head>
<body>
        <?php include '../header.php'; ?>
<section class="hero-section" id="home">
        <video class="hero-video" autoplay muted loop>
            <source src="../video/lienhe.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Liên Hệ Với Chúng Tôi</h1>
            <p></p>
            <a href="#products" class="hero-btn">Liên Hệ</a>
        </div>
    </section>
    <div class="contact-section">
        <div class="section-title">
            <h2>Liên Hệ Với Chúng Tôi</h2>
            <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn</p>
        </div>

        <div class="contact-container">
            <div class="contact-info-card">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div class="info-text">
                        <h4>Địa Chỉ</h4>
                        <p>45/25/20/41B Trần Thái Tông<br>Phường Tân Sơn, TPHCM</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div class="info-text">
                        <h4>Điện Thoại</h4>
                        <p>052.8934.340</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div class="info-text">
                        <h4>Email</h4>
                        <p>support@bama.vn</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="info-text">
                        <h4>Giờ Mở Cửa</h4>
                        <p>Thứ 2 - Chủ Nhật: 16:00 - 22:00</p>
                    </div>
                </div>
            </div>

            <div class="contact-form-card">
                <h3>Gửi Tin Nhắn</h3>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="name">Họ và Tên</label>
                        <input type="text" id="name" name="name" placeholder="Nguyễn Văn A" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="example@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Nội Dung</label>
                        <textarea id="message" name="message" placeholder="Nhập nội dung tin nhắn của bạn..." required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fa-solid fa-paper-plane"></i> Gửi Tin Nhắn
                    </button>
                </form>
            </div>
        </div>

        <div class="map-container">
            <h3>Tìm Đường Đến Bama</h3>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1959.4418786185042!2d106.63174573875837!3d10.820206931856978!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175290508fd612f%3A0xe34d37162bd0708a!2zQsO6biBUaOG7i3QgTsaw4bubbmcgQmFtYQ!5e0!3m2!1svi!2s!4v1760326385544!5m2!1svi!2s" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
    <?php include '../footer.html'; ?>
    <script src="/food/cart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>