<?php
session_start();
require_once "db.php"; // File kết nối cơ sở dữ liệu
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set header JSON
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Vui lòng đăng nhập để đặt hàng!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Chống spam: Chỉ cho gửi email mỗi 30 giây
if (isset($_SESSION['last_order_time']) && time() - $_SESSION['last_order_time'] < 30) {
    http_response_code(429);
    $remaining = 30 - (time() - $_SESSION['last_order_time']);
    echo json_encode([
        'success' => false, 
        'message' => "Vui lòng chờ {$remaining} giây trước khi đặt hàng lại."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy ID người dùng từ session
$user_id = $_SESSION['user']['id'];

// Lấy thông tin người dùng từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT username, email, phone, diachi FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo json_encode([
        'success' => false, 
        'message' => 'Không tìm thấy thông tin người dùng!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra thông tin người dùng đầy đủ
if (empty($user['username']) || empty($user['email'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Thông tin tài khoản chưa đầy đủ. Vui lòng cập nhật thông tin!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy dữ liệu giỏ hàng từ yêu cầu POST
$order_data = json_decode(file_get_contents('php://input'), true);
$cart = $order_data['cart'] ?? [];

// Kiểm tra giỏ hàng
if (empty($cart)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Giỏ hàng trống!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate dữ liệu giỏ hàng
foreach ($cart as $item) {
    if (!isset($item['name']) || !isset($item['price']) || !isset($item['qty'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Dữ liệu giỏ hàng không hợp lệ!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($item['qty'] <= 0 || $item['price'] <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Số lượng hoặc giá sản phẩm không hợp lệ!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Chuẩn bị nội dung email
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8'; // Hỗ trợ tiếng Việt

try {
    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'leducthanh261@gmail.com'; // Email của bạn
    $mail->Password = 'yedx dvyt gbdm vshf'; // Mật khẩu ứng dụng
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Thiết lập người gửi và người nhận
    $mail->setFrom('leducthanh261@gmail.com', 'Bún Thịt Nướng Bama');
    $mail->addAddress('leducthanh261@gmail.com', 'Admin Bama');
    $mail->addReplyTo($user['email'], $user['username']);

    // Tiêu đề email
    $username = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
    $subject = "Đơn Hàng Từ Khách Hàng: {$username} - " . date('d/m/Y H:i');
    $mail->Subject = $subject;

    // Tính tổng tiền và tạo chi tiết đơn hàng
    $total = 0;
    $order_details = '';
    
    foreach ($cart as $item) {
        $item_name = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
        $item_qty = intval($item['qty']);
        $item_price = intval($item['price']);
        $item_total = $item_price * $item_qty;
        $total += $item_total;
        
        $order_details .= '
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">' . $item_name . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . $item_qty . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">' . number_format($item_price, 0, ',', '.') . 'đ</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-weight: 600;">' . number_format($item_total, 0, ',', '.') . 'đ</td>
            </tr>';
    }

    // Xử lý thông tin người dùng (có thể null)
    $user_phone = !empty($user['phone']) ? htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8') : '<em>Chưa cập nhật</em>';
    $user_address = !empty($user['diachi']) ? htmlspecialchars($user['diachi'], ENT_QUOTES, 'UTF-8') : '<em>Chưa cập nhật</em>';
    $user_email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');

    // Nội dung email (HTML)
    $mail->isHTML(true);
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Đơn Hàng Khách Hàng</title>
    </head>
    <body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; background: #f9f9f9; padding: 20px;">
        <div style="max-width: 700px; margin: auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); overflow: hidden;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%); padding: 30px 20px; text-align: center;">
                <h1 style="color: #fff; margin: 0; font-size: 26px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">🍜 ĐƠN HÀNG MỚI</h1>
                <p style="color: #fff; margin: 10px 0 0 0; opacity: 0.95;">Bún Thịt Nướng Bama</p>
            </div>
            
            <!-- Content -->
            <div style="padding: 30px 20px;">
                <h2 style="color: #2c3e50; border-bottom: 3px solid #ff6b35; padding-bottom: 10px; margin-bottom: 20px;">Thông Tin Khách Hàng</h2>
                
                <table style="width: 100%; margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 8px 0; width: 40%;"><strong style="color: #555;">👤 Tên khách hàng:</strong></td>
                        <td style="padding: 8px 0;">' . $username . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong style="color: #555;">📧 Email:</strong></td>
                        <td style="padding: 8px 0;">' . $user_email . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong style="color: #555;">📱 Số điện thoại:</strong></td>
                        <td style="padding: 8px 0;">' . $user_phone . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong style="color: #555;">📍 Địa chỉ:</strong></td>
                        <td style="padding: 8px 0;">' . $user_address . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong style="color: #555;">🕐 Thời gian đặt:</strong></td>
                        <td style="padding: 8px 0;">' . date('d/m/Y H:i:s') . '</td>
                    </tr>
                </table>
                
                <h2 style="color: #2c3e50; border-bottom: 3px solid #ff6b35; padding-bottom: 10px; margin-bottom: 20px;">Chi Tiết Đơn Hàng</h2>
                
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #e0e0e0;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ff6b35; color: #2c3e50;">Sản Phẩm</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ff6b35; color: #2c3e50;">SL</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ff6b35; color: #2c3e50;">Đơn Giá</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ff6b35; color: #2c3e50;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>' . $order_details . '</tbody>
                    <tfoot>
                        <tr style="background: #fff8f5;">
                            <td colspan="3" style="padding: 15px; text-align: right; font-size: 16px; font-weight: bold; color: #2c3e50;">💰 TỔNG CỘNG:</td>
                            <td style="padding: 15px; text-align: right; color: #ff6b35; font-weight: bold; font-size: 18px;">' . number_format($total, 0, ',', '.') . 'đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Footer -->
            <div style="background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e0e0e0;">
                <p style="margin: 0; font-size: 13px; color: #666;">Email được gửi tự động từ hệ thống <strong>Bún Thịt Nướng Bama</strong></p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #999;">Vui lòng liên hệ ngay với khách hàng để xác nhận đơn hàng</p>
            </div>
        </div>
    </body>
    </html>';

    // Gửi email
    $mail->send();
    
    // Cập nhật thời gian đặt hàng cuối
    $_SESSION['last_order_time'] = time();
    
    // Trả về kết quả thành công
    echo json_encode([
        'success' => true, 
        'message' => 'Đơn hàng đã được gửi thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.',
        'order_info' => [
            'total' => $total,
            'items_count' => count($cart),
            'order_time' => date('d/m/Y H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi khi gửi đơn hàng: ' . $mail->ErrorInfo
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>