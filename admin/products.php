<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 1. XỬ LÝ THÊM SẢN PHẨM MỚI
if (isset($_POST['add_product'])) {
    $ten_sp = $conn->real_escape_string($_POST['ten_san_pham']);
    $gia_ban = (float)$_POST['gia_ban'];
    $gia_cu = !empty($_POST['gia_cu']) ? (float)$_POST['gia_cu'] : "NULL";
    $id_thuong_hieu = (int)$_POST['id_thuong_hieu'];
    $so_tham_chieu = $conn->real_escape_string($_POST['so_tham_chieu']);
    $ton_kho = (int)$_POST['ton_kho'];
    if ($ton_kho < 0) {
    echo "<script>
        alert('Lỗi: Số lượng tồn kho không được phép nhỏ hơn 0!');
        history.back();
    </script>";
    exit();
}
    
    $anh_san_pham = 'image/logo.png'; 
    if (isset($_FILES['anh_san_pham']) && $_FILES['anh_san_pham']['error'] == 0) {
        $target_dir = "../image/";
        $file_name = time() . "_" . basename($_FILES["anh_san_pham"]["name"]);
        if (move_uploaded_file($_FILES["anh_san_pham"]["tmp_name"], $target_dir . $file_name)) {
            $anh_san_pham = "image/" . $file_name;
        }
    }

    if($conn->query("INSERT INTO san_pham (id_thuong_hieu, ten_san_pham, gia_ban, gia_cu, anh_san_pham, so_tham_chieu, ton_kho) VALUES ($id_thuong_hieu, '$ten_sp', $gia_ban, $gia_cu, '$anh_san_pham', '$so_tham_chieu', $ton_kho)")) {
        $_SESSION['toast_msg'] = "Đã thêm sản phẩm: $ten_sp";
        $_SESSION['toast_type'] = "success";
    }
    header("Location: products.php"); exit();
}

if (isset($_FILES['anh_san_pham']) && $_FILES['anh_san_pham']['error'] == 0) {
    // LẤY ĐUÔI FILE (jpg, png, jpeg...)
    $file_ext = strtolower(pathinfo($_FILES["anh_san_pham"]["name"], PATHINFO_EXTENSION));
    $allowed_ext = array("jpg", "jpeg", "png", "gif", "webp"); // Chỉ cho phép file ảnh
    
    if (in_array($file_ext, $allowed_ext)) {
        $target_dir = "../image/";
        $file_name = time() . "_" . basename($_FILES["anh_san_pham"]["name"]);
        if (move_uploaded_file($_FILES["anh_san_pham"]["tmp_name"], $target_dir . $file_name)) {
            $anh_san_pham = "image/" . $file_name;
        }
    } else {
        echo "<script>alert('Lỗi: Chỉ được upload file hình ảnh (JPG, PNG, GIF)!'); history.back();</script>";
        exit();
    }
}

// 2. XỬ LÝ CẬP NHẬT (SỬA) SẢN PHẨM
if (isset($_POST['edit_product'])) {
    $id_sua = (int)$_POST['edit_id'];
    $ten_sp = $conn->real_escape_string($_POST['ten_san_pham']);
    $gia_ban = (float)$_POST['gia_ban'];
    $gia_cu = !empty($_POST['gia_cu']) ? (float)$_POST['gia_cu'] : "NULL";
    $id_thuong_hieu = (int)$_POST['id_thuong_hieu'];
    $so_tham_chieu = $conn->real_escape_string($_POST['so_tham_chieu']);
    $ton_kho = (int)$_POST['ton_kho'];
    if ($ton_kho < 0) {
    echo "<script>
        alert('Lỗi: Số lượng tồn kho không được phép nhỏ hơn 0!');
        history.back();
    </script>";
    exit();
}
    
    if (isset($_FILES['anh_san_pham']) && $_FILES['anh_san_pham']['error'] == 0) {
        $target_dir = "../image/";
        $file_name = time() . "_edit_" . basename($_FILES["anh_san_pham"]["name"]);
        if (move_uploaded_file($_FILES["anh_san_pham"]["tmp_name"], $target_dir . $file_name)) {
            $anh_moi = "image/" . $file_name;
            $conn->query("UPDATE san_pham SET id_thuong_hieu=$id_thuong_hieu, ten_san_pham='$ten_sp', gia_ban=$gia_ban, gia_cu=$gia_cu, anh_san_pham='$anh_moi', so_tham_chieu='$so_tham_chieu', ton_kho=$ton_kho WHERE id=$id_sua");
        }
    } else {
        $conn->query("UPDATE san_pham SET id_thuong_hieu=$id_thuong_hieu, ten_san_pham='$ten_sp', gia_ban=$gia_ban, gia_cu=$gia_cu, so_tham_chieu='$so_tham_chieu', ton_kho=$ton_kho WHERE id=$id_sua");
    }
    $_SESSION['toast_msg'] = "Đã cập nhật sản phẩm: $ten_sp";
    $_SESSION['toast_type'] = "success";
    header("Location: products.php"); exit();
}

