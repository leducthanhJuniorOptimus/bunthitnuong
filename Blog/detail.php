<?php
session_start();
require_once "../db.php";

// Sanitize and validate slug from URL
$slug = isset($_GET['slug']) ? filter_var($_GET['slug'], FILTER_SANITIZE_STRING) : '';

if (empty($slug)) {
    http_response_code(404);
    echo "<p style='text-align: center; color: red;'>Không tìm thấy bài viết.</p>";
    exit;
}

// Truy vấn bài viết
$sql = "SELECT * FROM blog WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if (!$blog) {
    http_response_code(404);
    echo "<p style='text-align: center; color: red;'>Không tìm thấy bài viết.</p>";
    exit;
}

// Hệ thống đếm view thông minh với cookie (24 giờ)
$cookie_name = 'blog_view_' . $blog['id'];
$should_count_view = true;

if (isset($_COOKIE[$cookie_name])) {
    $last_view_time = (int)$_COOKIE[$cookie_name];
    $time_difference = time() - $last_view_time;
    
    if ($time_difference < 86400) {
        $should_count_view = false;
    }
}

if ($should_count_view) {
    $update_sql = "UPDATE blog SET views = views + 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $blog['id']);
    $update_stmt->execute();
    $update_stmt->close();
    setcookie($cookie_name, time(), time() + 86400, '/', '', false, true);
    $blog['views']++;
}

// Xử lý dữ liệu
$ingredients_images = $blog['ingredients_images'] ? explode('|', $blog['ingredients_images']) : [];
$preparation_steps = $blog['preparation_steps'] ? json_decode($blog['preparation_steps'], true) : [];

