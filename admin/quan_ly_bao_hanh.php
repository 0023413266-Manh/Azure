<?php
session_start();
include 'connect.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// XỬ LÝ CẬP NHẬT TRẠNG THÁI BẢO HÀNH
if (isset($_POST['btn_update_status'])) {
    $id_bao_hanh = (int)$_POST['bao_hanh_id'];
    $trang_thai = trim($_POST['trang_thai']);

    $stmt = $conn->prepare("UPDATE yeu_cau_bao_hanh SET trang_thai = ? WHERE id = ?");
    $stmt->bind_param("si", $trang_thai, $id_bao_hanh);

    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Đã cập nhật trạng thái đơn bảo hành #BH$id_bao_hanh!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Lỗi hệ thống: " . $conn->error;
        $_SESSION['toast_type'] = "error";
    }
    $stmt->close();
    header("Location: quan_ly_bao_hanh.php");
    exit();
}

// XỬ LÝ XÓA ĐƠN BẢO HÀNH
if (isset($_POST['delete_bao_hanh_id'])) {
    $id_xoa = (int)$_POST['delete_bao_hanh_id'];

    $stmt = $conn->prepare("DELETE FROM yeu_cau_bao_hanh WHERE id = ?");
    $stmt->bind_param("i", $id_xoa);

    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Đã xóa hồ sơ bảo hành #BH$id_xoa thành công!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Lỗi hệ thống khi xóa: " . $conn->error;
        $_SESSION['toast_type'] = "error";
    }
    $stmt->close();
    header("Location: quan_ly_bao_hanh.php");
    exit();
}

// LẤY DANH SÁCH YÊU CẦU BẢO HÀNH
$sql_baohanh = "SELECT * FROM yeu_cau_bao_hanh ORDER BY id DESC";
$result_baohanh = $conn->query($sql_baohanh);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bảo hành - Admin Timeless</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Glass Modal Style Đồng Bộ */
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.5); animation: slideIn 0.3s ease-out; text-align: center; width: 420px; max-width: 90%; }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .glass-modal-content i { font-size: 60px; color: #d9534f; margin-bottom: 15px; } 
        .btn-danger { background: #d9534f; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px; } 
        .btn-cancel { background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        
        /* Style riêng cho bảng trạng thái */
        .status-select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }
        .btn-save-status {
            background: #b58b5a;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-save-status:hover { background: #966f42; }
        
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .st-pending { background: #fff3cd; color: #856404; }
        .st-fixing { background: #cce5ff; color: #004085; }
        .st-completed { background: #d4edda; color: #155724; }
        .st-rejected { background: #f8d7da; color: #721c24; }

        /* Thumb image style */
        .img-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: 0.2s;
        }
        .img-thumb:hover { transform: scale(1.08); border-color: #b58b5a; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header class="header"><h3>Quản lý Bảo hành & Sửa chữa</h3></header>
        <main class="content">
            <div class="card-header" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3>Danh sách yêu cầu tiếp nhận</h3>
            </div>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Mã Đơn BH</th>
                        <th>Khách hàng</th>
                        <th>Mã Đơn hàng</th>
                        <th>Hình ảnh đính kèm</th>
                        <th>Mô tả sự cố</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result_baohanh && $result_baohanh->num_rows > 0): ?>
                        <?php while($row = $result_baohanh->fetch_assoc()): ?>
                        <?php 
                            $st = $row['trang_thai'];
                            $badge_class = 'st-pending';
                            if ($st == 'Đang sửa chữa') $badge_class = 'st-fixing';
                            elseif ($st == 'Hoàn tất') $badge_class = 'st-completed';
                            elseif ($st == 'Từ chối / Hủy') $badge_class = 'st-rejected';

                            // Xử lý đường dẫn ảnh xem ở Admin
                            $img_src = '';
                            if (!empty($row['hinh_anh'])) {
                                $img_src = (strpos($row['hinh_anh'], 'uploads/') === 0) ? '../' . $row['hinh_anh'] : $row['hinh_anh'];
                            }
                        ?>
                        <tr>
                            <td><strong>#BH<?php echo $row['id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['ho_ten']); ?></strong><br>
                                <small style="color: #666;"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($row['so_dien_thoai']); ?></small>
                            </td>
                            <td>
                                <b>Đơn hàng #<?php echo !empty($row['ma_dong_ho']) ? htmlspecialchars($row['ma_dong_ho']) : 'N/A'; ?></b>
                            </td>
                            <td style="text-align: center;">
                                <?php if(!empty($img_src)): ?>
                                    <img src="<?php echo htmlspecialchars($img_src); ?>" class="img-thumb" onclick="openImageModal('<?php echo htmlspecialchars($img_src); ?>')" alt="Ảnh lỗi">
                                <?php else: ?>
                                    <span style="color: #999; font-size: 12px;">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 220px; font-size: 13px;">
                                <?php echo nl2br(htmlspecialchars($row['mo_ta_loi'])); ?>
                            </td>
                            <td>
                                <span class="badge-status <?php echo $badge_class; ?>"><?php echo $st; ?></span>
                            </td>
                            <td>
                                <form action="quan_ly_bao_hanh.php" method="POST" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="bao_hanh_id" value="<?php echo $row['id']; ?>">
                                    <select name="trang_thai" class="status-select">
                                        <option value="Đang chờ" <?php if($st=='Đang chờ' || $st=='Đang chờ xử lý') echo 'selected'; ?>>Đang chờ</option>
                                        <option value="Đang sửa chữa" <?php if($st=='Đang sửa chữa') echo 'selected'; ?>>Đang sửa</option>
                                        <option value="Hoàn tất" <?php if($st=='Hoàn tất') echo 'selected'; ?>>Hoàn tất</option>
                                        <option value="Từ chối / Hủy" <?php if($st=='Từ chối / Hủy') echo 'selected'; ?>>Từ chối</option>
                                    </select>
                                    <button type="submit" name="btn_update_status" class="btn-save-status" title="Lưu trạng thái"><i class="fa-solid fa-floppy-disk"></i></button>
                                </form>
                            </td>
                            <td>
                                <button class="action-btn btn-delete" onclick="openDeleteModal(<?php echo $row['id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center;">Chưa có yêu cầu bảo hành nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- MODAL PHÓNG TO HÌNH ẢNH -->
    <div id="imageModal" class="glass-modal" onclick="closeImageModal()">
        <div style="position: relative; max-width: 80%; max-height: 80%;">
            <img id="imgFull" src="" style="width: 100%; max-height: 80vh; object-fit: contain; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        </div>
    </div>

    <!-- MODAL CẢNH BÁO XÓA GLASSMORPHISM -->
    <div id="deleteModal" class="glass-modal">
        <div class="glass-modal-content">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h3>Cảnh báo xóa yêu cầu!</h3>
            <p>Xóa hồ sơ bảo hành này sẽ làm mất toàn bộ thông tin tiến độ sửa chữa. Bạn có chắc chắn muốn xóa?</p>
            <form action="quan_ly_bao_hanh.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="delete_bao_hanh_id" id="delete_id">
                <button type="submit" class="btn-danger">Xóa Hồ Sơ</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(id) { 
            document.getElementById('delete_id').value = id; 
            document.getElementById('deleteModal').style.display = 'flex'; 
        }
        function closeModal() { 
            document.getElementById('deleteModal').style.display = 'none'; 
        }
        function openImageModal(src) {
            document.getElementById('imgFull').src = src;
            document.getElementById('imageModal').style.display = 'flex';
        }
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
    </script>
    
    <?php include '../thongbao.php'; ?>
</body>
</html>