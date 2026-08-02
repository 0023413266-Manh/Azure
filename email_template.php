<?php
function getEmailHtml($ho_ten, $id_don_hang, $logo_base64) {
    $ten = htmlspecialchars($ho_ten);
    ob_start();
?>
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;">
  <!-- HEADER -->
  <div style="background:#1a1a1a;padding:25px;text-align:center;">
    <img src="<?php echo $logo_base64; ?>" width="55" height="55" alt="Logo" style="vertical-align:middle;margin-right:10px;">
    <span style="font-size:24px;font-weight:bold;color:#d4af37;letter-spacing:3px;vertical-align:middle;">TIMELESS</span><br>
    <span style="color:#888;font-size:11px;letter-spacing:2px;">ĐỒNG HỒ CHÍNH HÃNG NHẬP KHẨU</span>
  </div>
  <!-- BANNER -->
  <div style="background:#d4af37;padding:15px;text-align:center;">
    <strong style="color:#1a1a1a;font-size:15px;">✅ ĐƠN HÀNG ĐÃ ĐƯỢC XÁC NHẬN</strong>
  </div>
  <!-- NỘI DUNG -->
  <div style="padding:30px;background:#fff;">
    <p style="font-size:15px;">Xin chào <strong style="color:#d4af37;"><?php echo $ten; ?></strong>,</p>
    <p style="color:#555;font-size:13px;line-height:1.7;">
      Cảm ơn bạn đã tin tưởng mua sắm tại <strong>Timeless Watch Store</strong>.<br>
      Đơn hàng đã được ghi nhận và đang được xử lý.
    </p>
    <!-- CHI TIẾT ĐƠN HÀNG -->
    <div style="background:#f9f6ee;border-left:4px solid #d4af37;border-radius:6px;padding:18px 20px;margin:20px 0;">
      <p style="margin:0 0 10px;font-size:11px;color:#999;text-transform:uppercase;letter-spacing:1px;">Chi tiết đơn hàng</p>
      <table width="100%" style="font-size:13px;">
        <tr>
          <td style="color:#666;padding:5px 0;">Mã đơn hàng</td>
          <td align="right"><strong style="color:#d4af37;">#<?php echo $id_don_hang; ?></strong></td>
        </tr>
        <tr>
          <td style="color:#666;padding:5px 0;">Trạng thái</td>
          <td align="right"><span style="background:#e8f5e9;color:#2e7d32;padding:2px 10px;border-radius:20px;font-size:12px;">⏳ Chờ xác nhận</span></td>
        </tr>
        <tr>
          <td style="color:#666;padding:5px 0;">Khách hàng</td>
          <td align="right"><strong><?php echo $ten; ?></strong></td>
        </tr>
      </table>
    </div>
    <!-- CAM KẾT -->
    <table width="100%" style="text-align:center;margin:15px 0;">
      <tr>
        <td style="padding:10px;">🏆<br><span style="font-size:11px;color:#666;">Chính hãng 100%</span></td>
        <td style="padding:10px;border-left:1px solid #eee;border-right:1px solid #eee;">🚚<br><span style="font-size:11px;color:#666;">Giao hàng toàn quốc</span></td>
        <td style="padding:10px;">🛡️<br><span style="font-size:11px;color:#666;">Bảo hành chính hãng</span></td>
      </tr>
    </table>
    <p style="font-size:12px;color:#aaa;border-top:1px solid #eee;padding-top:15px;">
      Mọi thắc mắc vui lòng liên hệ bộ phận hỗ trợ của shop.
    </p>
  </div>
  <!-- FOOTER -->
  <div style="background:#1a1a1a;padding:20px 30px;">
    <p style="margin:0 0 4px;font-size:13px;font-weight:bold;color:#d4af37;letter-spacing:2px;">TIMELESS WATCH STORE</p>
    <p style="margin:0 0 3px;font-size:12px;color:#aaa;">📍 03-05 Pasteur, P. Nguyễn Thái Bình, Quận 1, TPHCM</p>
    <p style="margin:0 0 10px;font-size:12px;color:#aaa;">📞 0825549816</p>
    <p style="margin:0;font-size:11px;color:#555;">© 2026 Timeless Watch Store. Email này được gửi tự động.</p>
  </div>
</div>
<?php
    return ob_get_clean();
}
?>