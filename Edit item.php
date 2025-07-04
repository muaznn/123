<?php include 'db.php'; ?>
<?php
id = $_GET['id'];$

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['product_code'];
    $name = $_POST['product_name'];
    $cat = $_POST['category'];
    $sql = "UPDATE inventory SET product_code='$code', product_name='$name', category='$cat' WHERE id=$id";
    $conn->query($sql);
    header("Location: index.php");
}

$sql = "SELECT * FROM inventory WHERE id=$id";
$row = $conn->query($sql)->fetch_assoc();
?>
<h2>Edit Item</h2>
<form method="POST">
  Product Code: <input name="product_code" value="<?= $row['product_code'] ?>"><br>
  Product Name: <input name="product_name" value="<?= $row['product_name'] ?>"><br>
  Category:
  <select name="category">
    <option <?= $row['category'] == 'Item' ? 'selected' : '' ?>>Item</option>
    <option <?= $row['category'] == 'Equipment' ? 'selected' : '' ?>>Equipment</option>
  </select><br>
  <button type="submit">Save</button>
</form>
<a href="index.php">Cancel</a>
