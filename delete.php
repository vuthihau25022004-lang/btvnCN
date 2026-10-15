
<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $conn->close();
    header("Location: index.php");
    exit;
}

$MaSP = trim($_POST["MaSP"] ?? "");

if ($MaSP === "" || strlen($MaSP) > 10) {
    $conn->close();
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM sanpham WHERE MaSP = ?");
$stmt->bind_param("s", $MaSP);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: index.php");
exit;
?>
