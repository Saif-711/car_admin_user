<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "car_store";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $type = $_POST['type'];

    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    $imagePath = "images/" . time() . "_" . basename($imageName);

    if (move_uploaded_file($imageTmp, $imagePath)) {
        $stmt = $conn->prepare("INSERT INTO cars(title, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $description, $price, $type, $imagePath);

        if ($stmt->execute()) {
            header("Location:Admin_Dashboard.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Failed to upload image.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Car</title>
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
    input[type="text"], input[type="password"], input[type="file"] {
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

<div class="register-popup">
  <h1>Add Car</h1>
  <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
    <label for="name">Name</label>
    <input type="text" name="name" required />
    
    <label for="description">Description</label>
    <input type="text" name="description" required />

    <label for="price">Price</label>
    <input type="number" name="price" required  style="display:block;padding:5px;margin:3px;width:96%;"/>
     <label for="type">Type</label>
    <select name="type" id="type" style="display:block;padding:10px;margin:3px;width:100%;">
        <option value="All">All</option>
        <option value="Electric">Electric</option>
        <option value="Sedan">Sedan</option>
        <option value="Truck">Truck</option>
        <option value="SUV">SUV</option>
        <option value="VAN">VAN</option>
    </select>
    <label for="image">Upload Image</label>
    <input type="file" name="image" accept="image/*" required />   
    <button type="submit" class="btn">ADD</button>
    <button type="button" class="btn cancel" onclick="window.location.href='index.php'">CLOSE</button>
  </form>
</div>

</body>
</html>
