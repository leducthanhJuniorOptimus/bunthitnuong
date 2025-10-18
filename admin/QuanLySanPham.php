<?php 
session_start();
require_once "../db.php";

// Check if user is admin
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

// Create uploads/product directory if it doesn't exist
$uploadDir = '../uploads/product/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $is_special = isset($_POST['is_special']) ? 1 : 0;
        $imagePath = '';

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $fileType = $_FILES['image']['type'];
            if (in_array($fileType, $allowedTypes)) {
                $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
                $imagePath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    $imagePath = 'uploads/product/' . $fileName; // Relative path for database
                } else {
                    echo "Failed to upload image.";
                }
            } else {
                echo "Invalid file type. Only JPG, JPEG, PNG allowed.";
            }
        }

        // Insert into database
        $sql = "INSERT INTO products (name, category, price, image, is_special) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsi", $name, $category, $price, $imagePath, $is_special);
        $stmt->execute();
    } elseif (isset($_POST['edit_product'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $is_special = isset($_POST['is_special']) ? 1 : 0;
        $imagePath = $_POST['existing_image'];

        // Handle image upload for edit
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $fileType = $_FILES['image']['type'];
            if (in_array($fileType, $allowedTypes)) {
                $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
                $imagePath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    $imagePath = 'uploads/product/' . $fileName; // Relative path for database
                    // Optionally delete old image
                    if (file_exists('../' . $_POST['existing_image'])) {
                        unlink('../' . $_POST['existing_image']);
                    }
                } else {
                    echo "Failed to upload image.";
                }
            } else {
                echo "Invalid file type. Only JPG, JPEG, PNG allowed.";
            }
        }

        // Update database
        $sql = "UPDATE products SET name = ?, category = ?, price = ?, image = ?, is_special = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssi", $name, $category, $price, $imagePath, $is_special, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_product'])) {
        $id = $_POST['id'];
        // Fetch image path to delete file
        $sql = "SELECT image FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (file_exists('../' . $row['image'])) {
                unlink('../' . $row['image']);
            }
        }
        // Delete product from database
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Bún Thịt Nướng Bama</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        :root {
            --primary-color: #ff6b35;
            --secondary-color: #f7931e;
            --dark-color: #2d3748;
            --light-color: #f9fafb;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--light-color);
            font-family: 'Roboto', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
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

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            padding: 2rem;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        .admin-form {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 3rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 10px rgba(255, 107, 53, 0.2);
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .products-table {
            width: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th,
        .products-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .products-table th {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
        }

        .products-table tr:hover {
            background: #fff7f2;
        }

        .btn-edit,
        .btn-delete {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-edit {
            background: #4a90e2;
            color: white;
            margin-right: 0.5rem;
        }

        .btn-edit:hover {
            background: #357abd;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #e53e3e;
            color: white;
        }

        .btn-delete:hover {
            background: #c53030;
            transform: translateY(-2px);
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
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

        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }
            
            .sidebar.collapsed {
                width: 0;
            }
            
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
            
            .section-title {
                font-size: 2rem;
            }

            .admin-form {
                padding: 1.5rem;
            }

            .products-table th,
            .products-table td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="admin-container">
            <div class="section-header">
                <h2 class="section-title">Quản Lý Sản Phẩm</h2>
                <p class="text-gray-600">Quản lý danh sách sản phẩm của nhà hàng</p>
            </div>

            <!-- Add Product Form -->
            <div class="admin-form">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Thêm Sản Phẩm Mới</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Tên Sản Phẩm</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Danh Mục</label>
                                <select id="category" name="category" required>
                                    <option value="bun-thit-nuong">Bún Thịt Nướng</option>
                                    <option value="bun-dac-biet">Bún Đặc Biệt</option>
                                    <option value="com-chien">Cơm Chiên</option>
                                    <option value="combo">Combo</option>
                                    <option value="dothem">Đồ Thêm</option>
                                    <option value="nuocngot">Nước Ngọt</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Giá (VNĐ)</label>
                                <input type="number" id="price" name="price" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="image">Hình Ảnh Sản Phẩm</label>
                                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/jpg" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="d-flex align-items-center">
                            <span class="me-3">Sản Phẩm Đặc Biệt</span>
                            <label class="switch">
                                <input type="checkbox" name="is_special">
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    <button type="submit" name="add_product" class="btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm Sản Phẩm
                    </button>
                </form>
            </div>

            <!-- Products Table -->
            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình Ảnh</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Danh Mục</th>
                            <th>Giá (VNĐ)</th>
                            <th>Đặc Biệt</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-img"></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo ucfirst(str_replace('-', ' ', $row['category'])); ?></td>
                            <td><?php echo number_format($row['price']); ?></td>
                            <td>
                                <?php if ($row['is_special']): ?>
                                    <span class="badge bg-success">Có</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Không</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-edit" onclick='editProduct(<?php echo json_encode($row); ?>)'>
                                    <i class="fas fa-edit me-1"></i>Sửa
                                </button>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                        <i class="fas fa-trash me-1"></i>Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Sửa Sản Phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="edit-id">
                        <input type="hidden" name="existing_image" id="edit-existing-image">
                        <div class="form-group">
                            <label for="edit-name">Tên Sản Phẩm</label>
                            <input type="text" id="edit-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-category">Danh Mục</label>
                            <select id="edit-category" name="category" required>
                                <option value="bun-thit-nuong">Bún Thịt Nướng</option>
                                <option value="bun-dac-biet">Bún Đặc Biệt</option>
                                <option value="com-chien">Cơm Chiên</option>
                                <option value="combo">Combo</option>
                                <option value="dothem">Đồ Thêm</option>
                                <option value="nuocngot">Nước Ngọt</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-price">Giá (VNĐ)</label>
                            <input type="number" id="edit-price" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-image">Hình Ảnh Sản Phẩm</label>
                            <input type="file" id="edit-image" name="image" accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Để trống nếu không muốn thay đổi hình ảnh.</small>
                        </div>
                        <div class="form-group">
                            <label class="d-flex align-items-center">
                                <span class="me-3">Sản Phẩm Đặc Biệt</span>
                                <label class="switch">
                                    <input type="checkbox" name="is_special" id="edit-is-special">
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                        <button type="submit" name="edit_product" class="btn-primary">
                            <i class="fas fa-save me-2"></i>Lưu Thay Đổi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Rotate the icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
        });

        function editProduct(product) {
            document.getElementById('edit-id').value = product.id;
            document.getElementById('edit-name').value = product.name;
            document.getElementById('edit-category').value = product.category;
            document.getElementById('edit-price').value = product.price;
            document.getElementById('edit-existing-image').value = product.image;
            document.getElementById('edit-is-special').checked = product.is_special == 1;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>