if (isset($_FILES['anh_san_pham']) && $_FILES['anh_san_pham']['error'] == 0) {
    // LẤY ĐUÔI FILE (jpg, png, jpeg...)
    $file_ext = strtolower(pathinfo($_FILES["anh_san_pham"]["name"], PATHINFO_EXTENSION));
    $allowed_ext = array("jpg", "jpeg", "png", "gif", "webp"); // Chỉ cho phép file ảnh
    
    if (in_array($file_ext, $allowed_ext)) {
        $target_dir = "../image/";
        $file_name = time() . "_" . basename($_FILES["anh_san_pham"]["name"]);
        if (move_uploaded_file($_FILES["anh_san_pham"]["tmp_name"], $target_dir . $file_name)) {
            $anh_san_pham = "image/" . $file_name;
        }
    } else {
        echo "<script>alert('Lỗi: Chỉ được upload file hình ảnh (JPG, PNG, GIF)!'); history.back();</script>";
        exit();
    }
}

// 3. XỬ LÝ XÓA SẢN PHẨM (CÓ BẢO VỆ CHỐNG MẤT LỊCH SỬ ĐƠN HÀNG)
if (isset($_POST['delete_product_id'])) {
    $id_xoa = (int)$_POST['delete_product_id'];

    // Bước 1: Lấy đường dẫn ảnh cũ để chuẩn bị xóa (nhưng chưa xóa vội)
    $sql_get_img = "SELECT anh_san_pham FROM san_pham WHERE id = $id_xoa";
    $res = $conn->query($sql_get_img);
    $old_img = "";
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $old_img = "../" . $row['anh_san_pham'];
    }

    // Bước 2: Thử lệnh xóa CSDL
    if ($conn->query("DELETE FROM san_pham WHERE id = $id_xoa")) {
        // BƯỚC 3: Nếu xóa CSDL thành công -> Mới xóa ảnh trên ổ cứng
        if (!empty($old_img) && file_exists($old_img) && strpos($old_img, 'logo.png') === false) {
            unlink($old_img);
        }
        $_SESSION['toast_msg'] = "Đã xóa sản phẩm thành công!";
        $_SESSION['toast_type'] = "success";
    } else {
        // BƯỚC 4: Nếu thất bại do có đơn hàng dính líu (Mã 1451) -> Chặn lại
        if ($conn->errno == 1451) {
            $_SESSION['toast_msg'] = "LỖI BẢO VỆ DỮ LIỆU: Sản phẩm này đã nằm trong hóa đơn của khách. Không thể xóa để bảo toàn lịch sử giao dịch!";
            $_SESSION['toast_type'] = "error";
        } else {
            $_SESSION['toast_msg'] = "Lỗi hệ thống: " . $conn->error;
            $_SESSION['toast_type'] = "error";
        }
    }
    header("Location: products.php");
    exit();
}

$sql_products = "SELECT p.*, t.ten_thuong_hieu FROM san_pham p LEFT JOIN thuong_hieu t ON p.id_thuong_hieu = t.id ORDER BY p.id DESC";
$result_products = $conn->query($sql_products);