// Tạo base URL cho rewrite
$base_url = '/food/Blog/';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($blog['intro']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($blog['tags'] ?? 'ẩm thực, công thức, nấu ăn'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($blog['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($blog['intro']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($blog['thumbnail']); ?>">
    <meta property="og:url" content="<?php echo urlencode($base_url . $slug); ?>">
    <title><?php echo htmlspecialchars($blog['title']); ?> - Blog Ẩm Thực BaMa</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Recipe",
        "name": "<?php echo htmlspecialchars($blog['title']); ?>",
        "image": "<?php echo htmlspecialchars($blog['thumbnail']); ?>",
        "author": {
            "@type": "Person",
            "name": "<?php echo htmlspecialchars($blog['author']); ?>"
        },
        "datePublished": "<?php echo date('Y-m-d', strtotime($blog['created_at'])); ?>",
        "description": "<?php echo htmlspecialchars($blog['intro']); ?>",
        "prepTime": "<?php echo htmlspecialchars($blog['prep_time'] ?? 'PT15M'); ?>",
        "cookTime": "<?php echo htmlspecialchars($blog['cook_time'] ?? 'PT30M'); ?>",
        "recipeIngredient": <?php echo json_encode(explode("\n", $blog['ingredients'])); ?>,
        "recipeInstructions": <?php echo json_encode(array_map(function($step) {
            return [
                "@type" => "HowToStep",
                "text" => $step['content'],
                "name" => $step['title']
            ];
        }, $preparation_steps)); ?>
    }
    </script>
    <style>
        :root {
            --primary: #ff6b35;
            --secondary: #f7931e;
            --dark: #2d3748;
            --light: #f9fafb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: var(--light);
            color: #333;
            line-height: 1.6;
        }
        
        .blog-hero {
            position: relative;
            height: 70vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            overflow: hidden;
        }
        
        .blog-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('../<?php echo htmlspecialchars($blog['thumbnail']); ?>') center/cover;
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
            color: white;
        }
        
        .blog-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease;
        }
        
        .blog-meta {
            display: flex;
            gap: 2rem;
            font-size: 1.1rem;
            animation: fadeInUp 1s ease;
        }
        
        .blog-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }
        
        .blog-container {
            max-width: 1000px;
            margin: -100px auto 0;
            padding: 0 1.5rem 3rem;
            position: relative;
            z-index: 3;
        }
        
        .content-card {
            background: white;
            border-radius: 25px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            animation: fadeInUp 1.2s ease;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 10px;
        }
        
        .section-title i {
            color: var(--primary);
            margin-right: 0.5rem;
        }
        
        .intro-content {
            font-size: 1.15rem;
            line-height: 1.9;
            color: #555;
            margin-bottom: 2rem;
        }
        
        .intro-image {
            width: 100%;
            max-height: 600px;
            object-fit: cover;
            border-radius: 20px;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: transform 0.3s;
            cursor: pointer;
            loading: lazy;
        }
        
        .intro-image:hover {
            transform: scale(1.02);
        }
        
        .recipe-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 1.5rem;
        }
        
        .recipe-info p {
            margin: 0.5rem 0;
            font-size: 1.1rem;
            color: #555;
        }
        
        .recipe-info .badge {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }
        
        .toc-box {
            background: linear-gradient(135deg, #fff9f5 0%, #ffe8d6 100%);
            border-left: 5px solid var(--primary);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(255,107,53,0.1);
        }
        
        .toc-box ul {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        
        .toc-box > ul > li {
            margin-bottom: 1rem;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .toc-box > ul > li::before {
            content: '▸';
            position: absolute;
            left: 0;
            color: var(--primary);
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .toc-box li a {
            color: #333;
            text-decoration: none;
            font-size: 1.05rem;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
            padding: 0.3rem 0;
        }
        
        .toc-box li a:hover {
            color: var(--primary);
            transform: translateX(5px);
        }
        
        .toc-box ul ul {
            margin-top: 0.5rem;
            margin-left: 1rem;
            border-left: 2px solid #ffd4b8;
            padding-left: 1rem;
        }
        
        .toc-box ul ul li {
            margin-bottom: 0.6rem;
            padding-left: 1rem;
            position: relative;
        }
        
        .toc-box ul ul li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--secondary);
            font-size: 1.3rem;
            line-height: 1;
        }
        
        .toc-box ul ul li a {
            font-size: 0.95rem;
            font-weight: 400;
            color: #555;
        }
        
        .ingredients-section {
            background: linear-gradient(to bottom right, #f8f9fa, #fff);
            padding: 2rem;
            border-radius: 20px;
            border: 2px dashed #e0e0e0;
        }
        
        .ingredients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .ingredient-card {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.4s;
        }
        
        .ingredient-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .ingredient-card img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            transition: transform 0.4s;
            loading: lazy;
        }
        
        .ingredient-card:hover img {
            transform: scale(1.1);
        }
        
        .step-container {
            margin-bottom: 3rem;
        }
        
        .step-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e0e0e0;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .step-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
        }
        
        .step-card:hover {
            box-shadow: 0 10px 40px rgba(255,107,53,0.15);
            transform: translateX(5px);
        }
        
        .step-card:hover .step-title {
            color: var(--primary);
        }
        
        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            margin-right: 1.5rem;
            box-shadow: 0 5px 20px rgba(255,107,53,0.3);
        }
        
        .step-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--dark);
            flex: 1;
            transition: color 0.3s;
        }
        
        .step-content {
            font-size: 1.1rem;
            line-height: 1.9;
            color: #555;
            margin-bottom: 2rem;
        }
        
        .step-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        
        .step-image-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .step-image-wrapper:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 35px rgba(0,0,0,0.2);
        }
        
        .step-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
            cursor: pointer;
            loading: lazy;
        }
        
        .font-controls {
            position: fixed;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            background: white;
            border-radius: 50px;
            padding: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            z-index: 1000;
            animation: slideInRight 1s ease;
        }
        
        .font-btn {
            display: block;
            width: 50px;
            height: 50px;
            border: 2px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 50%;
            margin: 8px 0;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .font-btn:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            transform: scale(1.15);
            border-color: var(--secondary);
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 8px 25px rgba(255,107,53,0.3);
        }
        
        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255,107,53,0.4);
            color: white;
        }
        
        .read-more-btn {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        
        .read-more-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,53,0.3);
        }
        
        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            z-index: 1000;
            transition: width 0.3s ease;
        }
        
        .lightbox-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .lightbox-image {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        
        .share-section {
            margin-bottom: 3rem;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 10px 20px;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .share-btn.facebook {
            background: #3b5998;
        }
        
        .share-btn.twitter {
            background: #1da1f2;
        }
        
        .share-btn.pinterest {
            background: #bd081c;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
        
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
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translate(50px, -50%);
            }
            to {
                opacity: 1;
                transform: translate(0, -50%);
            }
        }
        
        @media (max-width: 768px) {
            .blog-hero {
                height: 50vh;
            }
            
            .blog-title {
                font-size: 2rem;
            }
            
            .blog-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .content-card {
                padding: 2rem 1.5rem;
            }
            
            .font-controls {
                display: none;
            }
            
            .step-card {
                padding: 1.5rem;
            }
            
            .step-header {
                flex-direction: column;
                text-align: center;
            }
            
            .step-number {
                margin: 0 0 1rem 0;
            }
            
            .intro-image {
                max-height: 400px;
            }
            
            .ingredient-card img {
                height: 250px;
            }
            
            .step-image {
                height: 200px;
            }
            
            .intro-content, .step-content {
                font-size: 1rem;
            }
            
            .toc-box ul ul {
                margin-left: 0.5rem;
                padding-left: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <!-- Hero Section -->
    <div class="blog-hero">
        <div class="hero-content">
            <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
            <div class="blog-meta">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($blog['author']); ?></span>
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></span>
                <span><i class="fas fa-eye"></i> <?php echo number_format($blog['views']); ?> lượt xem</span>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-bar" id="progress-bar"></div>

    <!-- Font Size Controls -->
    <div class="font-controls">
        <button class="font-btn" onclick="changeFontSize('decrease')" title="Giảm cỡ chữ">A-</button>
        <button class="font-btn" onclick="changeFontSize('reset')" title="Cỡ chữ mặc định">A</button>
        <button class="font-btn" onclick="changeFontSize('increase')" title="Tăng cỡ chữ">A+</button>
    </div>

    <!-- Main Content -->
    <div class="blog-container" id="main-content">
        <!-- Back Button -->
        

        <!-- Giới Thiệu -->
        <div class="content-card" id="intro">
            <h2 class="section-title"><i class="fas fa-info-circle"></i> Giới Thiệu</h2>
            <div class="intro-content" id="intro-content">
                <?php 
                $intro_content = $blog['intro'];
                $preview_length = 500;
                if (strlen($intro_content) > $preview_length):
                    $preview = substr($intro_content, 0, $preview_length);
                    $remaining = substr($intro_content, $preview_length);
                ?>
                    <div class="content-preview"><?php echo nl2br(htmlspecialchars($preview)); ?>...</div>
                    <div class="content-remaining" style="display: none;"><?php echo nl2br(htmlspecialchars($remaining)); ?></div>
                    <button class="read-more-btn" onclick="toggleContent()">Xem thêm</button>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($intro_content)); ?>
                <?php endif; ?>
                <div class="recipe-info mt-3">
                    <p><strong>Thời gian chuẩn bị:</strong> <?php echo htmlspecialchars($blog['prep_time'] ?? 'Chưa xác định'); ?></p>
                    <p><strong>Thời gian nấu:</strong> <?php echo htmlspecialchars($blog['cook_time'] ?? 'Chưa xác định'); ?></p>
                    <p><strong>Độ khó:</strong> <?php echo htmlspecialchars($blog['difficulty'] ?? 'Chưa xác định'); ?></p>
                    <?php if (!empty($blog['tags'])): ?>
                        <p><strong>Thẻ:</strong> 
                            <?php 
                            $tags = explode(',', $blog['tags']);
                            foreach ($tags as $tag): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($blog['intro_image']): ?>
                <img src="../<?php echo htmlspecialchars($blog['intro_image']); ?>" alt="Ảnh giới thiệu" class="intro-image" loading="lazy">
            <?php endif; ?>
        </div>

        <!-- Mục Lục -->
        <div class="content-card" id="muc-luc">
            <h2 class="section-title"><i class="fas fa-list-ul"></i> Mục Lục</h2>
            <div class="toc-box">
                <ul>
                    <li><a href="#intro">Giới Thiệu</a></li>
                    <li><a href="#ingredients">Nguyên Liệu Cần Chuẩn Bị</a></li>
                    <?php if ($blog['content']): ?>
                    <li><a href="#overview">Tổng Quan</a></li>
                    <?php endif; ?>
                    <?php if (!empty($preparation_steps)): ?>
                    <li>
                        <a href="#steps">Các Bước Chế Biến Chi Tiết</a>
                        <ul>
                            <?php foreach ($preparation_steps as $index => $step): ?>
                            <li><a href="#step-<?php echo $index + 1; ?>">Bước <?php echo $index + 1; ?>: <?php echo htmlspecialchars($step['title']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li><a href="#share">Chia Sẻ Bài Viết</a></li>
                </ul>
            </div>
        </div>

        <!-- Nguyên Liệu -->
        <div class="content-card" id="ingredients">
            <h2 class="section-title"><i class="fas fa-carrot"></i> Nguyên Liệu Cần Chuẩn Bị</h2>
            <div class="ingredients-section">
                <?php echo nl2br(htmlspecialchars($blog['ingredients'])); ?>
            </div>
            
            <?php if (!empty($ingredients_images)): ?>
            <div class="ingredients-grid">
                <?php foreach ($ingredients_images as $img): ?>
                    <div class="ingredient-card">
                        <img src="../<?php echo htmlspecialchars($img); ?>" alt="Nguyên liệu" loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Nội Dung Tổng Quan -->
        <?php if ($blog['content']): ?>
        <div class="content-card" id="overview">
            <h2 class="section-title"><i class="fas fa-file-alt"></i> Tổng Quan</h2>
            <div class="intro-content">
                <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Các Bước Chế Biến -->
        <?php if (!empty($preparation_steps)): ?>
        <div class="content-card" id="steps">
            <h2 class="section-title"><i class="fas fa-tasks"></i> Các Bước Chế Biến Chi Tiết</h2>
            <div class="step-container">
                <?php foreach ($preparation_steps as $index => $step): ?>
                <div class="step-card" id="step-<?php echo $index + 1; ?>">
                    <div class="step-header">
                        <div class="step-number"><?php echo $index + 1; ?></div>
                        <div class="step-title"><?php echo htmlspecialchars($step['title']); ?></div>
                    </div>
                    <div class="step-content">
                        <?php echo nl2br(htmlspecialchars($step['content'])); ?>
                    </div>
                    
                    <?php if (!empty($step['images'])): ?>
                    <div class="step-images-grid">
                        <?php foreach ($step['images'] as $img): ?>
                            <div class="step-image-wrapper">
                                <img src="../<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($step['title']); ?>" class="step-image" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Share Section -->
        <div class="share-section text-center mt-4" id="share">
            <h3 class="section-title"><i class="fas fa-share-alt"></i> Chia Sẻ Bài Viết</h3>
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($base_url . $slug); ?>" target="_blank" class="share-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($base_url . $slug); ?>&text=<?php echo urlencode($blog['title']); ?>" target="_blank" class="share-btn twitter">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode($base_url . $slug); ?>&media=<?php echo urlencode($blog['thumbnail']); ?>&description=<?php echo urlencode($blog['title']); ?>" target="_blank" class="share-btn pinterest">
                    <i class="fab fa-pinterest"></i> Pinterest
                </a>
            </div>
        </div>
    </div>
<div class="text-center mb-4">
            <a href="<?php echo $base_url; ?>" class="back-button">
                <i class="fas fa-arrow-left"></i> Quay Lại Blog
            </a>
        </div>
    <!-- Lightbox -->
    <div class="lightbox-overlay" id="lightbox">
        <img class="lightbox-image" id="lightbox-img" src="" alt="Ảnh lớn">
    </div>

    <?php include '../footer.html'; ?>

    <script>
        let currentFontSize = 100;
        
        function changeFontSize(action) {
            const mainContent = document.getElementById('main-content');
            
            if (action === 'increase' && currentFontSize < 140) {
                currentFontSize += 10;
            } else if (action === 'decrease' && currentFontSize > 80) {
                currentFontSize -= 10;
            } else if (action === 'reset') {
                currentFontSize = 100;
            }
            
            mainContent.style.fontSize = currentFontSize + '%';
            localStorage.setItem('blogFontSize', currentFontSize);
        }
        
        window.addEventListener('load', function() {
            const savedFontSize = localStorage.getItem('blogFontSize');
            if (savedFontSize) {
                currentFontSize = parseInt(savedFontSize);
                document.getElementById('main-content').style.fontSize = currentFontSize + '%';
            }
        });
        
        function toggleContent() {
            const preview = document.querySelector('.content-preview');
            const remaining = document.querySelector('.content-remaining');
            const btn = document.querySelector('.read-more-btn');
            
            if (remaining.style.display === 'none') {
                remaining.style.display = 'inline';
                btn.textContent = 'Thu gọn';
            } else {
                remaining.style.display = 'none';
                btn.textContent = 'Xem thêm';
            }
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 80;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.content-card, .step-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
        
        // Progress bar
        window.addEventListener('scroll', function() {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('progress-bar').style.width = scrolled + '%';
        });
        
        // Lightbox
        document.querySelectorAll('.intro-image, .ingredient-card img, .step-image').forEach(img => {
            img.addEventListener('click', function() {
                const lightbox = document.getElementById('lightbox');
                const lightboxImg = document.getElementById('lightbox-img');
                lightboxImg.src = this.src;
                lightbox.style.display = 'flex';
            });
        });
        
        document.getElementById('lightbox').addEventListener('click', function() {
            this.style.display = 'none';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>