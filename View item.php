<?php include 'db.php'; ?>
<?php
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$sql = "SELECT * FROM inventory WHERE id=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>
<h2>View Item</h2>
<p><strong>Product Code:</strong> <?= $row['product_code'] ?></p>
<p><strong>Product Name:</strong> <?= $row['product_name'] ?></p>
<p><strong>Category:</strong> <?= $row['category'] ?></p>
<a href="index.php">Back</a>
