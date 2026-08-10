<!-- CHÂN TRANG (FOOTER) CHUNG -->
<footer style="text-align: center; padding: 30px; background: #111; color: #888; margin-top: 50px; font-size: 13px;">
    <p>&copy; <?= date('Y') ?> TIMELESS WATCHES. All rights reserved.</p>
</footer>

<?php
// BẮT TOÀN BỘ HTML CỦA TRANG VÀ CHO AZURE DỊCH
if (isset($current_lang) && $current_lang !== 'vi') {
    $html_content = ob_get_clean(); // Lấy HTML từ ob_start() ở header
    
    if (function_exists('azureTranslateHTML')) {
        echo azureTranslateHTML($html_content, $current_lang);
    } else {
        echo $html_content;
    }
}
?>
</body>
</html>