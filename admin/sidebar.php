<?php
// Thuật toán lấy tên file hiện tại (vd: index.php, orders.php...)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="brand">
        <a href="index.php" style="display: flex; align-items: center; justify-content: center; gap: 10px; color: #fff; text-decoration: none; width: 100%;">
            <img src="../image/logo.png" alt="Logo" style="height: 40px; object-fit: contain;">
            <span style="font-family: 'Playfair Display', serif; font-size: 18px; font-weight: bold; letter-spacing: 1px;">TIMELESS</span>
        </a>
    </div>
    <ul class="menu">
        <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><a href="index.php"><i class="fa-solid fa-gauge"></i> Tổng quan (Dashboard)</a></li>
        <li class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>"><a href="products.php"><i class="fa-solid fa-box"></i> Quản lý Sản phẩm</a></li>
        <li class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>"><a href="categories.php"><i class="fa-solid fa-list-check"></i> Quản lý Danh mục</a></li>
        <li class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>"><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Quản lý Đơn hàng</a></li>
        <li class="<?php echo $current_page == 'customers.php' ? 'active' : ''; ?>"><a href="customers.php"><i class="fa-solid fa-users"></i> Quản lý Khách hàng</a></li>
        <li class="<?php echo $current_page == 'quan_ly_bao_hanh.php' ? 'active' : ''; ?>"><a href="quan_ly_bao_hanh.php"><i class="fa-solid fa-screwdriver-wrench"></i> Quản lý Bảo hành</a></li>
        <li class="<?php echo $current_page == 'messages.php' ? 'active' : ''; ?>"><a href="messages.php"><i class="fa-solid fa-envelope-open-text"></i> Hộp thư Liên hệ</a></li>
    </ul>
    <div class="logout">
        <a href="../index.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất / Về Web</a>
    </div>
</div>

<style>
    /* CSS Khóa chặt màu sắc cho menu đang chọn */
    .sidebar .menu li.active a {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
        border-left: 4px solid #b58b5a !important;
        font-weight: bold !important;
    }
    .sidebar .menu li a:hover {
        background-color: rgba(255, 255, 255, 0.08);
    }
</style>