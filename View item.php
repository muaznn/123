<?php include 'db.php'; ?>
<?php
$id = $_GET['id'];
$sql = "SELECT * FROM inventory WHERE id=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>
<h2>View Item</h2>
<p><strong>Product Code:</strong> <?= $row['product_code'] ?></p>
<p><strong>Product Name:</strong> <?= $row['product_name'] ?></p>
<p><strong>Category:</strong> <?= $row['category'] ?></p>
<a href="index.php">Back</a>
