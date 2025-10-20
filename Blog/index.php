<?php
session_start();
require_once "../db.php";

// Truy vấn tất cả bài viết
$sql = "SELECT id, title, slug, intro, thumbnail, author, created_at, views FROM blog ORDER BY created_at DESC";
$result = $conn->query($sql);

// Kiểm tra lỗi truy vấn
if (!$result) {
    echo "<p style='text-align: center; color: red;'>Lỗi khi tải bài viết: " . $conn->error . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Blog ẩm thực của Bún Thịt Nướng BaMa - Khám phá công thức nấu ăn ngon và mẹo chế biến món Việt.">
    <meta name="keywords" content="blog ẩm thực, công thức nấu ăn, bún thịt nướng, món Việt">
    <meta property="og:title" content="Blog Ẩm Thực BaMa">
    <meta property="og:description" content="Khám phá các công thức nấu ăn ngon và mẹo chế biến món Việt tại Blog Bún Thịt Nướng BaMa.">
    <meta property="og:image" content="/image/logo.jpg">
    <meta property="og:url" content="http://localhost/Blog">
    <title>Blog Ẩm Thực - Bún Thịt Nướng BaMa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .blog-section {
            padding: 4rem 0;
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

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .blog-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: fadeInUp 0.6s ease-out both;
        }

        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.2);
        }

        .blog-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 200px;
        }

        .blog-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .blog-card:hover .blog-img {
            transform: scale(1.1);
        }

        .blog-body {
            padding: 1.5rem;
        }

        .blog-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .blog-card:hover .blog-title {
            color: #ff6b35;
        }

        .blog-meta {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .blog-intro {
            font-size: 1rem;
            color: #4a5568;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-read-more {
            color: #ff6b35;
            font-weight: 600;
            text-decoration: none;
        }

        .blog-read-more:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }

            .blog-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
    <section class="hero-section" id="home">
        <video class="hero-video" autoplay muted loop preload="auto" poster="image/poster.jpg">
            <source src="../image/blog.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Bún Thịt Nướng BaMa</h1>
            <p>Tin Túc - Chia Sẽ - Hướng Dẫn Công Thức Thực Phẩn</p>
            <a href="#products" class="hero-btn">Khám Phá Ngay</a>
        </div>
    </section>
    <!-- Blog Section -->
    <section class="blog-section" id="blog">
        <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 0 2rem;">
            <div class="section-header">
                <h2 class="section-title">Blog Ẩm Thực BaMa</h2>
            </div>
            <div class="blog-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="blog-card">
                            <div class="blog-img-wrapper">
                                <img src="/food/<?php echo htmlspecialchars($row['thumbnail']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="blog-img" loading="lazy">
                            </div>
                            <div class="blog-body">
                                <h3 class="blog-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($row['author']); ?></span> |
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($row['created_at'])); ?></span> |
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($row['views']); ?> Lượt xem</span>
                                </div>
                                <p class="blog-intro"><?php echo htmlspecialchars($row['intro']); ?></p>
                                <a href="/food/Blog/<?php echo htmlspecialchars($row['slug']); ?>" class="blog-read-more">Đọc thêm</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center;">Chưa có bài viết nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include '../footer.html'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>