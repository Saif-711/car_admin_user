<?php
// --------------------
// edit_user.php
// --------------------

// 1) عرض الأخطاء للمطور
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) ربط بقاعدة البيانات
$host     = "localhost";
$dbUser   = "root";
$dbPass   = "";
$dbName   = "car_store";
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3) احصل على user_id من الـ GET
if (!isset($_GET['user_id'])) {
    die("User ID missing in URL.");
}
$user_id = intval($_GET['user_id']);

$message = "";

// 4) جلب بيانات المستخدم الحالية
$stmt = $conn->prepare("SELECT name, password, image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($currentName, $currentHashedPw, $currentImage);
if (!$stmt->fetch()) {
    die("User not found.");
}
$stmt->close();

// 5) معالجة الإرسال
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // الاسم الجديد
    $newName = trim($_POST['name'] ?? '');
    
    // كلمات السر
    $old_pw = $_POST['old_password'] ?? '';
    $new_pw = $_POST['new_password'] ?? '';
    
    // البداية: نعيد SQL وبارامترات
    $sql    = "UPDATE users SET name = ?";
    $types  = "s";
    $params = [ &$newName ];
    
    // تحقق من كلمة السر القديمة إن أدخل المستخدم حقل كلمة السر
    if ($old_pw !== '' || $new_pw !== '') {
        if (sha1($old_pw) !== $currentHashedPw) {
            $message .= "<p style='color:red;text-align:center;'>❌ كلمة السر القديمة غير صحيحة.</p>";
        } elseif ($new_pw === '') {
            $message .= "<p style='color:red;text-align:center;'>❌ من فضلك أدخل كلمة السر الجديدة.</p>";
        } else {
            $newHash = sha1($new_pw);
            $sql    .= ", password = ?";
            $types  .= "s";
            $params[] = & $newHash;
            $message .= "<p style='color:green;text-align:center;'>✅ سيتم تحديث كلمة السر.</p>";
        }
    }
    
    // معالجة الصورة إن رفعها المستخدم
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = uniqid() . "_" . basename($_FILES['image']['name']);
        $target   = "$uploadDir/$filename";
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $sql     .= ", image = ?";
            $types   .= "s";
            $params[] = & $filename;
            $message .= "<p style='color:green;text-align:center;'>✅ تم تحديث الصورة.</p>";
        } else {
            $message .= "<p style='color:red;text-align:center;'>❌ فشل رفع الصورة.</p>";
        }
    }
    
    // نكمل جملة WHERE
    $sql      .= " WHERE id = ?";
    $types    .= "i";
    $params[] = & $user_id;
    
    // إذا لا توجد أخطاء فنية تمنع التحديث
    if (strpos($message, '❌') === false) {
        $stmt = $conn->prepare($sql);
        // ربط المعاملات ديناميكياً
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $message .= "<p style='color:green;text-align:center;'>✅ تم تحديث بيانات المستخدم بنجاح.</p>";
            // نحدث الاسم وكلمة السر المحلية
            $currentName = $newName;
            if (isset($newHash)) {
                $currentHashedPw = $newHash;
            }
            if (isset($filename)) {
                $currentImage = $filename;
            }
        } else {
            $message .= "<p style='color:red;text-align:center;'>❌ خطأ في التحديث: {$stmt->error}</p>";
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8" />
  <title>Edit User</title>
  <style>
    body { background: #f5f5f5; font-family: Tahoma, sans-serif; }
    .popup {
      width: 360px; margin: 50px auto; padding: 20px;
      background: #fff; border-radius: 8px;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
    }
    .popup h1 { text-align: center; color: #333; margin-bottom: 20px; }
    input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; }
    button { background: #007bff; color: #fff; border: none; cursor: pointer; }
    button.cancel { background: #dc3545; }
    img.profile { display: block; margin: 0 auto 10px; width: 80px; height: 80px; object-fit: cover; border-radius: 50%; }
    label { font-weight: bold; }
  </style>
</head>
<body>

<div class="popup">
  <h1>Modify User</h1>
  <?php echo $message; ?>
  
  <?php if ($currentImage): ?>
    <img src="uploads/<?php echo htmlspecialchars($currentImage); ?>" class="profile" alt="User Image">
  <?php endif; ?>
  
  <form method="POST" enctype="multipart/form-data">
    <label>Updated Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($currentName); ?>" required>
    
    <label>Old password</label>
    <input type="password" name="old_password" placeholder="للتعديل">
    
    <label>New Password </label>
    <input type="password" name="new_password">
    
    <label>Updated Image</label>
    <input type="file" name="image" accept="image/*">
    
    <button type="submit">Save Changes</button>
    <button type="button" class="cancel" onclick="location.href='dashboard.php'">Cancel</button>
  </form>
</div>

</body>
</html>
