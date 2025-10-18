<?php
session_start();
require_once "db.php";

// Truy vấn sản phẩm
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Kiểm tra lỗi truy vấn
if (!$result) {
    echo "<p style='text-align: center; color: red;'>Lỗi khi tải sản phẩm: " . $conn->error . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bún Thịt Nướng BaMa - Hương vị truyền thống Việt Nam với nguyên liệu tươi ngon và công thức gia truyền.">
    <meta name="keywords" content="bún thịt nướng, BaMa, món ăn Việt Nam, thực đơn, quán ăn">
    <meta property="og:title" content="Bún Thịt Nướng BaMa">
    <meta property="og:description" content="Thưởng thức bún thịt nướng đậm đà hương vị truyền thống tại BaMa.">
    <meta property="og:image" content="/image/logo.jpg">
    <meta property="og:url" content="https://yourwebsite.com">
    <title>Bún Thịt Nướng BaMa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .products-section {
            padding: 4rem 0;
            position: relative;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .section-title {
            font-size: 3rem;
            font-weight: 800;
            color: #2d3748;
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35, #f7931e);
            border-radius: 10px;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin: 3rem 0;
            animation: fadeIn 1s ease-out 0.3s both;
        }

        .filter-btn {
            padding: 12px 30px;
            border: 2px solid #ff6b35;
            background: white;
            color: #ff6b35;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.1);
            position: relative;
            overflow: hidden;
        }

        .filter-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: linear-gradient(90deg, #ff6b35, #f7931e);
            transition: all 0.5s ease;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        .filter-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .filter-btn:hover, .filter-btn:focus {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.3);
            outline: 2px solid #ff6b35;
            outline-offset: 2px;
        }

        .filter-btn.active {
            background: linear-gradient(90deg, #ff6b35, #f7931e);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        .filter-btn span {
            position: relative;
            z-index: 1;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
            justify-content: center;
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            animation: fadeInUp 0.6s ease-out both;
            min-height: 450px;
            display: flex;
            flex-direction: column;
        }

        .menu-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.2);
        }

        .card-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 220px;
        }

        .card-img-top {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .menu-card:hover .card-img-top {
            transform: scale(1.15) rotate(2deg);
        }

        .card-img-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-card:hover .card-img-wrapper::after {
            opacity: 1;
        }

        .special-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
            animation: pulse 2s infinite;
        }

        .card-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .menu-card:hover .card-title {
            color: #ff6b35;
        }

        .card-text {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ff6b35;
            margin-bottom: 1rem;
        }

        .btn.btn-danger {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #ff6b35, #f7931e);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn.btn-danger::before {
            content: '🛒';
            position: absolute;
            left: -30px;
            transition: all 0.3s ease;
        }

        .btn.btn-danger:hover::before, .btn.btn-danger:focus::before {
            left: 15px;
        }

        .btn.btn-danger:hover, .btn.btn-danger:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            padding-left: 40px;
            outline: 2px solid #ff6b35;
            outline-offset: 2px;
        }

        .btn.btn-danger:active {
            transform: scale(0.95);
        }

        .product-item.hidden {
            display: none;
        }

        .loading-spinner {
            text-align: center;
            margin: 2rem 0;
            display: none;
        }

        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 15px;
            background: linear-gradient(90deg, #ff6b35, #f7931e);
            color: white;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            transition: all 0.3s ease;
        }

        .back-to-top:hover, .back-to-top:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            outline: 2px solid #ff6b35;
            outline-offset: 2px;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .product-item:nth-child(1) { animation-delay: 0.1s; }
        .product-item:nth-child(2) { animation-delay: 0.2s; }
        .product-item:nth-child(3) { animation-delay: 0.3s; }
        .product-item:nth-child(4) { animation-delay: 0.4s; }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }

            .filter-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 1.5rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <video class="hero-video" autoplay muted loop preload="auto" poster="image/poster.jpg">
            <source src="image/videocoking.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Bún Thịt Nướng BaMa</h1>
            <p>Hương vị truyền thống - Đẳng cấp hiện đại</p>
            <a href="#products" class="hero-btn">Khám Phá Thực Đơn</a>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 0 2rem;">
            <div class="section-header">
                <h2 class="section-title">Thực Đơn Hôm Nay</h2>
            </div>

            <!-- Search Bar -->
            <div class="search-bar" style="text-align: center; margin: 2rem 0;">
                <input type="text" id="searchInput" class="form-control w-50 mx-auto" placeholder="Tìm kiếm món ăn..." aria-label="Tìm kiếm sản phẩm">
            </div>

            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all" role="button" aria-label="Hiển thị tất cả sản phẩm">
                    <span>🍜 Tất Cả</span>
                </button>
                <button class="filter-btn" data-filter="bun-thit-nuong" role="button" aria-label="Hiển thị món bún thịt nướng">
                    <span>🥢 Bún Thịt Nướng</span>
                </button>
                <button class="filter-btn" data-filter="combo" role="button" aria-label="Hiển thị món combo">
                    <span>🍱 Combo</span>
                </button>
                <button class="filter-btn" data-filter="com-chien" role="button" aria-label="Hiển thị món cơm chiên">
                    <span>🍱 Cơm Chiên</span>
                </button>
                <button class="filter-btn" data-filter="dothem" role="button" aria-label="Hiển thị món đồ thêm">
                    <span>🍱 Đồ Thêm</span>
                </button>
                <button class="filter-btn" data-filter="nuocngot" role="button" aria-label="Hiển thị món nước ngọt">
                    <span>🍱 Nước Ngọt</span>
                </button>
            </div>

            <!-- Loading Spinner -->
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="menu-card product-item" data-category="<?php echo htmlspecialchars($row['category']); ?>">
                            <div class="card-img-wrapper">
                                <img src="/food/uploads/product/<?php echo basename(htmlspecialchars($row['image'])); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="card-img-top" loading="lazy">
                                <?php if ($row['is_special']): ?>
                                    <span class="special-badge">
                                        <?php echo $row['category'] === 'bun-dac-biet' ? '✨ Đặc Biệt' : ($row['category'] === 'bun-thit-nuong' ? '🔥 Phổ Biến' : '🆕 Mới'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text"><?php echo number_format($row['price']); ?> VNĐ</p>
                                <button class="btn btn-danger" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-price="<?php echo $row['price']; ?>" aria-label="Thêm <?php echo htmlspecialchars($row['name']); ?> vào giỏ hàng">Thêm vào giỏ</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center;">Không có sản phẩm nào trong thực đơn.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section container py-5">
        <h2 class="text-center mb-5">Giới Thiệu Về Bún Thịt Nướng BaMa</h2>
        <div class="about-content row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="about-image">
                    <img src="image/thanganh.png" alt="Món ăn tại Bún Thịt Nướng BaMa" class="img-fluid rounded shadow" loading="lazy">
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <h3>Từ Gốc Bếp Nhỏ</h3>
                <p>Bún Thịt Nướng BaMa bắt đầu từ niềm đam mê mang đến hương vị truyền thống Việt Nam. Chúng tôi tự hào sử dụng nguyên liệu tươi ngon, công thức gia truyền để tạo nên những món ăn đậm đà, đậm chất quê hương.</p>
                <a href="gioi-thieu-ve-bun-thit-nuong-bama" class="btn btn-about" aria-label="Xem thêm về Bún Thịt Nướng BaMa">Xem Thêm Về Chúng Tôi</a>
            </div>
        </div>
    </section>

    <!-- Notifications -->
    <div class="notifications">
        <div class="toast success">
            <i class="fa-solid fa-circle-check"></i>
            <div class="content">
                <div class="title">Thành công</div>
                <span>Đã thêm vào giỏ hàng!</span>
            </div>
            <i class="fa-solid fa-xmark" onclick="(this.parentElement).remove()" aria-label="Đóng thông báo"></i>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" aria-label="Quay lại đầu trang">
        <i class="fas fa-arrow-up"></i>
    </button>

    <?php include 'footer.html'; ?>

    <!-- Schema Markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Restaurant",
        "name": "Bún Thịt Nướng BaMa",
        "description": "Quán ăn chuyên phục vụ bún thịt nướng và các món ăn truyền thống Việt Nam.",
        "url": "https://yourwebsite.com",
        "image": "/image/logo.jpg",
        "servesCuisine": "Vietnamese"
    }
    </script>

    <script>
        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const productItems = document.querySelectorAll('.product-item');
        const loadingSpinner = document.querySelector('.loading-spinner');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                loadingSpinner.style.display = 'block';

                setTimeout(() => {
                    const filterValue = button.getAttribute('data-filter');
                    productItems.forEach(item => {
                        if (filterValue === 'all') {
                            item.classList.remove('hidden');
                            item.style.animation = 'fadeInUp 0.6s ease-out both';
                        } else {
                            if (item.getAttribute('data-category') === filterValue) {
                                item.classList.remove('hidden');
                                item.style.animation = 'fadeInUp 0.6s ease-out both';
                            } else {
                                item.classList.add('hidden');
                            }
                        }
                    });
                    loadingSpinner.style.display = 'none';
                }, 300);
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchValue = e.target.value.toLowerCase();
            productItems.forEach(item => {
                const name = item.querySelector('.card-title').textContent.toLowerCase();
                if (name.includes(searchValue)) {
                    item.classList.remove('hidden');
                    item.style.animation = 'fadeInUp 0.6s ease-out both';
                } else {
                    item.classList.add('hidden');
                }
            });
        });

        // Back to Top functionality
        const backToTop = document.querySelector('.back-to-top');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
    <script src="cart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>