<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Items</title>
  <style>
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
    a.btn { padding: 4px 10px; text-decoration: none; border-radius: 4px; margin: 0 2px; }
    .view { background: #2ecc71; color: #fff; }
    .edit { background: #f39c12; color: #fff; }
    .delete { background: #e74c3c; color: #fff; }
  </style>
</head>
<body>
  <h2>Inventory Summary</h2>
  <a href="add.php" class="btn">+ Add New Item</a>
  <br><br>
  <table>
    <tr>
      <th>No.</th>
      <th>Product Code</th>
      <th>Product Name</th>
      <th>Category</th>
      <th>Action</th>
    </tr>
    <?php
    $sql = "SELECT * FROM inventory";
    $result = $conn->query($sql);
    $i = 1;
    while($row = $result->fetch_assoc()) {
      echo "<tr>
              <td>{$i}</td>
              <td>{$row['product_code']}</td>
              <td>{$row['product_name']}</td>
              <td>{$row['category']}</td>
              <td>
                <a href='view.php?id={$row['id']}' class='btn view'>👁️</a>
                <a href='edit.php?id={$row['id']}' class='btn edit'>✏️</a>
                <a href='delete.php?id={$row['id']}' class='btn delete' onclick='return confirm(\"Are you sure?\")'>🗑️</a>
              </td>
            </tr>";
      $i++;
    }
    ?>
  </table>
</body>
</html>
