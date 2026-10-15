<?php
require_once "db.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$MaSP = trim($_GET["MaSP"] ?? "");

if ($MaSP === "" || strlen($MaSP) > 10) {
    $conn->close();
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM sanpham WHERE MaSP = ?");
$stmt->bind_param("s", $MaSP);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $conn->close();
    die("Không tìm thấy sản phẩm.");
}

$TenSP = $product["TenSP"];
$Gia = $product["Gia"];
$SoLuong = $product["SoLuong"];
$MoTa = $product["MoTa"];
$NgayTao = date("Y-m-d\TH:i", strtotime($product["NgayTao"]));
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $TenSP = trim($_POST["TenSP"] ?? "");
    $Gia = trim($_POST["Gia"] ?? "");
    $SoLuong = trim($_POST["SoLuong"] ?? "");
    $MoTa = trim($_POST["MoTa"] ?? "");
    $NgayTao = trim($_POST["NgayTao"] ?? "");

    if ($TenSP === "" || $Gia === "" || $SoLuong === "" || $NgayTao === "") {
        $error = "Vui lòng nhập đầy đủ thông tin bắt buộc.";
    } elseif (strlen($TenSP) > 100 || strlen($MoTa) > 200) {
        $error = "Tên sản phẩm tối đa 100 ký tự, mô tả tối đa 200 ký tự.";
    } elseif (!is_numeric($Gia) || (float)$Gia < 0
        || !is_finite((float)$Gia)
        || (float)$Gia > 9999999999.99
        || !preg_match('/^\d+$/', $SoLuong)
        || (float)$SoLuong > 2147483647) {
        $error = "Giá hoặc số lượng không hợp lệ.";
    } else {
        $date = DateTime::createFromFormat("Y-m-d\TH:i", $NgayTao);
        $validDate = $date && $date->format("Y-m-d\TH:i") === $NgayTao;

        if (!$validDate) {
            $error = "Ngày tạo không hợp lệ.";
        } else {
            $NgayTaoDB = $date->format("Y-m-d H:i:s");
            $price = (float)$Gia;
            $quantity = (int)$SoLuong;

            $stmt = $conn->prepare(
                "UPDATE sanpham
                 SET TenSP = ?, Gia = ?, SoLuong = ?, MoTa = ?, NgayTao = ?
                 WHERE MaSP = ?"
            );

            $stmt->bind_param(
                "sdisss", $TenSP, $price, $quantity,
                $MoTa, $NgayTaoDB, $MaSP
            );

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: index.php");
                exit;
            }

            $error = "Không thể cập nhật sản phẩm.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h1>SỬA SẢN PHẨM</h1>

        <?php if ($error !== ""): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Mã sản phẩm</label>
            <input type="text" value="<?= e($MaSP) ?>" disabled>

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

            <label>Ngày tạo *</label>
            <input type="datetime-local" name="NgayTao"
                   value="<?= e($NgayTao) ?>" required>

            <div class="form-actions">
                <button type="submit" class="btn btn-edit">
                    Cập nhật
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
