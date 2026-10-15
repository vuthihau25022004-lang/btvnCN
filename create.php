<?php
require_once "db.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$MaSP = "";
$TenSP = "";
$Gia = "";
$SoLuong = "";
$MoTa = "";
$NgayTao = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $MaSP = strtoupper(trim($_POST["MaSP"] ?? ""));
    $TenSP = trim($_POST["TenSP"] ?? "");
    $Gia = trim($_POST["Gia"] ?? "");
    $SoLuong = trim($_POST["SoLuong"] ?? "");
    $MoTa = trim($_POST["MoTa"] ?? "");
    $NgayTao = trim($_POST["NgayTao"] ?? "");

    if ($MaSP === "" || $TenSP === "" || $Gia === "" || $SoLuong === "") {
        $error = "Vui lòng nhập đầy đủ các thông tin bắt buộc.";
    } elseif (strlen($MaSP) > 10) {
        $error = "Mã sản phẩm không được vượt quá 10 ký tự.";
    } elseif (strlen($TenSP) > 100 || strlen($MoTa) > 200) {
        $error = "Tên sản phẩm tối đa 100 ký tự, mô tả tối đa 200 ký tự.";
    } elseif (!is_numeric($Gia) || (float)$Gia < 0
        || !is_finite((float)$Gia)
        || (float)$Gia > 9999999999.99
        || !preg_match('/^\d+$/', $SoLuong)
        || (float)$SoLuong > 2147483647) {
        $error = "Giá hoặc số lượng không hợp lệ.";
    } else {
        $check = $conn->prepare("SELECT MaSP FROM sanpham WHERE MaSP = ?");
        $check->bind_param("s", $MaSP);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $error = "Mã sản phẩm đã tồn tại.";
        } else {
            if ($NgayTao === "") {
                $stmt = $conn->prepare(
                    "INSERT INTO sanpham (MaSP, TenSP, Gia, SoLuong, MoTa)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $price = (float)$Gia;
                $quantity = (int)$SoLuong;
                $stmt->bind_param(
                    "ssdis", $MaSP, $TenSP, $price, $quantity, $MoTa
                );
            } else {
                $date = DateTime::createFromFormat("Y-m-d\TH:i", $NgayTao);
                $validDate = $date && $date->format("Y-m-d\TH:i") === $NgayTao;

                if (!$validDate) {
                    $error = "Ngày tạo không hợp lệ.";
                } else {
                    $NgayTaoDB = $date->format("Y-m-d H:i:s");
                    $stmt = $conn->prepare(
                        "INSERT INTO sanpham
                         (MaSP, TenSP, Gia, SoLuong, MoTa, NgayTao)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $price = (float)$Gia;
                    $quantity = (int)$SoLuong;
                    $stmt->bind_param(
                        "ssdiss", $MaSP, $TenSP, $price,
                        $quantity, $MoTa, $NgayTaoDB
                    );
                }
            }

            if ($error === "") {
                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();
                    header("Location: index.php");
                    exit;
                }

                $error = "Không thể thêm sản phẩm.";
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h1>THÊM SẢN PHẨM</h1>

        <?php if ($error !== ""): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Mã sản phẩm *</label>
            <input type="text" name="MaSP" maxlength="10"
                   value="<?= e($MaSP) ?>" required>

            <label>Tên sản phẩm *</label>
            <input type="text" name="TenSP" maxlength="100"
                   value="<?= e($TenSP) ?>" required>

            <label>Giá (VNĐ) *</label>
            <input type="number" name="Gia" min="0" max="9999999999.99"
                   step="0.01" value="<?= e($Gia) ?>" required>

            <label>Số lượng *</label>
            <input type="number" name="SoLuong" min="0" step="1"
                   value="<?= e($SoLuong) ?>" required>

            <label>Mô tả</label>
            <textarea name="MoTa" maxlength="200"><?= e($MoTa) ?></textarea>

            <label>Ngày tạo (để trống nếu lấy ngày hiện tại)</label>
            <input type="datetime-local" name="NgayTao"
                   value="<?= e($NgayTao) ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-add">
                    Lưu sản phẩm
                </button>
                <a href="index.php" class="btn btn-back">Quay lại</a>
            </div>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
