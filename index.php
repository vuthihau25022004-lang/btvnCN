<?php
require_once "db.php";

$result = $conn->query("SELECT * FROM sanpham ORDER BY NgayTao DESC");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>QUẢN LÝ SẢN PHẨM</h1>

        <div class="toolbar">
            <p>Danh sách sản phẩm trong hệ thống</p>
            <a href="create.php" class="btn btn-add">
                + Thêm sản phẩm
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá (VNĐ)</th>
                        <th>Số lượng</th>
                        <th>Mô tả</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= e($row["MaSP"]) ?></td>
                                <td><?= e($row["TenSP"]) ?></td>
                                <td>
                                    <?= number_format((float)$row["Gia"], 0, ",", ".") ?>
                                </td>
                                <td><?= e($row["SoLuong"]) ?></td>
                                <td><?= e($row["MoTa"]) ?></td>
                                <td><?= e($row["NgayTao"]) ?></td>
                                <td class="actions">
                                    <a
                                        href="edit.php?MaSP=<?= urlencode($row["MaSP"]) ?>"
                                        class="btn btn-edit"
                                    >Sửa</a>

                                    <form action="delete.php" method="POST"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                        <input
                                            type="hidden"
                                            name="MaSP"
                                            value="<?= e($row["MaSP"]) ?>"
                                        >
                                        <button type="submit" class="btn btn-delete">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Chưa có sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
