<?php
session_start();
include 'connect.php';

// BẪY LỖI: CHẶN VƯỢT RÀO
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// =======================================================
// XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
// =======================================================
if (isset($_POST['update_status'])) {
    $id_dh = (int)$_POST['order_id'];
    $trang_thai_moi = $conn->real_escape_string($_POST['trang_thai']);
    
    // BƯỚC A: Lấy trạng thái hiện tại của đơn hàng trước khi cập nhật
    $order_info = $conn->query("SELECT trang_thai FROM don_hang WHERE id = $id_dh")->fetch_assoc();
    $trang_thai_cu = $order_info['trang_thai'];

    // BƯỚC B: Nếu trạng thái mới là 'Đã hủy' và trước đó chưa hủy
    if ($trang_thai_moi == 'Đã hủy' && $trang_thai_cu != 'Đã hủy') {
        // Lấy danh sách sản phẩm và số lượng trong đơn hàng
        $items = $conn->query("SELECT id_san_pham, so_luong FROM chi_tiet_don_hang WHERE id_don_hang = $id_dh");
        while ($item = $items->fetch_assoc()) {
            $id_sp = $item['id_san_pham'];
            $qty = $item['so_luong'];
            // Cộng lại vào kho
            $conn->query("UPDATE san_pham SET ton_kho = ton_kho + $qty WHERE id = $id_sp");
        }
    }

    // BƯỚC C: Cập nhật trạng thái đơn hàng
    $sql_update = "UPDATE don_hang SET trang_thai = '$trang_thai_moi' WHERE id = $id_dh";
    
    if ($conn->query($sql_update) === TRUE) {
        $_SESSION['toast_msg'] = "Đã cập nhật Đơn hàng #$id_dh thành: $trang_thai_moi";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "LỖI DATABASE: " . $conn->error;
        $_SESSION['toast_type'] = "error";
    }
    header("Location: orders.php");
    exit();
}

// LẤY DỮ LIỆU ĐƠN HÀNG (Tách ID an toàn bằng ma_don_hang)
$sql_orders = "SELECT d.id AS ma_don_hang, d.ngay_dat, d.tong_tien, d.trang_thai, n.ho_ten 
               FROM don_hang d 
               LEFT JOIN nguoi_dung n ON d.id_nguoi_dung = n.id 
               ORDER BY d.id DESC";
$result_orders = $conn->query($sql_orders);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đơn hàng - Admin Timeless</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .status-cho-xac-nhan { background-color: #fff3cd; color: #856404; }
        .status-dang-giao { background-color: #cce5ff; color: #004085; }
        .status-da-giao { background-color: #d4edda; color: #155724; }
        .status-da-huy { background-color: #f8d7da; color: #721c24; }
        
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; width: 400px; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
        .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; outline: none;}
        .form-group select:focus { border-color: #b58b5a; }
        .btn-update { background: #b58b5a; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 15px; font-size: 15px;}
        .btn-cancel { background: #ccc; color: #333; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; font-size: 15px;}
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <header class="header"><h3>Quản lý Đơn hàng</h3></header>
        <main class="content">
            <div class="recent-grid">
                <table class="styled-table">
                    <thead>
                        <tr><th>Mã ĐH</th><th>Khách hàng</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php if($result_orders && $result_orders->num_rows > 0): ?>
                            <?php while($row = $result_orders->fetch_assoc()): 
                                $tt = !empty($row['trang_thai']) ? $row['trang_thai'] : 'Chờ xác nhận';
                                $badge_class = 'status-cho-xac-nhan';
                                if($tt == 'Đang giao') $badge_class = 'status-dang-giao';
                                if($tt == 'Đã giao') $badge_class = 'status-da-giao';
                                if($tt == 'Đã hủy') $badge_class = 'status-da-huy';
                            ?>
                            <tr>
                                <td><strong>#DH<?php echo $row['ma_don_hang']; ?></strong></td>
                                <td><?php echo isset($row['ho_ten']) ? $row['ho_ten'] : 'Khách vãng lai'; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_dat'] ?? date('Y-m-d H:i:s'))); ?></td>
                                <td style="font-weight: bold; color: #d9534f;"><?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ</td>
                                <td><span class="status-badge <?php echo $badge_class; ?>"><?php echo $tt; ?></span></td>
                                <td>
                                    <button type="button" class="action-btn" style="background:#6dabea; border: none; cursor: pointer;" onclick="openStatusModal(<?php echo $row['ma_don_hang']; ?>, '<?php echo $tt; ?>')">
                                        <i class="fa-solid fa-pen"></i> Cập nhật
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">Chưa có đơn hàng nào</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="statusModal" class="glass-modal">
        <div class="glass-modal-content">
            <h3 style="margin-top: 0; color: #b58b5a; border-bottom: 2px solid #eee; padding-bottom: 10px;">Cập nhật Đơn hàng #<span id="display_id"></span></h3>
            <form action="orders.php" method="POST">
                <input type="hidden" name="order_id" id="status_order_id">
                <div class="form-group">
                    <label style="font-weight: bold; display: block; margin-bottom: 8px; color: #555;">Tình trạng giao hàng:</label>
                    <select name="trang_thai" id="status_select">
                        <option value="Chờ xác nhận">Chờ xác nhận</option>
                        <option value="Đang giao">Đang giao</option>
                        <option value="Đã giao">Đã giao</option>
                        <option value="Đã hủy">Đã hủy</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn-update"><i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi</button>
                <button type="button" class="btn-cancel" onclick="document.getElementById('statusModal').style.display='none'">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <script>
        function openStatusModal(id, currentStatus) {
            document.getElementById('status_order_id').value = id;
            document.getElementById('display_id').innerText = id;
            document.getElementById('status_select').value = currentStatus;
            document.getElementById('statusModal').style.display = 'flex';
        }
    </script>

    <?php include '../thongbao.php'; ?>
</body>
</html>