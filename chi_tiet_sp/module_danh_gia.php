<style>
    /* CSS cho hệ thống đánh giá sản phẩm */
    .review-section { max-width: 1200px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
    .review-title { font-size: 20px; border-bottom: 2px solid #b58b5a; padding-bottom: 10px; margin-bottom: 20px; color: #333; font-family: "Playfair Display", serif; }
    .review-summary { display: flex; gap: 30px; margin-bottom: 30px; flex-wrap: wrap; }
    .review-rating-overview { text-align: center; padding: 20px; background: #faf7f2; border-radius: 8px; min-width: 200px; }
    .review-big-rating { display: flex; align-items: baseline; justify-content: center; gap: 5px; margin-bottom: 10px; }
    .review-score { font-size: 48px; font-weight: bold; color: #b58b5a; }
    .review-out-of { font-size: 18px; color: #888; }
    .review-stars { display: flex; justify-content: center; gap: 5px; margin-bottom: 10px; }
    .review-total { font-size: 14px; color: #666; }
    .review-form { flex: 1; min-width: 300px; }
    .review-stars-input { margin-bottom: 15px; }
    .review-stars-input label { display: block; margin-bottom: 10px; font-weight: bold; color: #333; }
    .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; }
    .star-rating input { display: none; }
    .star-rating label { font-size: 30px; color: #ddd; cursor: pointer; transition: color 0.2s; }
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label { color: #f39c12; }
    .review-form textarea { width: 100%; height: 100px; padding: 15px; border: 1px solid #ccc; border-radius: 5px; resize: none; font-family: inherit; outline: none; margin-bottom: 15px; }
    .review-form textarea:focus { border-color: #b58b5a; }
    .review-file-upload { margin-bottom: 15px; }
    .review-file-upload label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
    .review-file-upload input[type="file"] { padding: 10px; border: 1px dashed #ccc; border-radius: 5px; width: 100%; }
    .image-preview { margin-top: 10px; }
    .image-preview img { max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #eee; }
    .btn-submit-review { background: #b58b5a; color: #fff; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .btn-submit-review:hover { background: #967045; }
    .review-notice { padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .review-notice i { font-size: 20px; }
    .review-notice.notice-info { background: #f0f7ff; color: #0056b3; border: 1px solid #cce5ff; }
    .review-notice.notice-warning { background: #fff5f5; color: #c92a2a; border: 1px solid #ffc9c9; }
    .review-notice.notice-success { background: #f4fbf7; color: #2b8a3e; border: 1px solid #b2f2bb; }
    .review-notice a { color: inherit; font-weight: bold; text-decoration: underline; }
    .review-success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
    .review-error-msg { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
    .review-list { display: flex; flex-direction: column; gap: 20px; }
    .review-item { display: flex; gap: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .review-item-header { flex: 1; }
    .review-user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .review-user-avatar { width: 40px; height: 40px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #b58b5a; font-size: 18px; }
    .review-user-details { display: flex; flex-direction: column; }
    .review-author { font-weight: bold; color: #333; }
    .review-date { font-size: 12px; color: #888; }
    .review-item-stars { display: flex; gap: 3px; margin-bottom: 10px; }
    .review-item-content { flex: 1; }
    .review-item-content p { color: #555; line-height: 1.6; margin-bottom: 10px; }
    .review-item-image img { max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #eee; cursor: pointer; transition: transform 0.2s; }
    .review-item-image img:hover { transform: scale(1.05); }
    .review-no-data { text-align: center; padding: 40px; color: #888; }
    .review-no-data i { font-size: 40px; color: #ddd; margin-bottom: 15px; display: block; }
    
    .star-rating-options { display: flex; gap: 10px; flex-wrap: wrap; }
    .star-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 8px 16px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fafafa; cursor: pointer; transition: all 0.2s ease; user-select: none; min-width: 90px; }
    .star-btn .star-num { font-size: 14px; font-weight: 600; color: #f39c12; }
    .star-btn .star-text { font-size: 12px; color: #666; margin-top: 2px; }
    .star-btn:hover { border-color: #f39c12; background-color: #fff9f0; }
    input[name="so_sao"]:checked + .star-btn { border-color: #f39c12; background-color: #fff8e7; box-shadow: 0 0 0 1px #f39c12; }
    input[name="so_sao"]:checked + .star-btn .star-text { color: #d35400; font-weight: 600; }
</style>

<!-- Đánh Giá Sản Phẩm -->
<div class="review-section" id="danh-gia">
    <h3 class="review-title"><i class="fa-solid fa-star"></i> Đánh Giá Sản Phẩm</h3>
    
    <?php
    // Xác định ID sản phẩm chung ($sp_id)
    if (!isset($sp_id) || empty($sp_id)) {
        if (isset($row['id'])) {
            $sp_id = (int)$row['id'];
        } elseif (isset($id)) {
            $sp_id = (int)$id;
        } else {
            $sp_id = 0;
        }
    } else {
        $sp_id = (int)$sp_id;
    }

    // Xử lý submit đánh giá
    if (isset($_POST["submit_review"])) {
        if (isset($_SESSION["user_id"])) {
            $uid = (int)$_SESSION["user_id"];
            $so_sao = (int)$_POST["so_sao"];
            $noi_dung = $conn->real_escape_string(trim($_POST["noi_dung"]));
            
            // Kiểm tra đã đánh giá chưa
            $check = $conn->query("SELECT id FROM danh_gia WHERE id_nguoi_dung = $uid AND id_san_pham = $sp_id LIMIT 1");
            if ($check && $check->num_rows > 0) {
                $review_error = "Bạn đã đánh giá sản phẩm này rồi!";
            } else {
                // Xử lý upload ảnh
                $anh_danh_gia = null;
                if (isset($_FILES["anh_danh_gia"]) && $_FILES["anh_danh_gia"]["error"] === UPLOAD_ERR_OK) {
                    $allowed = ["image/jpeg", "image/png", "image/gif", "image/webp"];
                    $file_type = mime_content_type($_FILES["anh_danh_gia"]["tmp_name"]);
                    if (in_array($file_type, $allowed)) {
                        $target_dir = __DIR__ . "/../image/review_images/";
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }
                        
                        $ext = pathinfo($_FILES["anh_danh_gia"]["name"], PATHINFO_EXTENSION);
                        $filename = "review_" . $uid . "_" . $sp_id . "_" . time() . "." . $ext;
                        $upload_path = $target_dir . $filename;
                        
                        if (move_uploaded_file($_FILES["anh_danh_gia"]["tmp_name"], $upload_path)) {
                            $anh_danh_gia = "image/review_images/" . $filename;
                        }
                    }
                }
                
                // Lưu vào database
                $anh_val = $anh_danh_gia ? "'" . $conn->real_escape_string($anh_danh_gia) . "'" : "NULL";
                $conn->query("INSERT INTO danh_gia (id_san_pham, id_nguoi_dung, so_sao, noi_dung, anh_danh_gia) VALUES ($sp_id, $uid, $so_sao, '" . $noi_dung . "', $anh_val)");
                $review_success = "Cảm ơn bạn đã đánh giá sản phẩm!";
            }
        } else {
            $review_error = "Vui lòng đăng nhập để đánh giá sản phẩm.";
        }
    }
    
    // Lấy thông tin đánh giá
    $sp_id_hien_tai = $sp_id;
    $sql_avg = "SELECT AVG(so_sao) as avg_sao, COUNT(id) as tong_danh_gia FROM danh_gia WHERE id_san_pham = $sp_id_hien_tai";
    $res_avg = $conn->query($sql_avg);
    $row_avg = $res_avg ? $res_avg->fetch_assoc() : null;
    $diem_tb = $row_avg ? round($row_avg["avg_sao"], 1) : 0;
    $tong_dg = $row_avg ? (int)$row_avg["tong_danh_gia"] : 0;
    
    // Kiểm tra quyền đánh giá
    $can_review = false;
    $review_reason = "";
    if (isset($_SESSION["user_id"])) {
        $uid_review = (int)$_SESSION["user_id"];
        $check_da_dg = $conn->query("SELECT id FROM danh_gia WHERE id_nguoi_dung = $uid_review AND id_san_pham = $sp_id_hien_tai LIMIT 1");
        if ($check_da_dg && $check_da_dg->num_rows > 0) {
            $review_reason = "already_reviewed";
        } else {
            $sql_check_mua = "SELECT dh.id FROM don_hang dh
                              JOIN chi_tiet_don_hang ct ON ct.id_don_hang = dh.id
                              WHERE dh.id_nguoi_dung = $uid_review
                                AND ct.id_san_pham = $sp_id_hien_tai
                                AND dh.trang_thai IN ('Da giao', 'Đã giao', 'da_giao', 'Hoàn thành', 'hoan_thanh')
                              LIMIT 1";
            $res_check_mua = $conn->query($sql_check_mua);
            if ($res_check_mua && $res_check_mua->num_rows > 0) {
                $can_review = true;
            } else {
                $review_reason = "not_purchased";
            }
        }
    }
    ?>
    
    <!-- Tổng quan đánh giá -->
    <div class="review-summary">
        <div class="review-rating-overview">
            <div class="review-big-rating">
                <span class="review-score"><?php echo $diem_tb > 0 ? $diem_tb : "0"; ?></span>
                <span class="review-out-of">/5</span>
            </div>
            <div class="review-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-solid fa-star<?php echo $i <= round($diem_tb) ? "" : " fa-regular"; ?>" style="color: <?php echo $i <= round($diem_tb) ? "#f39c12" : "#ddd"; ?>;"></i>
                <?php endfor; ?>
            </div>
            <div class="review-total"><?php echo $tong_dg; ?> đánh giá</div>
        </div>
        
        <!-- Form đánh giá -->
        <?php if (isset($_SESSION["user_id"])): ?>
            <?php if ($can_review): ?>
                <?php if (isset($review_success)): ?>
                    <div class="review-success-msg"><?php echo $review_success; ?></div>
                <?php endif; ?>
                <?php if (isset($review_error)): ?>
                    <div class="review-error-msg"><?php echo $review_error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="review-form">
                    <div class="review-stars-input" style="margin-bottom: 20px; text-align: left;">
                        <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 14px;">
                            Đánh giá của bạn về sản phẩm:
                        </label>

                        <div class="star-rating-options">
                            <input type="radio" name="so_sao" value="5" id="star5" required hidden>
                            <label for="star5" class="star-btn">
                                <span class="star-num">5 <i class="fa-solid fa-star"></i></span>
                                <span class="star-text">Rất tốt</span>
                            </label>

                            <input type="radio" name="so_sao" value="4" id="star4" hidden>
                            <label for="star4" class="star-btn">
                                <span class="star-num">4 <i class="fa-solid fa-star"></i></span>
                                <span class="star-text">Tốt</span>
                            </label>

                            <input type="radio" name="so_sao" value="3" id="star3" hidden>
                            <label for="star3" class="star-btn">
                                <span class="star-num">3 <i class="fa-solid fa-star"></i></span>
                                <span class="star-text">Bình thường</span>
                            </label>

                            <input type="radio" name="so_sao" value="2" id="star2" hidden>
                            <label for="star2" class="star-btn">
                                <span class="star-num">2 <i class="fa-solid fa-star"></i></span>
                                <span class="star-text">Tệ</span>
                            </label>

                            <input type="radio" name="so_sao" value="1" id="star1" hidden>
                            <label for="star1" class="star-btn">
                                <span class="star-num">1 <i class="fa-solid fa-star"></i></span>
                                <span class="star-text">Rất tệ</span>
                            </label>
                        </div>
                    </div>

                    <textarea name="noi_dung" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..." required></textarea>
                    
                    <div class="review-file-upload">
                        <label for="anh_danh_gia">Thêm ảnh (tùy chọn):</label>
                        <input type="file" name="anh_danh_gia" id="anh_danh_gia" accept="image/*" onchange="previewImage(this)">
                        <div id="image-preview" class="image-preview"></div>
                    </div>
                    
                    <button type="submit" name="submit_review" class="btn-submit-review">
                        <i class="fa-solid fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </form>

            <?php elseif ($review_reason == "not_logged_in"): ?>
                <div class="review-notice notice-info">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Vui lòng <a href="../login.php">Đăng nhập</a> để đánh giá sản phẩm.</span>
                </div>
            <?php elseif ($review_reason == "not_purchased"): ?>
                <div class="review-notice notice-warning">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Chỉ khách hàng đã mua và nhận thành công sản phẩm này mới có thể gửi đánh giá.</span>
                </div>
            <?php elseif ($review_reason == "already_reviewed"): ?>
                <div class="review-notice notice-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Bạn đã đánh giá sản phẩm này. Cảm ơn bạn!</span>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="review-notice notice-info">
                <i class="fa-solid fa-circle-info"></i>
                <span>Vui lòng <a href="../login.php">Đăng nhập</a> để đánh giá sản phẩm.</span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Danh sách đánh giá đã tối ưu bố cục & ảnh -->
    <div class="review-list" style="margin-top: 25px;">
        <?php
        $sql_dg = "SELECT d.*, n.ho_ten 
                  FROM danh_gia d
                  JOIN nguoi_dung n ON d.id_nguoi_dung = n.id
                  WHERE d.id_san_pham = $sp_id_hien_tai
                  ORDER BY d.ngay_danh_gia DESC LIMIT 10";
        $res_dg = $conn->query($sql_dg);
        
        if ($res_dg && $res_dg->num_rows > 0):
            while ($dg = $res_dg->fetch_assoc()):
        ?>

        <div class="review-item" style="padding: 15px; border-bottom: 1px solid #eee; margin-bottom: 15px;">
            <!-- HÀNG 1: Avatar + Tên + Ngày tháng + Số Sao -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 35px; height: 35px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-user" style="color: #888;"></i>
                    </div>
                    <div>
                        <strong style="font-size: 14px; display: block; color: #333;"><?php echo htmlspecialchars($dg["ho_ten"]); ?></strong>
                        <span style="font-size: 12px; color: #999;"><?php echo date('d/m/Y H:i', strtotime($dg["ngay_danh_gia"])); ?></span>
                    </div>
                </div>
                <!-- Dãy sao vàng -->
                <div style="color: #ffc107; font-size: 13px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php echo ($i <= $dg["so_sao"]) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star" style="color:#ccc;"></i>'; ?>
                    <?php endfor; ?>
                </div>
            </div>
            <!-- HÀNG 2: Nội dung nhận xét -->
            <div style="margin-top: 10px; font-size: 14px; color: #444; line-height: 1.5;">
                <?php echo nl2br(htmlspecialchars($dg["noi_dung"])); ?>
            </div>
            <!-- HÀNG 3: Ảnh thực tế đính kèm (Nếu có) -->
            <?php
                $path_anh = $dg["anh_danh_gia"] ?? '';
                if (!empty($path_anh) && strpos($path_anh, 'http') !== 0) {
                    $path_anh = "../" . ltrim($path_anh, '/');
                }
            ?>
            <?php if (!empty($dg["anh_danh_gia"])): ?>
                <div style="margin-top: 10px;">
                    <img src="<?php echo $path_anh; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; cursor: pointer;" onclick="window.open(this.src)">
                </div>
            <?php endif; ?>
        </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="review-no-data" style="text-align: center; padding: 40px 20px; color: #999;">
                <i class="fa-solid fa-comment-slash" style="font-size: 35px; margin-bottom: 10px; color: #ccc;"></i>
                <p style="margin: 0;">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
            </div>
        <?php endif; ?>
    </div>
</div>
