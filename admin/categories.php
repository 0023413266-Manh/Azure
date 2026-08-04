<?php
session_start();
include 'connect.php';

// BẪY LỖI: CHẶN VƯỢT RÀO
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 1. XỬ LÝ THÊM THƯƠNG HIỆU
if (isset($_POST['add_category'])) {
    $ten_th = $conn->real_escape_string($_POST['ten_thuong_hieu']);
    $conn->query("INSERT INTO thuong_hieu (ten_thuong_hieu) VALUES ('$ten_th')");
    $_SESSION['toast_msg'] = "Đã thêm thương hiệu: $ten_th";
    $_SESSION['toast_type'] = "success";
    header("Location: categories.php"); exit();
}

// 2. XỬ LÝ SỬA THƯƠNG HIỆU
if (isset($_POST['edit_category'])) {
    $id_sua = (int)$_POST['edit_id'];
    $ten_th = $conn->real_escape_string($_POST['ten_thuong_hieu']);
    $conn->query("UPDATE thuong_hieu SET ten_thuong_hieu = '$ten_th' WHERE id = $id_sua");
    $_SESSION['toast_msg'] = "Đã cập nhật thành: $ten_th";
    $_SESSION['toast_type'] = "success";
    header("Location: categories.php"); exit();
}

// 3. XỬ LÝ XÓA THƯƠNG HIỆU - CÓ BƯỚC KIỂM TRA AN TOÀN TRƯỚC KHI XÓA
if (isset($_POST['delete_category_id'])) {
    $id_xoa = (int)$_POST['delete_category_id'];
    
    // BƯỚC 1: Tự động đếm xem hãng này có đang chứa sản phẩm nào không
    $check_sp = $conn->query("SELECT COUNT(*) as total FROM san_pham WHERE id_thuong_hieu = $id_xoa")->fetch_assoc();
    
    if ($check_sp['total'] > 0) {
        // BƯỚC 2: Nếu có >= 1 sản phẩm -> CHẶN NGAY LẬP TỨC
        $_SESSION['toast_msg'] = "KHÔNG THỂ XÓA! Thương hiệu này đang chứa " . $check_sp['total'] . " sản phẩm. Vui lòng chuyển hoặc xóa sản phẩm trước.";
        $_SESSION['toast_type'] = "error";
    } else {
        // BƯỚC 3: Nếu an toàn (0 sản phẩm) -> Mới cho phép xóa
        if ($conn->query("DELETE FROM thuong_hieu WHERE id = $id_xoa")) {
            $_SESSION['toast_msg'] = "Đã xóa thương hiệu thành công!";
            $_SESSION['toast_type'] = "success";
        } else {
            $_SESSION['toast_msg'] = "Lỗi hệ thống: " . $conn->error;
            $_SESSION['toast_type'] = "error";
        }
    }
    
    header("Location: categories.php"); 
    exit();
}


$sql_cats = "SELECT t.id, t.ten_thuong_hieu, COUNT(p.id) as so_luong_sp 
             FROM thuong_hieu t 
             LEFT JOIN san_pham p ON t.id = p.id_thuong_hieu 
             GROUP BY t.id";
$result_cats = $conn->query($sql_cats);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh mục - Admin Timeless</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; width: 400px; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.5); animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .form-group { margin-bottom: 15px; } .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #555;}
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; outline: none;}
        .form-group input:focus { border-color: #b58b5a; }
        .btn-save { background: #28a745; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; font-size: 15px;}
        .btn-update { background: #b58b5a; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; font-size: 15px;}
        .btn-danger { background: #d9534f; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; font-size: 15px;}
        .btn-cancel { background: #ccc; color: #333; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; font-size: 15px;}
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <header class="header"><h3>Quản lý Danh mục</h3></header>
        <main class="content">
            <div class="card-header" style="display: flex; justify-content: space-between;">
                <h3>Danh sách Thương hiệu</h3>
                <button class="btn" onclick="openModal('addModal')"><i class="fa-solid fa-plus"></i> Thêm thương hiệu</button>
            </div>
            <table class="styled-table">
                <thead>
                    <tr><th>ID</th><th>Tên Thương hiệu</th><th>Số lượng SP</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if($result_cats && $result_cats->num_rows > 0): ?>
                        <?php while($row = $result_cats->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong style="color: #b58b5a;"><?php echo $row['ten_thuong_hieu']; ?></strong></td>
                            <td><?php echo $row['so_luong_sp']; ?> sản phẩm</td>
                            <td>
                                <button class="action-btn btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['ten_thuong_hieu'], ENT_QUOTES); ?>')"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn btn-delete" onclick="openDeleteModal(<?php echo $row['id']; ?>)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan=\"4\" style="text-align: center;">Chưa có danh mục nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="addModal" class="glass-modal">
        <div class="glass-modal-content">
            <h3 style="margin-top: 0; color: #28a745; border-bottom: 2px solid #eee; padding-bottom: 10px;">Thêm Thương Hiệu</h3>
            <form action="categories.php" method="POST">
                <div class="form-group"><label>Tên thương hiệu (*)</label><input type="text" name="ten_thuong_hieu" required></div>
                <button type="submit" name="add_category" class="btn-save"><i class="fa-solid fa-plus"></i> Lưu lại</button>
                <button type="button" class="btn-cancel" onclick="closeAllModals()">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="glass-modal">
        <div class="glass-modal-content">
            <h3 style="margin-top: 0; color: #b58b5a; border-bottom: 2px solid #eee; padding-bottom: 10px;">Sửa Thương Hiệu</h3>
            <form action="categories.php" method="POST">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="form-group"><label>Tên thương hiệu (*)</label><input type="text" name="ten_thuong_hieu" id="edit_ten" required></div>
                <button type="submit" name="edit_category" class="btn-update"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
                <button type="button" class="btn-cancel" onclick="closeAllModals()">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="glass-modal">
        <div class="glass-modal-content" style="text-align: center;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 50px; color: #d9534f; margin-bottom: 15px;"></i>
            <h3>Xác nhận xóa?</h3>
            <p>Hành động này sẽ xóa vĩnh viễn thương hiệu.</p>
            <form action="categories.php" method="POST">
                <input type="hidden" name="delete_category_id" id="delete_id">
                <button type="submit" class="btn-danger">Xóa ngay</button>
                <button type="button" class="btn-cancel" onclick="closeAllModals()">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeAllModals() { document.querySelectorAll('.glass-modal').forEach(m => m.style.display = 'none'); }
        function openEditModal(id, ten) { document.getElementById('edit_id').value = id; document.getElementById('edit_ten').value = ten; openModal('editModal'); }
        function openDeleteModal(id) { document.getElementById('delete_id').value = id; openModal('deleteModal'); }
    </script>
    
    <?php include '../thongbao.php'; ?>
</body>
</html>