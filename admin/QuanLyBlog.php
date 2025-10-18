<?php
session_start();
require_once "../db.php";

// Tạo thư mục uploads/blog nếu chưa tồn tại
$uploadDir = '../uploads/blog/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Hàm tạo slug
function createSlug($string) {
    $search = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ','è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ','ì','í','ị','ỉ','ĩ','ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ','ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ','À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ','È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ','Ì','Í','Ị','Ỉ','Ĩ','Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ','Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ','Ỳ','Ý','Ỵ','Ỷ','Ỹ','Đ'];
    $replace = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','E','E','E','E','E','E','E','E','E','E','E','I','I','I','I','I','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','U','U','U','U','U','U','U','U','U','U','U','Y','Y','Y','Y','Y','D'];
    $string = str_replace($search, $replace, $string);
    $string = preg_replace("/[^a-zA-Z0-9\s]/", "", $string);
    $string = str_replace(" ", "-", trim(strtolower($string)));
    $string = preg_replace("/-+/", "-", $string);
    return $string;
}

// Hàm upload ảnh
function uploadImage($file, $uploadDir) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowedTypes)) {
        $fileName = uniqid() . '-' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/blog/' . $fileName;
        }
    }
    return '';
}

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_blog'])) {
        $title = $_POST['title'] ?? '';
        $slug = $_POST['slug'] ? $_POST['slug'] : createSlug($title);
        $intro = $_POST['intro'] ?? '';
        $toc = $_POST['toc'] ?? '';
        $ingredients = $_POST['ingredients'] ?? '';
        $content = $_POST['content'] ?? '';
        $author = $_POST['author'] ?? '';
        
        // Upload ảnh đại diện
        $thumbnail = uploadImage($_FILES['thumbnail'], $uploadDir);
        
        // Upload ảnh giới thiệu
        $intro_image = uploadImage($_FILES['intro_image'], $uploadDir);
        
        // Upload ảnh nguyên liệu (nhiều ảnh)
        $ingredients_images = [];
        if (isset($_FILES['ingredients_images'])) {
            foreach ($_FILES['ingredients_images']['tmp_name'] as $key => $tmp_name) {
                $file = [
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['ingredients_images']['error'][$key],
                    'type' => $_FILES['ingredients_images']['type'][$key],
                    'name' => $_FILES['ingredients_images']['name'][$key]
                ];
                $uploaded = uploadImage($file, $uploadDir);
                if ($uploaded) $ingredients_images[] = $uploaded;
            }
        }
        $ingredients_images_str = implode('|', $ingredients_images);
        
        // Xử lý các bước chế biến với ảnh
        $preparation_steps = [];
        if (isset($_POST['step_title']) && is_array($_POST['step_title'])) {
            foreach ($_POST['step_title'] as $index => $step_title) {
                $step_content = $_POST['step_content'][$index] ?? '';
                $step_images = [];
                
                // Upload ảnh cho từng bước
                if (isset($_FILES['step_images']['tmp_name'][$index])) {
                    foreach ($_FILES['step_images']['tmp_name'][$index] as $img_key => $tmp_name) {
                        if ($_FILES['step_images']['error'][$index][$img_key] === UPLOAD_ERR_OK) {
                            $file = [
                                'tmp_name' => $tmp_name,
                                'error' => $_FILES['step_images']['error'][$index][$img_key],
                                'type' => $_FILES['step_images']['type'][$index][$img_key],
                                'name' => $_FILES['step_images']['name'][$index][$img_key]
                            ];
                            $uploaded = uploadImage($file, $uploadDir);
                            if ($uploaded) $step_images[] = $uploaded;
                        }
                    }
                }
                
                $preparation_steps[] = [
                    'title' => $step_title,
                    'content' => $step_content,
                    'images' => $step_images
                ];
            }
        }
        $preparation_steps_json = json_encode($preparation_steps, JSON_UNESCAPED_UNICODE);

        // Thêm vào database
        $sql = "INSERT INTO blog (title, slug, intro, intro_image, toc, ingredients, ingredients_images, content, preparation_steps, thumbnail, author) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $title, $slug, $intro, $intro_image, $toc, $ingredients, $ingredients_images_str, $content, $preparation_steps_json, $thumbnail, $author);
        $stmt->execute();
        $stmt->close();
        
        echo "<script>alert('Thêm bài blog thành công!'); window.location.href = 'QuanLyBlog.php';</script>";
    }
    
    if (isset($_POST['delete_blog'])) {
        $id = $_POST['id'];
        // Lấy và xóa các file ảnh
        $sql = "SELECT thumbnail, intro_image, ingredients_images, preparation_steps FROM blog WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Xóa các file ảnh
            $images_to_delete = [$row['thumbnail'], $row['intro_image']];
            if ($row['ingredients_images']) {
                $images_to_delete = array_merge($images_to_delete, explode('|', $row['ingredients_images']));
            }
            if ($row['preparation_steps']) {
                $steps = json_decode($row['preparation_steps'], true);
                foreach ($steps as $step) {
                    if (isset($step['images'])) {
                        $images_to_delete = array_merge($images_to_delete, $step['images']);
                    }
                }
            }
            foreach ($images_to_delete as $img) {
                if ($img && file_exists('../' . $img)) {
                    unlink('../' . $img);
                }
            }
        }
        
        // Xóa bài blog
        $sql = "DELETE FROM blog WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        echo "<script>alert('Xóa bài blog thành công!'); window.location.href = 'QuanLyBlog.php';</script>";
    }
}

