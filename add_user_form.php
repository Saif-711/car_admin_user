<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host     = "localhost";
$user     = "root";
$password = "";
$dbname   = "car_store";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST['name'] ?? '');
    $username = trim($_POST['email'] ?? '');
    $pw       = $_POST['psw']   ?? '';
    $pw2      = $_POST['psw2']  ?? '';

    // Check passwords match
    if ($pw !== $pw2) {
        $message = "<p style='color:red;text-align:center;'>Passwords do not match!</p>";
    }
    // Check file upload presence and errors
    elseif (!isset($_FILES['image'])) {
        $message = "<p style='color:red;text-align:center;'>No file uploaded.</p>";
    }
    else {
        $fileError = $_FILES['image']['error'];

        if ($fileError !== UPLOAD_ERR_OK) {
            // Detailed upload errors
            switch ($fileError) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "<p style='color:red;text-align:center;'>Uploaded file is too large.</p>";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "<p style='color:red;text-align:center;'>File was only partially uploaded.</p>";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = "<p style='color:red;text-align:center;'>No file was uploaded.</p>";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "<p style='color:red;text-align:center;'>Missing a temporary folder.</p>";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "<p style='color:red;text-align:center;'>Failed to write file to disk.</p>";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = "<p style='color:red;text-align:center;'>File upload stopped by extension.</p>";
                    break;
                default:
                    $message = "<p style='color:red;text-align:center;'>Unknown upload error.</p>";
            }
        }
        else {
            // Prepare uploads folder
            $uploadBase = __DIR__ . '/uploads';
            if (!is_dir($uploadBase)) {
                if (!mkdir($uploadBase, 0755, true)) {
                    $message = "<p style='color:red;text-align:center;'>Failed to create uploads directory.</p>";
                }
            }

            if (empty($message)) {
                $imageName = uniqid() . "_" . basename($_FILES['image']['name']);
                $tmpName   = $_FILES['image']['tmp_name'];
                $target    = $uploadBase . '/' . $imageName;

                if (!is_uploaded_file($tmpName)) {
                    $message = "<p style='color:red;text-align:center;'>Uploaded file is not valid.</p>";
                } elseif (!move_uploaded_file($tmpName, $target)) {
                    $message = "<p style='color:red;text-align:center;'>Failed to move the uploaded file.</p>";
                } else {
                    // Insert user data to DB
                    $hashed = sha1($pw);
                    $stmt = $conn->prepare("INSERT INTO users (name, username, password, image) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $name, $username, $hashed, $imageName);

                    if ($stmt->execute()) {
                        // Redirect on success
                        header("Location: login.php");
                        exit;
                    } else {
                        $message = "<p style='color:red;text-align:center;'>Database error: " . htmlentities($stmt->error) . "</p>";
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add User</title>
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
  <h1>Add User</h1>
  <?php echo $message; ?>
  <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
    <label for="name">Name</label>
    <input type="text" name="name" required />

    <label for="image">Upload Image</label>
    <input type="file" name="image" accept="image/*" required />

    <label for="email">Email</label>
    <input type="text" name="email" required />

    <label for="psw">Password</label>
    <input type="password" name="psw" required />

    <label for="psw2">Confirm Password</label>
    <input type="password" name="psw2" required />

    <button type="submit" class="btn">ADD</button>
    <button type="button" class="btn cancel" onclick="window.location.href='index.php'">CLOSE</button>
  </form>
</div>

</body>
</html>
