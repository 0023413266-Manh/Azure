<?php session_start(); 
include 'admin/connect.php'; 

// =======================================================
// BẪY LỖI KHI CHƯA ĐĂNG NHẬP (ÉP BUỘC ĐĂNG NHẬP)
// =======================================================
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['ajax'])) { echo "not_logged_in"; exit(); }
    $_SESSION['toast_msg'] = "Vui lòng đăng nhập để sử dụng giỏ hàng!";
    $_SESSION['toast_type'] = "error";
    header("Location: login.php"); exit();
}

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = array(); }

// HÀM BẢO VỆ: Quét Database lấy số lượng tồn kho thực tế
function getTonKho($conn, $id) {
    $res = $conn->query("SELECT ton_kho FROM san_pham WHERE id = $id");
    return ($res && $res->num_rows > 0) ? (int)$res->fetch_assoc()['ton_kho'] : 0;
}

// 1. TÍNH NĂNG MUA NGAY
if (isset($_GET['action']) && $_GET['action'] == 'buynow' && isset($_GET['id'])) {
    $id_buy = (int)$_GET['id'];
    $ton_kho = getTonKho($conn, $id_buy);
    $current_qty = isset($_SESSION['cart'][$id_buy]) ? $_SESSION['cart'][$id_buy] : 0;
    
    // Bẫy lỗi: Vượt quá tồn kho
    if (($current_qty + 1) > $ton_kho) {
        $_SESSION['toast_msg'] = "Rất tiếc, kho chỉ còn tối đa $ton_kho sản phẩm!";
        $_SESSION['toast_type'] = "error";
        header("Location: cart.php"); exit();
    }
    
    $_SESSION['cart'][$id_buy] = $current_qty + 1; 
    echo "<form id='fast_checkout' action='checkout.php' method='POST'>
            <input type='hidden' name='selected_items' value='$id_buy'>
          </form><script>document.getElementById('fast_checkout').submit();</script>";
    exit();
}

// 2. XỬ LÝ THÊM VÀO GIỎ HÀNG
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id_add = (int)$_GET['id'];
    $ton_kho = getTonKho($conn, $id_add);
    $current_qty = isset($_SESSION['cart'][$id_add]) ? $_SESSION['cart'][$id_add] : 0;
    
    // Bẫy lỗi: Vượt quá tồn kho
    if (($current_qty + 1) > $ton_kho) {
        if (isset($_GET['ajax'])) { echo "out_of_stock|$ton_kho"; exit(); }
        $_SESSION['toast_msg'] = "Sản phẩm này chỉ còn $ton_kho chiếc. Bạn không thể thêm nữa!";
        $_SESSION['toast_type'] = "error";
    } else {
        $_SESSION['cart'][$id_add] = $current_qty + 1;
        if (isset($_GET['ajax'])) { echo "success"; exit(); }
        $_SESSION['toast_msg'] = 'Đã thêm 1 sản phẩm vào giỏ hàng.';
        $_SESSION['toast_type'] = 'success';
    }
    header("Location: cart.php"); exit();
}

// 3. XỬ LÝ XÓA KHỎI GIỎ (Giữ nguyên)
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_remove = (int)$_GET['id'];
    if (isset($_SESSION['cart'][$id_remove])) {
        unset($_SESSION['cart'][$id_remove]); 
        $_SESSION['toast_msg'] = 'Đã xóa sản phẩm khỏi giỏ hàng.';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: cart.php"); exit();
}

