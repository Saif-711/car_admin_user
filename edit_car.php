<?php
$conn = new mysqli("localhost", "root", "", "car_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the car ID from the URL
$carId = (int)$_GET['car_id'];

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Handle image upload
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    $imagePath = "images/" . time() . "_" . basename($imageName);
    move_uploaded_file($imageTmp, $imagePath);

    // Prepare and execute the update
    $stmt = $conn->prepare("UPDATE cars SET title = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssdssi", $title, $description, $price, $category, $imagePath, $carId);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Car updated successfully'); window.location.href='Admin_Dashboard.php';</script>";
    exit();
}

// Optional: fetch current car data to show in form (if you want)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Update Car</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f5f5;
    }
    .register-popup {
      width: 360px;
      margin: 80px auto;
      padding: 30px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .register-popup h1 {
      text-align: center;
      color: #333;
      margin-bottom: 24px;
    }
    input[type="text"], input[type="number"], input[type="file"] {
      width: 100%;
      padding: 12px;
      margin: 8px 0 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }
    .btn {
      width: 100%;
      background-color: #007bff;
      color: white;
      padding: 12px;
      margin: 10px 0;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s ease;
    }
    .btn:hover { background-color: #0056b3; }
    .cancel { background-color: #dc3545; }
    .cancel:hover { background-color: #b02a37; }
    label { font-weight: bold; color: #444; }
  </style>
</head>
<body>
    <?php
   
        $result = $conn->query("SELECT * FROM cars WHERE id = $carId");
        if ($result->num_rows === 0) {
            die("Car not found.");
        }
        $car = $result->fetch_assoc();
    ?>

<div class="register-popup">
  <h1>Update Car</h1>
  <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
    <label for="title">Title</label>
    <input type="text" name="title" required value="<?php echo htmlspecialchars($car['title']); ?>" />

    <label for="description">Description</label>
    <input type="text" name="description" required value="<?php echo htmlspecialchars($car['description']); ?>"/>

    <label for="price">Price</label>
    <input type="number" name="price" required value="<?php echo htmlspecialchars($car['price']); ?>" />

    <label for="category">Category</label>
    <select name="category" required value="<?php echo htmlspecialchars($car['title']); ?>">
        <option value="Sedan">Sedan</option>
        <option value="SUV">SUV</option>
        <option value="Truck">Truck</option>
        <option value="Electric">Electric</option>
        <option value="VAN">VAN</option>
    </select>
        <p>Current Image:</p>
        <img src="<?php echo $car['image']; ?>" width="150" alt="Current Car Image" style="margin-bottom:10px;" />
    <label for="image">Image</label>
    <input type="file" name="image" accept="image/*" required src="<?php echo $car['image']; ?>" />

    <button type="submit" class="btn">Update</button>
    <button type="button" class="btn cancel" onclick="window.location.href='index.php'">Close</button>
  </form>
</div>

</body>
</html>
