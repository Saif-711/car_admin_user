<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "car_store";
if(isset($_SESSION['role'])){
  if($_SESSION['role']==='ADMIN'){
    header("Location:Admin_Dashboard.php");
  }
  else{
    header("Location:index.php");
  }
}

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['email'] ?? '');
    $password = $_POST['psw'] ?? '';

    $stmt = $conn->prepare("SELECT name, id, username, password, role, image FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (sha1($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['image'] = $user['image'];

            if (strtoupper($user['role']) === 'ADMIN') {
                header("Location: Admin_Dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "<p style='color:red;text-align:center;'>Wrong password!</p>";
        }
    } else {
        $message = "<p style='color:red;text-align:center;'>Email does not exist!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f5f5;
    }
    .login-popup {
      width: 360px;
      margin: 80px auto;
      padding: 30px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .login-popup h1 {
      text-align: center;
      color: #333;
      margin-bottom: 24px;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 8px 0 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }
    .btn {
      width: 100%;
      background-color: #28a745;
      color: white;
      padding: 12px;
      margin: 10px 0;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s ease;
    }
    .btn:hover { background-color: #218838; }
    .cancel { background-color: #dc3545; }
    .cancel:hover { background-color: #b02a37; }
    label { font-weight: bold; color: #444; }
  </style>
</head>
<body>

<div class="login-popup">
  <h1>Login</h1>
  <?= $message ?>
  <form method="POST" action="" autocomplete="off">
    <label for="email">Email</label>
    <input type="text" name="email" required />

    <label for="psw">Password</label>
    <input type="password" name="psw" required />

    <button type="submit" class="btn">Login</button>
    <button type="button" class="btn cancel" onclick="window.location.href='index.php'">Cancel</button>
  </form>
  <p style="text-align:center;margin-top:15px;">Don't have an account? <a href="add_user_form.php">Register</a></p>
</div>

</body>
</html>