// Lấy danh sách bài blog
$sql = "SELECT * FROM blog ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Blog - Bún Thịt Nướng BaMa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff6b35;
            --secondary: #f7931e;
            --dark: #2d3748;
            --light: #f9fafb;
        }
        
        body {
            background: var(--light);
            font-family: 'Roboto', sans-serif;
        }
        
        .container-custom {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .card-custom {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .page-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 10px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(255,107,53,0.2);
        }
        
        .btn-primary-custom {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,107,53,0.4);
        }
        
        .step-container {
            border: 2px dashed #e2e8f0;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }
        
        .step-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .btn-remove-step {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .btn-add-step {
            background: #48bb78;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 1rem;
        }
        
        .table-custom {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .table-custom th {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            font-weight: 600;
            padding: 1rem;
        }
        
        .table-custom td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .blog-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .btn-edit, .btn-delete {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            border: none;
            margin: 0 5px;
        }
        
        .btn-edit {
            background: #4a90e2;
            color: white;
        }
        
        .btn-delete {
            background: #e53e3e;
            color: white;
        }
           .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-header h3 {
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar.collapsed .sidebar-header h3 {
            display: none;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            padding-left: 2rem;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            border-right: 4px solid white;
        }

        .sidebar-menu i {
            font-size: 1.2rem;
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .sidebar-menu span {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
            padding: 0.8rem 0;
        }

        .sidebar.collapsed .sidebar-menu i {
            margin-right: 0;
        }

        .toggle-btn {
            position: absolute;
            top: 1rem;
            right: -15px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
     <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <button class="toggle-btn" id="toggleBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="sidebar-menu">
            <ul>
                <li>
                    <a href="QuanLyTrangChu.php">
                        <i class="fas fa-home"></i>
                        <span>Quản Lý Trang Chủ</span>
                    </a>
                </li>
                <li>
                    <a href="QuanLySanPham.php" class="active">
                        <i class="fas fa-box"></i>
                        <span>Quản Lý Sản Phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        <span>Quản Lý Người Dùng</span>
                    </a>
                </li>
                <li>
                    <a href="QuanLyBlog.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Quản Lý Blog</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-chart-bar"></i>
                        <span>Thống Kê</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Cài Đặt</span>
                    </a>
                </li>
                <li>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng Xuất</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="container-custom">
        <h1 class="page-title">Quản Lý Blog</h1>
        
        <!-- Form thêm blog -->
        <div class="card-custom">
            <h3 class="mb-4"><i class="fas fa-plus-circle"></i> Thêm Bài Blog Mới</h3>
            <form method="POST" enctype="multipart/form-data" id="blogForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tiêu Đề</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug (để trống tự động tạo)</label>
                        <input type="text" name="slug" class="form-control">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tác Giả</label>
                        <input type="text" name="author" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-image"></i> Ảnh Đại Diện (Thumbnail)</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*" required>
                    </div>
                </div>
                
                <!-- Giới thiệu -->
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-info-circle"></i> Giới Thiệu</label>
                    <textarea name="intro" class="form-control" rows="4" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-image"></i> Ảnh Giới Thiệu</label>
                    <input type="file" name="intro_image" class="form-control" accept="image/*">
                </div>
                
                <!-- Mục lục -->
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-list"></i> Mục Lục (HTML)</label>
                    <textarea name="toc" class="form-control" rows="3"></textarea>
                </div>
                
                <!-- Nguyên liệu -->
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-carrot"></i> Nguyên Liệu (HTML)</label>
                    <textarea name="ingredients" class="form-control" rows="5" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-images"></i> Ảnh Nguyên Liệu (có thể chọn nhiều)</label>
                    <input type="file" name="ingredients_images[]" class="form-control" accept="image/*" multiple>
                </div>
                
                <!-- Nội dung tổng quan -->
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-file-alt"></i> Nội Dung Tổng Quan (HTML)</label>
                    <textarea name="content" class="form-control" rows="4"></textarea>
                </div>
                
                <!-- Các bước chế biến -->
                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-tasks"></i> Các Bước Chế Biến Chi Tiết</label>
                    <div id="stepsContainer">
                        <div class="step-container">
                            <div class="step-header">
                                <h5>Bước 1</h5>
                                <button type="button" class="btn-remove-step" onclick="removeStep(this)"><i class="fas fa-times"></i> Xóa</button>
                            </div>
                            <input type="text" name="step_title[]" class="form-control mb-2" placeholder="Tiêu đề bước (VD: Sơ chế đu đủ)" required>
                            <textarea name="step_content[]" class="form-control mb-2" rows="3" placeholder="Mô tả chi tiết cách làm..." required></textarea>
                            <input type="file" name="step_images[0][]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">Có thể chọn nhiều ảnh minh họa cho bước này</small>
                        </div>
                    </div>
                    <button type="button" class="btn-add-step" onclick="addStep()"><i class="fas fa-plus"></i> Thêm Bước</button>
                </div>
                
                <button type="submit" name="add_blog" class="btn-primary-custom">
                    <i class="fas fa-save"></i> Lưu Bài Blog
                </button>
            </form>
        </div>
        
        <!-- Danh sách blog -->
        <div class="card-custom">
            <h3 class="mb-4"><i class="fas fa-list"></i> Danh Sách Bài Blog</h3>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Tiêu Đề</th>
                            <th>Tác Giả</th>
                            <th>Ngày Đăng</th>
                            <th>Lượt Xem</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($row['thumbnail']); ?>" class="blog-thumb"></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['author']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td><?php echo number_format($row['views']); ?></td>
                            <td>
                                <a href="/food/Blog/<?php echo htmlspecialchars($row['slug']); ?>" class="btn-edit" target="_blank"><i class="fas fa-eye"></i> Xem</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_blog" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa?')"><i class="fas fa-trash"></i> Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let stepCount = 1;
        
        function addStep() {
            stepCount++;
            const container = document.getElementById('stepsContainer');
            const stepDiv = document.createElement('div');
            stepDiv.className = 'step-container';
            stepDiv.innerHTML = `
                <div class="step-header">
                    <h5>Bước ${stepCount}</h5>
                    <button type="button" class="btn-remove-step" onclick="removeStep(this)"><i class="fas fa-times"></i> Xóa</button>
                </div>
                <input type="text" name="step_title[]" class="form-control mb-2" placeholder="Tiêu đề bước" required>
                <textarea name="step_content[]" class="form-control mb-2" rows="3" placeholder="Mô tả chi tiết cách làm..." required></textarea>
                <input type="file" name="step_images[${stepCount-1}][]" class="form-control" accept="image/*" multiple>
                <small class="text-muted">Có thể chọn nhiều ảnh minh họa cho bước này</small>
            `;
            container.appendChild(stepDiv);
        }
        
        function removeStep(btn) {
            if (document.querySelectorAll('.step-container').length > 1) {
                btn.closest('.step-container').remove();
                updateStepNumbers();
            } else {
                alert('Phải có ít nhất 1 bước chế biến!');
            }
        }
        
        function updateStepNumbers() {
            document.querySelectorAll('.step-container').forEach((step, index) => {
                step.querySelector('h5').textContent = `Bước ${index + 1}`;
            });
            stepCount = document.querySelectorAll('.step-container').length;
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>