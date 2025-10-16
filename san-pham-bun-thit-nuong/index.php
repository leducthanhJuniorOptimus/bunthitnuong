<?php 
session_start();
require_once "../db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tất cả sản phẩm của bún thịt nướng bama</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
     <link
      href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../style.css">
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

        /* Filter Buttons */
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

        .filter-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.3);
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

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        /* Product Card */
        .menu-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            animation: fadeInUp 0.6s ease-out both;
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

        .btn btn-danger {
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

        .btn btn-danger::before {
            content: '🛒';
            position: absolute;
            left: -30px;
            transition: all 0.3s ease;
        }

        .btn btn-danger:hover::before {
            left: 15px;
        }

        .btn btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            padding-left: 40px;
        }

        .btn btn-danger:active {
            transform: scale(0.95);
        }

        /* Hidden State */
        .product-item.hidden {
            display: none;
        }

        /* Animations */
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

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Stagger animation for cards */
        .product-item:nth-child(1) { animation-delay: 0.1s; }
        .product-item:nth-child(2) { animation-delay: 0.2s; }
        .product-item:nth-child(3) { animation-delay: 0.3s; }
        .product-item:nth-child(4) { animation-delay: 0.4s; }

        /* Responsive */
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

        /* Decorative Elements */
        

    </style>
</head>
<body>
      <?php include '../header.php'; ?>

    <!-- All Products Section -->
 <section class="hero-section" id="home">
        <video class="hero-video" autoplay muted loop>
            <source src="../video/videosanpham.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Tất Cả Sản Phẩm Bún Thịt Nướng Bama</h1>
            <p></p>
            <a href="#products" class="hero-btn">Xem Tất Cả Sản Phẩm</a>
        </div>
    </section>

  <!-- Filter Buttons -->
<section class="products-section" id="products">
        <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 0 2rem;">
            <div class="section-header">
                <h2 class="section-title">Tất Cả Sản Phẩm</h2>
            </div>

            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">
                    <span>🍜 Tất Cả</span>
                </button>
                <button class="filter-btn" data-filter="bun-thit-nuong">
                    <span>🥢 Bún Thịt Nướng</span>
                </button>
                <button class="filter-btn" data-filter="bun-dac-biet">
                    <span>⭐ Bún Đặc Biệt</span>
                </button>
                <button class="filter-btn" data-filter="com-chien">
                    <span>🍚 Cơm Chiên</span>
                </button>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <!-- Product 1 -->
                <div class="menu-card product-item" data-category="bun-thit-nuong">
                    <div class="card-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=300&h=200&fit=crop" alt="Bún Thịt Nướng" class="card-img-top">
                        <span class="special-badge">🔥 Phổ Biến</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Bún Thịt Nướng</h5>
                        <p class="card-text">45,000 VNĐ</p>
                        <button class="btn btn-danger" data-name="Bún Thịt Nướng" data-price="45000">Thêm vào giỏ</button>
                    </div>
                </div>

                <!-- Product 2 -->
                <div class="menu-card product-item" data-category="bun-dac-biet">
                    <div class="card-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=300&h=200&fit=crop" alt="Bún Đặc Biệt" class="card-img-top">
                        <span class="special-badge">✨ Đặc Biệt</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Bún Đặc Biệt</h5>
                        <p class="card-text">60,000 VNĐ</p>
                        <button class="btn btn-danger" data-name="Bún Đặc Biệt" data-price="60000">Thêm vào giỏ</button>
                    </div>
                </div>

                <!-- Product 3 -->
                <div class="menu-card product-item" data-category="com-chien">
                    <div class="card-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1603133872878-684f1e9c4694?w=300&h=200&fit=crop" alt="Cơm Chiên" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Cơm Chiên Hải Sản</h5>
                        <p class="card-text">55,000 VNĐ</p>
                        <button class="btn btn-danger" data-name="Cơm Chiên Hải Sản" data-price="55000">Thêm vào giỏ</button>
                    </div>
                </div>

                <!-- Product 4 -->
                <div class="menu-card product-item" data-category="bun-thit-nuong">
                    <div class="card-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=300&h=200&fit=crop" alt="Bún Thịt Nướng Đặc Biệt" class="card-img-top">
                        <span class="special-badge">🆕 Mới</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Bún Thịt Nướng Đặc Biệt</h5>
                        <p class="card-text">50,000 VNĐ</p>
                        <button class="btn btn-danger" data-name="Bún Thịt Nướng Đặc Biệt" data-price="50000">Thêm vào giỏ</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const productItems = document.querySelectorAll('.product-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');

                const filterValue = button.getAttribute('data-filter');

                productItems.forEach(item => {
                    if (filterValue === 'all') {
                        item.classList.remove('hidden');
                        setTimeout(() => {
                            item.style.animation = 'fadeInUp 0.6s ease-out both';
                        }, 10);
                    } else {
                        if (item.getAttribute('data-category') === filterValue) {
                            item.classList.remove('hidden');
                            setTimeout(() => {
                                item.style.animation = 'fadeInUp 0.6s ease-out both';
                            }, 10);
                        } else {
                            item.classList.add('hidden');
                        }
                    }
                });
            });
        });

    </script>
    <?php include '../footer.html'; ?>
    <script src="/food/cart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>