// 4. XỬ LÝ NÚT (+ / -) CẬP NHẬT SỐ LƯỢNG
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['id']) && isset($_GET['qty'])) {
    $id_update = (int)$_GET['id'];
    $new_qty = (int)$_GET['qty'];
    $ton_kho = getTonKho($conn, $id_update);
    
    // Bẫy lỗi: Khách cố tình bấm dấu + vượt quá kho
    if ($new_qty > $ton_kho) {
        $_SESSION['cart'][$id_update] = $ton_kho; // Tự động ép về mức tối đa cho phép
        $_SESSION['toast_msg'] = "Kho chỉ còn tối đa $ton_kho chiếc. Đã điều chỉnh lại số lượng!";
        $_SESSION['toast_type'] = "error";
    } elseif ($new_qty > 0) {
        $_SESSION['cart'][$id_update] = $new_qty; 
        $_SESSION['toast_msg'] = 'Đã cập nhật số lượng.';
        $_SESSION['toast_type'] = 'success';
    } else {
        unset($_SESSION['cart'][$id_update]); 
        $_SESSION['toast_msg'] = 'Đã xóa sản phẩm khỏi giỏ hàng.';
        $_SESSION['toast_type'] = 'error';
    }
    header("Location: cart.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng của bạn - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        .cart-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #b58b5a; }
        .cart-table th, .cart-table td { padding: 20px 15px; vertical-align: middle; }
        .cart-table th:first-child, .cart-table td:first-child { width: 40px; text-align: center; padding: 20px 5px; }
        .cart-title { font-family: 'Segoe UI', Arial, sans-serif !important; font-weight: 700; font-size: 24px; margin-bottom: 20px; }
        .qty-control { display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .qty-btn-link { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; background-color: #f1f1f1; color: #555; text-decoration: none; border: 1px solid #ddd; border-radius: 6px; font-weight: bold; font-size: 16px; }
        .qty-btn-link:hover { background-color: #e0e0e0; color: #000; }
        .qty-input { width: 45px; height: 30px; text-align: center; border: 1px solid #ddd; border-radius: 6px; outline: none; background: #fff; font-weight: 600; }
        .btn-remove { color: #d9534f; background: none; border: none; font-size: 22px; cursor: pointer; text-decoration: none; margin-left: 20px; }
        .btn-remove:hover { color: #c9302c; }
        .cart-item-link { text-decoration: none; color: inherit; transition: 0.2s; }
        .cart-item-link:hover h4 { color: #b58b5a; }
    </style>
</head>
<body>

    <div id="smart-header">
        <header class="top-header" style="justify-content: center;">
            <div class="logo">
                <a href="index.php" class="logo-link">
                    <h1>TIMELESS</h1>
                    <img src="image/logo.png" alt="Timeless Icon">
                </a>
            </div>

            <div class="user-box" style="position: absolute; right: 50px;">
                <?php 
                if(isset($_SESSION['user_id'])) {
                    $uid = $_SESSION['user_id'];
                    $get_name = $conn->query("SELECT ho_ten FROM nguoi_dung WHERE id = $uid");
                    $ten_ngan = "User";
                    if($get_name && $get_name->num_rows > 0) {
                        $row_name = $get_name->fetch_assoc();
                        $mang_ten = explode(' ', trim($row_name['ho_ten']));
                        $ten_ngan = end($mang_ten); 
                    }
                ?>
                    <a href="profile.php" style="text-decoration: none;"> 
                        <button class="btn-user" style="color: #b58b5a; font-weight: bold; border-color: #b58b5a;">
                            <?php echo $ten_ngan; ?> <i class="fa-solid fa-circle-user"></i>
                        </button>
                    </a>
                <?php } else { ?>
                    <a href="login.php" style="text-decoration: none;"> 
                        <button class="btn-user">User <i class="fa-solid fa-circle-user"></i></button>
                    </a>
                <?php } ?>
            </div>
        </header>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php">TRANG CHỦ</a></li>
                <li class="dropdown">
                    <a href="#">THƯƠNG HIỆU <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="all_rolex.php">ROLEX</a></li>
                        <li><a href="all_omega.php">OMEGA</a></li>
                        <li><a href="all_casio.php">CASIO</a></li>
                        <li><a href="all_seiko.php">SEIKO</a></li>
                        <li><a href="all_hublot.php">HUBLOT</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">SẢN PHẨM <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="Dongho_nam.php">DÀNH CHO NAM</a></li>
                        <li><a href="Dongho_nu.php">DÀNH CHO NỮ</a></li>
                    </ul>
                </li>
                <li><a href="explore.php">KHÁM PHÁ</a></li>
                <li><a href="contact.php">LIÊN HỆ</a></li>
                <li class="nav-icons">
                    <div class="search-box">
                         <form action="search.php" method="GET">
                            <input type="text" name="query" placeholder="Bạn tìm gì..." class="search-input">
                            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </div>
                    <a href="cart.php" class="icon-cart" style="color: #b58b5a;">
                        <i class="fa-solid fa-cart-shopping" style="color: #b58b5a;"></i>
                        <span class="cart-text">Giỏ hàng</span>
                     </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <div class="cart-page-container">
        <div class="cart-left">
            <h2 class="cart-title">Giỏ hàng của bạn (<?php echo count($_SESSION['cart']); ?> sản phẩm)</h2>
            
            <?php if (empty($_SESSION['cart'])): ?>
                <div style="text-align: center; padding: 50px 0;">
                    <i class="fa-solid fa-cart-arrow-down" style="font-size: 50px; color: #ccc; margin-bottom: 20px;"></i>
                    <p style="font-size: 18px; color: #666;">Giỏ hàng của bạn đang trống.</p>
                    <a href="index.php" style="display: inline-block; margin-top: 15px; padding: 10px 25px; background-color: #b58b5a; color: white; text-decoration: none; border-radius: 5px;">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>
         <table class="cart-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" class="cart-checkbox" checked></th>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th style="text-align: center;">Số lượng</th>
                        <th>Thành tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $cart_ids = array_keys($_SESSION['cart']);
                    $str_ids = implode(',', $cart_ids); 
                    
                    $sql = "SELECT * FROM san_pham WHERE id IN ($str_ids)";
                    $result = $conn->query($sql);
                    
                    while ($row = $result->fetch_assoc()):
                        $id = $row['id'];
                        $ton_kho = (int)$row['ton_kho'];
                        
                        // "SIÊU ĐỘ": Tự động ép số lượng trong giỏ bằng đúng tồn kho thực tế
                        if ($_SESSION['cart'][$id] > $ton_kho) {
                            $_SESSION['cart'][$id] = $ton_kho; 
                        }
                        $qty = $_SESSION['cart'][$id]; 
                        
                        $price = $row['gia_ban'];
                        $subtotal = $price * $qty;
                        
                        $detail_link = "#";
                        if ($row['id_thuong_hieu'] == 1) $detail_link = "chi_tiet_sp/chi_tiet_rolex.php?id=" . $id;
                        if ($row['id_thuong_hieu'] == 2) $detail_link = "chi_tiet_sp/chi_tiet_hublot.php?id=" . $id;
                        if ($row['id_thuong_hieu'] == 3) $detail_link = "chi_tiet_sp/chi_tiet_omega.php?id=" . $id;
                        if ($row['id_thuong_hieu'] == 4) $detail_link = "chi_tiet_sp/chi_tiet_casio.php?id=" . $id;
                        if ($row['id_thuong_hieu'] == 5) $detail_link = "chi_tiet_sp/chi_tiet_seiko.php?id=" . $id;
                    ?>
                    <tr class="cart-item-row" <?php if($ton_kho == 0) echo 'style="background-color: #fcfcfc;"'; ?>>
                        <td>
                            <?php if ($ton_kho > 0): ?>
                                <input type="checkbox" class="item-check cart-checkbox" value="<?php echo $id; ?>" checked onchange="calculateTotal()">
                            <?php else: ?>
                                <i class="fa-solid fa-ban" style="color: #ccc; font-size: 18px;" title="Không thể mua"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $detail_link; ?>" class="cart-item-link">
                                <div class="cart-item-info" <?php if($ton_kho == 0) echo 'style="opacity: 0.5; filter: grayscale(100%);"'; ?>>
                                    <img src="<?php echo trim($row['anh_san_pham']); ?>" alt="<?php echo $row['ten_san_pham']; ?>" onerror="this.src='image/logo.png'">
                                    <div>
                                        <h4><?php echo $row['ten_san_pham']; ?></h4>
                                        <p style="font-size: 12px; color: #888; margin-top: 4px;">Mã: <?php echo $row['so_tham_chieu']; ?></p>
                                        <?php if ($ton_kho == 0): ?>
                                            <span style="display:inline-block; margin-top:5px; color: #d9534f; font-weight: bold; font-size: 11px; background: #fde8e8; padding: 3px 8px; border-radius: 4px;">Tạm hết hàng</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td class="item-price" data-price="<?php echo $price; ?>" style="font-weight: 600; color: #b58b5a; <?php if($ton_kho == 0) echo 'opacity: 0.5;'; ?>"><?php echo number_format($price, 0, ',', '.'); ?>đ</td>
                        <td>
                            <?php if ($ton_kho > 0): ?>
                                <div class="qty-control">
                                    <a href="cart.php?action=update&id=<?php echo $id; ?>&qty=<?php echo $qty - 1; ?>" class="qty-btn-link">-</a>
                                    <input type="text" class="qty-input" value="<?php echo $qty; ?>" readonly>
                                    <a href="cart.php?action=update&id=<?php echo $id; ?>&qty=<?php echo $qty + 1; ?>" class="qty-btn-link">+</a>
                                </div>
                            <?php else: ?>
                                <span style="color: #999; font-weight: bold; font-size: 16px;">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="item-total" style="font-weight: 700; <?php if($ton_kho == 0) echo 'opacity: 0.5;'; ?>"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
                        <td>
                            <a href="cart.php?action=remove&id=<?php echo $id; ?>" class="btn-remove" title="Xóa"><i class="fa-solid fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="cart-right">
            <div class="cart-summary">
                <h3>Tóm tắt đơn hàng</h3>
                <div class="summary-row">
                    <span>Tạm tính (<span id="checked-count">0</span> sản phẩm):</span>
                    <span id="sub-total">0đ</span>
                </div>
                <div class="summary-row">
                    <span>Bảo hiểm & Vận chuyển:</span>
                    <span style="color: #28a745; font-weight: bold;">Miễn phí</span>
                </div>
                <div class="summary-total">
                    <span>TỔNG CỘNG:</span>
                    <span id="final-total">0đ</span>
                </div>
                <button class="btn-checkout" onclick="proceedToCheckout()" style="background: #b58b5a;">Tiến hành thanh toán</button>
                
                <div style="margin-top: 20px; font-size: 13px; color: #666; text-align: center;">
                    <i class="fa-solid fa-shield-halved"></i> Thanh toán an toàn & Bảo mật 100%
                </div>
            </div>
        </div>
    </div>

    <style>
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; width: 350px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .btn-modal-ok { background: #b58b5a; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 15px; width: 100%; font-size: 15px; transition: 0.3s;}
        .btn-modal-ok:hover { background: #967045; }
    </style>

    <div id="warningModal" class="glass-modal">
        <div class="glass-modal-content">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 55px; color: #d9534f; margin-bottom: 15px;"></i>
            <h3 style="margin-top: 0; margin-bottom: 10px; color: #333; font-family: 'Playfair Display', serif;">Thông báo</h3>
            <p style="color: #666; font-size: 15px; line-height: 1.5;">Vui lòng chọn ít nhất 1 sản phẩm hợp lệ để tiến hành thanh toán!</p>
            <button type="button" class="btn-modal-ok" onclick="document.getElementById('warningModal').style.display='none'">Đã hiểu</button>
        </div>
    </div>

    <script>
        function formatMoney(amount) {
            return amount.toLocaleString('vi-VN') + 'đ';
        }

        function calculateTotal() {
            const rows = document.querySelectorAll('.cart-item-row');
            let totalMoney = 0; let totalItems = 0; let allChecked = true; let hasValidItems = false;
            if(rows.length === 0) return; 

            rows.forEach(row => {
                const checkbox = row.querySelector('.item-check');
                // Nếu sản phẩm hết hàng (không có checkbox) thì bỏ qua luôn
                if (!checkbox) {
                    allChecked = false;
                    return; 
                }
                
                hasValidItems = true;
                const price = parseInt(row.querySelector('.item-price').getAttribute('data-price'));
                const qty = parseInt(row.querySelector('.qty-input').value);

                if (checkbox.checked) {
                    totalMoney += (price * qty);
                    totalItems += qty;
                } else {
                    allChecked = false;
                }
            });

            document.getElementById('checked-count').innerText = totalItems;
            document.getElementById('sub-total').innerText = formatMoney(totalMoney);
            document.getElementById('final-total').innerText = formatMoney(totalMoney);
            
            const selectAllBtn = document.getElementById('selectAll');
            if(selectAllBtn) {
                selectAllBtn.checked = hasValidItems ? allChecked : false;
            }
        }

        const selectAllBtn = document.getElementById('selectAll');
        if(selectAllBtn) {
            selectAllBtn.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.item-check');
                checkboxes.forEach(cb => { cb.checked = this.checked; });
                calculateTotal(); 
            });
        }
        calculateTotal();

        // CHUYỂN HƯỚNG THANH TOÁN (ĐÃ SỬA LỖI MODAL)
        function proceedToCheckout() {
            // Chỉ gom những ô được check (Sản phẩm hết hàng ko có ô check nên ko bị gom)
            const checkboxes = document.querySelectorAll('.item-check:checked');
            
            // Gọi Modal xịn sò thay vì Alert
            if (checkboxes.length === 0) {
                document.getElementById('warningModal').style.display = 'flex';
                return;
            }
            
            let selectedIds = [];
            checkboxes.forEach(cb => selectedIds.push(cb.value));

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_items';
            input.value = selectedIds.join(',');

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        const smartHeader = document.getElementById('smart-header');
        if (smartHeader) {
            let lastScrollTop = 0;
            window.addEventListener('scroll', function() {
                let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                if (currentScroll > lastScrollTop && currentScroll > 100) {
                    smartHeader.classList.add('header-hidden');
                } else {
                    smartHeader.classList.remove('header-hidden');
                }
                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
            });
        }
    </script>
    
    <?php include 'thongbao.php'; ?>
</body>
</html>