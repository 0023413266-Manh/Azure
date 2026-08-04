<?php
session_start();
include 'connect.php';

// BẪY LỖI: CHẶN VƯỢT RÀO
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// XỬ LÝ ĐÁNH DẤU ĐÃ ĐỌC
if (isset($_POST['mark_read_id'])) {
    $id_read = (int)$_POST['mark_read_id'];
    $conn->query("UPDATE lien_he SET trang_thai = 'Đã xem' WHERE id = $id_read");
    header("Location: messages.php"); 
    exit();
}

// XỬ LÝ XÓA TIN NHẮN
if (isset($_POST['delete_msg_id'])) {
    $id_del = (int)$_POST['delete_msg_id'];
    $conn->query("DELETE FROM lien_he WHERE id = $id_del");
    $_SESSION['toast_msg'] = "Đã xóa tin nhắn liên hệ!";
    $_SESSION['toast_type'] = "success";
    header("Location: messages.php"); 
    exit();
}

// LẤY DANH SÁCH TIN NHẮN MỚI NHẤT LÊN ĐẦU
$sql_msgs = "SELECT * FROM lien_he ORDER BY id DESC";
$result_msgs = $conn->query($sql_msgs);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hộp thư liên hệ - Admin Timeless</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* CSS CHO MODAL KÍNH (ĐỌC VÀ XÓA) */
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; width: 450px; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .msg-box { background: #f8f9fa; border-left: 4px solid #b58b5a; padding: 15px; margin: 15px 0; border-radius: 4px; font-style: italic; color: #555; white-space: pre-wrap; line-height: 1.5; }
        .btn-mark-read { background: #b58b5a; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-bottom: 10px; }
        .btn-cancel { background: #eee; color: #333; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-danger { background: #d9534f; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-bottom: 10px;}
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header class="header">
            <h3>Hộp thư Liên hệ</h3>
        </header>

        <main class="content">
            <div class="card-header">
                <h3>Danh sách lời nhắn từ khách hàng</h3>
            </div>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Nội dung tóm tắt</th>
                        <th>Ngày gửi</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result_msgs && $result_msgs->num_rows > 0): ?>
                        <?php while($row = $result_msgs->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td style="padding: 15px;">
                                <strong><?php echo $row['ho_ten']; ?></strong><br>
                                <span style="font-size: 13px; color: #666;"><i class="fa-solid fa-phone"></i> <?php echo $row['so_dien_thoai']; ?></span>
                            </td>
                            <td>
                                <span style="display: inline-block; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($row['noi_dung']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_gui'])); ?></td>
                                <td>
                                <?php if($row['trang_thai'] != 'Đã xem'): ?>
                                    <span style="background: #fde8e8; color: #d9534f; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;"><i class="fa-solid fa-circle-dot" style="font-size: 8px;"></i> MỚI</span>
                                <?php else: ?>
                                    <span style="background: #eef2ff; color: #2b6cb0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;"><i class="fa-solid fa-check-double"></i> Đã xem</span>
                                <?php endif; ?>
                                </td>
                            <td>
                                <button type="button" class="action-btn btn-edit" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['ho_ten'], ENT_QUOTES); ?>"
                                    data-phone="<?php echo htmlspecialchars($row['so_dien_thoai'], ENT_QUOTES); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>"
                                    data-msg="<?php echo htmlspecialchars($row['noi_dung'], ENT_QUOTES); ?>"
                                    data-status="<?php echo $row['trang_thai']; ?>"
                                    onclick="openReadModal(this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <button type="button" class="action-btn btn-delete" onclick="openDeleteModal(<?php echo $row['id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">Hộp thư đang trống.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="readModal" class="glass-modal">
        <div class="glass-modal-content">
            <h3 style="margin-top: 0; color: #b58b5a; border-bottom: 2px solid #eee; padding-bottom: 10px;">Chi tiết Liên hệ</h3>
            <p><strong>Khách hàng:</strong> <span id="v_name"></span></p>
            <p><strong>Số điện thoại:</strong> <span id="v_phone"></span></p>
            <p><strong>Email:</strong> <span id="v_email"></span></p>
            
            <div class="msg-box" id="v_msg"></div>
            
            <form action="messages.php" method="POST" id="form_mark_read">
                <input type="hidden" name="mark_read_id" id="v_id">
                <button type="submit" class="btn-mark-read" id="btn_mark"><i class="fa-solid fa-check"></i> Đánh dấu là đã xem</button>
            </form>
            <button type="button" class="btn-cancel" onclick="document.getElementById('readModal').style.display='none'">Đóng</button>
        </div>
    </div>

    <div id="deleteModal" class="glass-modal">
        <div class="glass-modal-content" style="text-align: center;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 50px; color: #d9534f; margin-bottom: 15px;"></i>
            <h3>Xác nhận xóa?</h3>
            <p style="color: #666; margin-bottom: 20px;">Tin nhắn liên hệ này sẽ bị xóa vĩnh viễn khỏi hệ thống.</p>
            <form action="messages.php" method="POST">
                <input type="hidden" name="delete_msg_id" id="delete_id">
                <button type="submit" class="btn-danger">Xóa ngay</button>
                <button type="button" class="btn-cancel" onclick="document.getElementById('deleteModal').style.display='none'">Hủy bỏ</button>
            </form>
        </div>
    </div>

    <script>
        // Hàm đọc dữ liệu an toàn từ data-* attributes
        function openReadModal(btn) {
            document.getElementById('v_id').value = btn.getAttribute('data-id');
            document.getElementById('v_name').innerText = btn.getAttribute('data-name');
            document.getElementById('v_phone').innerText = btn.getAttribute('data-phone');
            document.getElementById('v_email').innerText = btn.getAttribute('data-email');
            document.getElementById('v_msg').innerText = btn.getAttribute('data-msg');
            
            if(btn.getAttribute('data-status') === 'Đã xem') {
                document.getElementById('btn_mark').style.display = 'none';
            } else {
                document.getElementById('btn_mark').style.display = 'block';
            }
            
            document.getElementById('readModal').style.display = 'flex';
        }

        // Hàm mở Modal Xóa
        function openDeleteModal(id) { 
            document.getElementById('delete_id').value = id; 
            document.getElementById('deleteModal').style.display = 'flex'; 
        }
    </script>
    
    <?php include '../thongbao.php'; ?>
</body>
</html>