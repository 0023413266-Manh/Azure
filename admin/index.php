<?php
session_start();
include 'connect.php';

// Kiem tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy các con số thống kê
$tong_khach_hang = $conn->query("SELECT COUNT(*) FROM nguoi_dung WHERE vai_tro = 'khach_hang'")->fetch_row()[0] ?? 0;
$tong_san_pham = $conn->query("SELECT COUNT(*) FROM san_pham")->fetch_row()[0] ?? 0;

// ĐÃ SỬA: Đếm cả những đơn bị trống (NULL hoặc rỗng) do lỗi test lúc trước
$don_hang_moi = $conn->query("SELECT COUNT(*) FROM don_hang WHERE trang_thai = 'Chờ xác nhận' OR trang_thai = 'Chờ duyệt' OR trang_thai IS NULL OR trang_thai = ''")->fetch_row()[0] ?? 0;

$doanh_thu = $conn->query("SELECT SUM(tong_tien) FROM don_hang WHERE trang_thai = 'Đã giao'")->fetch_row()[0] ?? 0;

// Lấy 5 đơn hàng mới nhất VÀ NỐI BẢNG ĐỂ LẤY TÊN KHÁCH HÀNG (ho_ten)
$sql_recent_orders = "SELECT d.*, n.ho_ten FROM don_hang d LEFT JOIN nguoi_dung n ON d.id_nguoi_dung = n.id ORDER BY d.id DESC LIMIT 5";
$recent_orders = $conn->query($sql_recent_orders);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị Admin - Timeless</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'sidebar.php'; ?> 

    <div class="main-content">
        
        <header class="header">
            <h3>Tổng quan</h3>
            <div class="admin-user">
                <span>Xin chào, <b><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></b></span>
                <i class="fa-solid fa-circle-user" style="font-size: 30px; vertical-align: middle; margin-left: 10px;"></i>
            </div>
        </header>

        <main class="content">
            
            <div class="cards-grid">
                <div class="card-single">
                    <div>
                        <span>Tổng khách hàng</span>
                        <h1><?php echo $tong_khach_hang; ?></h1>
                    </div>
                    <i class="fa-solid fa-users card-icon"></i>
                </div>
                <div class="card-single">
                    <div>
                        <span>Tổng sản phẩm</span>
                        <h1><?php echo $tong_san_pham; ?></h1>
                    </div>
                    <i class="fa-solid fa-box card-icon"></i>
                </div>
                <div class="card-single">
                    <div>
                        <span>Đơn hàng mới</span>
                        <h1><?php echo $don_hang_moi; ?></h1>
                    </div>
                    <i class="fa-solid fa-cart-shopping card-icon"></i>
                </div>
                <div class="card-single">
                    <div>
                        <span>Doanh thu</span>
                        <h1 style="color: #10b981;"><?php echo number_format($doanh_thu, 0, ',', '.'); ?>đ</h1>
                    </div>
                    <i class="fa-solid fa-sack-dollar card-icon"></i>
                </div>
            </div>

            <div class="recent-grid">
                <div class="card-header">
                    <h3>Đơn hàng mới nhất</h3>
                    <a href="orders.php" class="btn">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                
                <table width="100%" style="border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <th style="padding: 10px;">Mã ĐH</th>
                            <th style="padding: 10px;">Khách hàng</th>
                            <th style="padding: 10px;">Trạng thái</th>
                            <th style="padding: 10px;">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($recent_orders && $recent_orders->num_rows > 0): ?>
                            <?php while($order = $recent_orders->fetch_assoc()): 
                                // Nếu đơn hàng test bị trống, tự động gán là "Chờ xác nhận"
                                $trang_thai_hien_thi = !empty($order['trang_thai']) ? $order['trang_thai'] : 'Chờ xác nhận';
                                
                                $color = 'orange';
                                if($trang_thai_hien_thi == 'Đã giao') $color = 'green';
                                if($trang_thai_hien_thi == 'Đã hủy') $color = 'red';
                            ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 10px; font-weight: bold;">#DH<?php echo $order['id']; ?></td>
                                <td style="padding: 10px;"><?php echo isset($order['ho_ten']) ? $order['ho_ten'] : 'Khách vãng lai'; ?></td>
                                <td style="padding: 10px;"><span style="color: <?php echo $color; ?>; font-weight: bold; background: #fef9f1; padding: 4px 10px; border-radius: 4px;"><?php echo $trang_thai_hien_thi; ?></span></td>
                                <td style="padding: 10px; font-weight: bold; color: #d9534f;"><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>đ</td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="padding: 15px; text-align: center; color: #888;">Chưa có đơn hàng nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>