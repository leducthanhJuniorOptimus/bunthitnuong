<?php 
session_start();
require_once "db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bún Thịt Nướng BaMa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto|Work+Sans:400,600" rel="stylesheet">
</head>
<body>
        <?php include 'header.php'; ?>

    <main>
        <section class="hero-section" id="home">
        <video class="hero-video" autoplay muted loop>
            <source src="image/videocoking.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Bún Thịt Nướng BaMa</h1>
            <p>Hương vị truyền thống - Đẳng cấp hiện đại</p>
            <a href="#menu" class="hero-btn">Khám Phá Thực Đơn</a>
        </div>
    </section>

        <section class="menu-section container py-5">
            <h2 class="text-center mb-4">Thực Đơn Hôm Nay</h2>
            <div class="row justify-content-center g-4">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card menu-card">
                        <div class="card-img-wrapper">
                            <img src="image/anhsanphambunthitnuong.jpg" class="card-img-top" alt="Bún Thịt Nướng BaMa">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Bún Thịt Nướng Nhiều Thịt</h5>
                            <p class="card-text">38.000đ</p>
                            <button class="btn btn-danger">Đặt Ngay</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card menu-card">
                        <div class="card-img-wrapper">
                            <img src="image/bunthitnuongbama1.jpg" class="card-img-top" alt="Bún Chả Hà Nội">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Bún Thịt Nướng Đặc Biệt</h5>
                            <p class="card-text">42.000đ</p>
                            <span class="special-badge">Special</span>
                            <button class="btn btn-danger">Đặt Ngay</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card menu-card">
                        <div class="card-img-wrapper">
                            <img src="image/bunthitnuongbama.jpg" class="card-img-top" alt="Bún Chả Hà Nội">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">Bún Thịt Nướng Chả Giò</h5>
                            <p class="card-text">42.000đ</p>
                            <button class="btn btn-danger">Đặt Ngay</button>
                        </div>
                    </div>
                </div>
                
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section container py-5">
            <h2 class="text-center mb-5">Giới Thiệu Về Bún Thịt Nướng BaMa</h2>
            <div class="about-content row align-items-center">
                <div class="col-lg-6 col-md-12">
                    <div class="about-image">
                        <img src="image/thanganh.png" alt="Bún Thịt Nướng BaMa" class="img-fluid rounded shadow">
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <h3>Từ Gốc Bếp Nhỏ</h3>
                    <p>Bún Thịt Nướng BaMa bắt đầu từ niềm đam mê mang đến hương vị truyền thống Việt Nam. Chúng tôi tự hào sử dụng nguyên liệu tươi ngon, công thức gia truyền để tạo nên những món ăn đậm đà, đậm chất quê hương.</p>
                    <a href="#" class="btn btn-about">Xem Thêm Về Chúng Tôi</a>
                </div>
            </div>
        </section>

        <!-- Combo Section -->
        <section class="combo-section container-fluid py-5">
            <h2 class="text-center mb-5">Combo Của Quán</h2>
            <div class="combo-banner">
                <img src="image/combobunthitnuong.png" alt="Combo Bún Thịt Nướng" class="img-fluid w-100">
                <div class="combo-overlay">
                    <h3>Combo Đặc Biệt</h3>
                    <p>Thưởng thức combo độc quyền với giá ưu đãi, mang đến trải nghiệm ẩm thực trọn vẹn!</p>
                    <a href="#" class="btn btn-combo">Khám Phá Ngay</a>
                </div>
            </div>
            <div class="container mt-4">
                <div class="row justify-content-center g-4">
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="card combo-card">
                            <div class="wrapper">
                                <img src="image/bunthitnguongbama.jpg" class="cover-image" alt="Bún Thịt Nướng Đặc Biệt">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Bún Thịt Nướng Đặc Biệt</h5>
                                <p class="card-text">45.000đ</p>
                                <button class="btn btn-danger">Đặt Ngay</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="card combo-card">
                            <div class="wrapper">
                                <img src="https://images.unsplash.com/photo-1569562211093-4ed0d0758f12?w=400" class="cover-image" alt="Combo Gia Đình">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Combo Gia Đình</h5>
                                <p class="card-text">120.000đ</p>
                                <button class="btn btn-danger">Đặt Ngay</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="card combo-card">
                            <div class="wrapper">
                                <img src="https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=400" class="cover-image" alt="Combo Tiết Kiệm">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">Combo Tiết Kiệm</h5>
                                <p class="card-text">80.000đ</p>
                                <button class="btn btn-danger">Đặt Ngay</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Giỏ Hàng -->
    </main>
    <div class="notifications">
    <div class="toast success">
        <i class="fa-solid fa-circle-check"></i>
        <div class="content">
            <div class="title">Thành công</div>
            <span>Đã thêm vào giỏ hàng!</span>
        </div>
        <i class="fa-solid fa-xmark" onclick="(this.parentElement).remove()"></i>
    </div>
</div>
    <?php include 'footer.html'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="cart.js"></script>
</body>
</html>