<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// XỬ LÝ XÓA KHÁCH HÀNG
if (isset($_POST['delete_customer_id'])) {
    $id_xoa = (int)$_POST['delete_customer_id'];
    
    // Thực hiện lệnh xóa và bắt luôn kết quả
    if ($conn->query("DELETE FROM nguoi_dung WHERE id = $id_xoa AND vai_tro = 'khach_hang'")) {
        $_SESSION['toast_msg'] = "Đã khóa và xóa tài khoản Khách hàng thành công!";
        $_SESSION['toast_type'] = "success";
    } else {
        // Bẫy lỗi 1451: Lỗi Khóa ngoại (Khách này đã có đơn hàng)
        if ($conn->errno == 1451) {
            $_SESSION['toast_msg'] = "KHÔNG THỂ XÓA! Khách hàng này đã có đơn hàng trên hệ thống. Bạn chỉ nên khóa tài khoản thay vì xóa để giữ lại lịch sử giao dịch.";
            $_SESSION['toast_type'] = "error";
        } else {
            $_SESSION['toast_msg'] = "Lỗi hệ thống: " . $conn->error;
            $_SESSION['toast_type'] = "error";
        }
    }
    header("Location: customers.php"); 
    exit();
}

$sql_customers = "SELECT * FROM nguoi_dung WHERE vai_tro = 'khach_hang' ORDER BY id DESC";
$result_customers = $conn->query($sql_customers);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khách hàng - Admin Timeless</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.5); animation: slideIn 0.3s ease-out; text-align: center; width: 400px; max-width: 90%; }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .glass-modal-content i { font-size: 60px; color: #d9534f; margin-bottom: 15px; } .btn-danger { background: #d9534f; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px; } .btn-cancel { background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header class="header"><h3>Quản lý Khách hàng</h3></header>
        <main class="content">
            <div class="card-header" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3>Danh sách khách hàng</h3>
            </div>
            <table class="styled-table">
                <thead>
                    <tr><th>Mã KH</th><th>Họ tên</th><th>Email</th><th>Số điện thoại</th><th>Địa chỉ</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if($result_customers && $result_customers->num_rows > 0): ?>
                        <?php while($row = $result_customers->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#KH<?php echo $row['id']; ?></strong></td>
                            <td><?php echo $row['ho_ten']; ?></td>
                            <td><?php echo !empty($row['email']) ? $row['email'] : 'Chưa có'; ?></td>
                            <td><?php echo $row['so_dien_thoai']; ?></td>
                            <td><?php echo !empty($row['dia_chi']) ? $row['dia_chi'] : 'Chưa cập nhật'; ?></td>
                            <td>
                                 <button class="action-btn btn-delete" onclick="openDeleteModal(<?php echo $row['id']; ?>)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">Chưa có khách hàng nào đăng ký</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="deleteModal" class="glass-modal">
        <div class="glass-modal-content">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h3>Cảnh báo xóa tài khoản!</h3>
            <p>Xóa tài khoản này đồng nghĩa với việc khách hàng không thể đăng nhập được nữa. Bạn có chắc chắn?</p>
            <form action="customers.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="delete_customer_id" id="delete_id">
                <button type="submit" class="btn-danger">Khóa & Xóa Tài Khoản</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(id) { document.getElementById('delete_id').value = id; document.getElementById('deleteModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('deleteModal').style.display = 'none'; }
    </script>
    
    <?php include '../thongbao.php'; ?>
</body>
</html>