$result_brands = $conn->query("SELECT * FROM thuong_hieu");
$brands = [];
while($b = $result_brands->fetch_assoc()) { $brands[] = $b; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sản phẩm - Admin Timeless</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; width: 500px; max-width: 90%; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.5); animation: slideIn 0.3s ease-out; position: relative; }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .form-group { margin-bottom: 15px; text-align: left;} .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; } .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .btn-save { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; } .btn-cancel { background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; } .btn-update { background: #b58b5a; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; }
        .delete-modal-content { width: 400px; text-align: center; } .delete-modal-content i { font-size: 60px; color: #d9534f; margin-bottom: 15px; } .btn-danger { background: #d9534f; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header class="header"><h3>Quản lý Sản phẩm</h3></header>
        <main class="content">
            <div class="card-header" style="display: flex; justify-content: space-between;">
                <h3>Danh sách sản phẩm</h3>
                <button class="btn" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Thêm sản phẩm mới</button>
            </div>
            <table class="styled-table">
                <thead>
                    <tr><th>ID</th><th>Hình ảnh</th><th>Tên sản phẩm</th><th>Giá tiền</th><th>Thương hiệu</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if($result_products && $result_products->num_rows > 0): ?>
                        <?php while($row = $result_products->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#SP<?php echo $row['id']; ?></strong></td>
                            <td><img src="../<?php echo trim($row['anh_san_pham']); ?>" onerror="this.src='../image/logo.png'" width="50" style="border-radius:4px; max-height: 50px; max-width: 50px; object-fit: contain; background: #fff;"></td>
                            <td><?php echo $row['ten_san_pham']; ?></td>
                            <td style="font-weight: bold; color: #b58b5a;"><?php echo number_format($row['gia_ban'], 0, ',', '.'); ?>đ</td>
                            <td><?php echo $row['ten_thuong_hieu'] ?? 'Khác'; ?></td>
                            <td>
                                <button class="action-btn btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['ten_san_pham'], ENT_QUOTES); ?>', <?php echo $row['gia_ban']; ?>, '<?php echo $row['gia_cu']; ?>', '<?php echo $row['so_tham_chieu']; ?>', <?php echo $row['id_thuong_hieu']; ?>, <?php echo $row['ton_kho']; ?>)"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn btn-delete" onclick="openDeleteModal(<?php echo $row['id']; ?>)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">Chưa có sản phẩm nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="addModal" class="glass-modal">
        <div class="glass-modal-content">
            <h3 style="margin-top: 0; color: #28a745; border-bottom: 2px solid #eee; padding-bottom: 10px;">Thêm Sản Phẩm Mới</h3>
            <form action="products.php" method="POST" enctype="multipart/form-data">
                <div class="form-group"><label>Tên sản phẩm (*)</label><input type="text" name="ten_san_pham" required></div>
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;"><label>Giá bán (*)</label><input type="number" name="gia_ban" required></div>
                    <div class="form-group" style="flex: 1;"><label>Giá cũ</label><input type="number" name="gia_cu"></div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;"><label>Mã tham chiếu (Ref)</label><input type="text" name="so_tham_chieu" required></div>
                    <div class="form-group" style="flex: 1;"><label>Thương hiệu</label>
                        <select name="id_thuong_hieu" required>
                            <?php foreach($brands as $b): ?><option value="<?php echo $b['id']; ?>"><?php echo $b['ten_thuong_hieu']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Số lượng kho</label><input type="number" name="ton_kho" value="10" required></div>
                <div class="form-group"><label>Tải ảnh lên (*)</label><input type="file" name="anh_san_pham" accept="image/*" required></div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeModals()">Hủy bỏ</button>
                    <button type="submit" name="add_product" class="btn-save"><i class="fa fa-save"></i> Thêm Sản Phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="glass-modal">
        <div class="glass-modal-content">
            <h3 style="margin-top: 0; color: #b58b5a; border-bottom: 2px solid #eee; padding-bottom: 10px;">Chỉnh Sửa Sản Phẩm</h3>
            <form action="products.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="form-group"><label>Tên sản phẩm (*)</label><input type="text" name="ten_san_pham" id="edit_ten" required></div>
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;"><label>Giá bán (*)</label><input type="number" name="gia_ban" id="edit_gia" required></div>
                    <div class="form-group" style="flex: 1;"><label>Giá cũ</label><input type="number" name="gia_cu" id="edit_giacu"></div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;"><label>Mã tham chiếu (Ref)</label><input type="text" name="so_tham_chieu" id="edit_ref" required></div>
                    <div class="form-group" style="flex: 1;"><label>Thương hiệu</label>
                        <select name="id_thuong_hieu" id="edit_thuonghieu" required>
                            <?php foreach($brands as $b): ?><option value="<?php echo $b['id']; ?>"><?php echo $b['ten_thuong_hieu']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Số lượng kho</label><input type="number" name="ton_kho" id="edit_kho" min="0" required></div>
                <div class="form-group"><label>Ảnh mới (Bỏ trống nếu giữ ảnh cũ)</label><input type="file" name="anh_san_pham" accept="image/*"></div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeModals()">Hủy bỏ</button>
                    <button type="submit" name="edit_product" class="btn-update"><i class="fa fa-wrench"></i> Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="glass-modal">
        <div class="glass-modal-content delete-modal-content">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h3>Cảnh báo xóa!</h3>
            <p>Bạn có chắc chắn muốn xóa sản phẩm này khỏi hệ thống? Hành động này không thể hoàn tác.</p>
            <form action="products.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="delete_product_id" id="delete_id">
                <button type="submit" class="btn-danger">Xác nhận Xóa</button>
                <button type="button" class="btn-cancel" style="width: auto;" onclick="closeModals()">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
        function openEditModal(id, ten, gia, giacu, ref, thuonghieu, kho) {
            document.getElementById('edit_id').value = id; document.getElementById('edit_ten').value = ten; document.getElementById('edit_gia').value = gia; document.getElementById('edit_giacu').value = giacu; document.getElementById('edit_ref').value = ref; document.getElementById('edit_thuonghieu').value = thuonghieu; document.getElementById('edit_kho').value = kho;
            document.getElementById('editModal').style.display = 'flex';
        }
        function openDeleteModal(id) { document.getElementById('delete_id').value = id; document.getElementById('deleteModal').style.display = 'flex'; }
        function closeModals() { document.getElementById('addModal').style.display = 'none'; document.getElementById('editModal').style.display = 'none'; document.getElementById('deleteModal').style.display = 'none'; }
    </script>
    
    <?php include '../thongbao.php'; ?>
</body>
</html>