<?php
// Lắng nghe thông báo từ hệ thống PHP (Đăng nhập, Đăng ký...)
$toast_msg = '';
$toast_type = 'success'; 
$toast_icon = 'fa-circle-check';
$toast_color = '#b58b5a'; // Vàng gold mặc định

if (isset($_SESSION['toast_msg'])) {
    $toast_msg = $_SESSION['toast_msg'];
    $toast_type = isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : 'success';
    
    // Nếu là thông báo LỖI -> Chuyển sang Đỏ
    if ($toast_type == 'error') {
        $toast_icon = 'fa-circle-xmark';
        $toast_color = '#d9534f'; 
    } 
    // Xóa bộ nhớ sau khi lấy để F5 không bị hiện lại
    unset($_SESSION['toast_msg']);
    unset($_SESSION['toast_type']);
}
?>

<style>
    #prism-toast.toast-show {
        bottom: 40px !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    @keyframes lightSweep {
        0% { transform: translateX(-100%) skewX(-45deg); opacity: 1; }
        10% { transform: translateX(-100%) skewX(-45deg); opacity: 1; }
        90% { transform: translateX(100%) skewX(-45deg); opacity: 0.8; }
        100% { transform: translateX(100%) skewX(-45deg); opacity: 0; }
    }
</style>

<div id="prism-toast" style="position: fixed; bottom: -100px; right: 30px; background: rgba(15, 15, 15, 0.7); backdrop-filter: blur(15px) saturate(180%); -webkit-backdrop-filter: blur(15px) saturate(180%); color: #fff; padding: 18px 28px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 15px 35px rgba(0,0,0,0.3); font-size: 16px; font-weight: 500; z-index: 9999999; display: flex; align-items: center; gap: 12px; transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); opacity: 0; pointer-events: none; overflow: hidden;">
    <i id="toast-icon" class="fa-solid fa-circle-check" style="font-size: 22px;"></i>
    <span id="toast-text" style="color: #fff; letter-spacing: 0.3px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"></span>
    
    <div class="light-bar-container" style="position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background: rgba(0, 0, 0, 0.5); border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; overflow: hidden;">
        <div id="light-bar" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; animation: none;"></div>
    </div>
</div>

<script>
    // Hàm hiển thị thông báo
    function showGlassPrismToast(message, iconClass, color) {
        const toast = document.getElementById('prism-toast');
        const lightBar = document.getElementById('light-bar');
        const toastIcon = document.getElementById('toast-icon');
        const toastText = document.getElementById('toast-text');

        toastText.innerText = message;
        toastIcon.className = 'fa-solid ' + iconClass;
        toastIcon.style.color = color;
        lightBar.style.background = `linear-gradient(90deg, transparent, ${color}, ${color}, transparent)`;

        toast.classList.add('toast-show');
        lightBar.style.animation = 'none';
        void lightBar.offsetWidth; 
        lightBar.style.animation = 'lightSweep 5s ease-out forwards';
        
        setTimeout(() => { toast.classList.remove('toast-show'); }, 5500);
    }

    // Dùng cho nút Thêm vào giỏ hàng (Gọi bằng Javascript Fetch)
function addToCartSilent(productId) {
        fetch('../cart.php?action=add&id=' + productId + '&ajax=1')
        .then(response => response.text())
        .then(data => {
            if(data.trim() === 'success') {
                showGlassPrismToast('Ting! Đã thêm đồng hồ vào giỏ hàng.', 'fa-cart-plus', '#b58b5a');
            } else if(data.trim() === 'not_logged_in') {
                showGlassPrismToast('Vui lòng đăng nhập để thêm vào giỏ!', 'fa-triangle-exclamation', '#d9534f');
                setTimeout(() => { window.location.href = '../login.php'; }, 2000);
            } else if(data.includes('out_of_stock')) {
                // BẮT LỖI VƯỢT TỒN KHO TỪ CART.PHP
                let maxQty = data.split('|')[1];
                showGlassPrismToast('Rất tiếc! Kho chỉ còn tối đa ' + maxQty + ' sản phẩm.', 'fa-circle-xmark', '#d9534f');
            }
        }).catch(error => { console.error('Lỗi:', error); });
    }

    // Kích hoạt thông báo từ PHP
    <?php if ($toast_msg != ""): ?>
        window.addEventListener('DOMContentLoaded', (event) => {
            var msg = "<?php echo $toast_msg; ?>";
            var icon = "<?php echo $toast_icon; ?>";
            var color = "<?php echo $toast_color; ?>";
            showGlassPrismToast(msg, icon, color);
        });
    <?php endif; ?>
</script>