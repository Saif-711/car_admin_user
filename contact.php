<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

// connect
$conn = new mysqli("localhost", "root", "", "car_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$admin_id = $_SESSION['user_id'];

// target user
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "User not specified.";
    exit();
}
$user_id = (int)$_GET['user_id'];

// handle admin message send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        $stmt = $conn->prepare(
            "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $admin_id, $user_id, $message);
        $stmt->execute();
    }
    header("Location: contact.php?user_id={$user_id}#chat");
    exit();
}

// fetch chatting user’s name
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?: ['name'=>'Unknown'];

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Chat with <?= htmlspecialchars($user['name']) ?></title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
    .chat-box { max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px; }
    .messages { max-height:400px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-bottom:15px; }
    .msg { margin:8px 0; }
    .msg.admin { text-align:right; }
    .msg.user  { text-align:left; }
    .msg span { display:inline-block; padding:8px 12px; border-radius:12px; }
    .msg.admin span { background:#d1e7dd; }
    .msg.user  span { background:#cff4fc; }
    form textarea { width:100%; height:80px; padding:8px; }
    form button { padding:10px 20px; background:#000; color:#fff; border:none; cursor:pointer; }
    .msg.user.read span {
  background-color: #bde0fe !important; /* light blue */
  font-style: italic;
  opacity: 0.8;
}

  </style>
</head>
<body>

<div class="chat-box" id="chat">
  <h2>Chat with <?= htmlspecialchars($user['name']) ?></h2>

  <div class="messages" id="chat-window">
    <?php
    $stmt = $conn->prepare("
        SELECT sender_id, message, created_at, is_read
        FROM messages
        WHERE (sender_id=? AND receiver_id=?)
        OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at ASC
        ");
    $stmt->bind_param("iiii", $admin_id, $user_id, $user_id, $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()):
      $who = $row['sender_id'] === $admin_id ? 'admin' : 'user';
    ?>
      <div class="msg <?= $who ?>">
        <span><?= nl2br(htmlspecialchars($row['message'])) ?></span>
        <br><small><?= $row['created_at'] ?></small>
      </div>
    <?php endwhile; ?>
  </div>

  <form method="post" action="">
<?php
      $markRead = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
      $markRead->bind_param("ii", $user_id, $admin_id);
      $markRead->execute();
      $markRead->close();
?>
    <textarea name="message" placeholder="Type your message..." required></textarea>
    <br><button type="submit" name="send">Send</button>
    <button onclick="window.location.href='Admin_Dashboard.php'">Close Chat</button>
  </form>
</div>

<script>
  // scroll to bottom
  const win = document.getElementById('chat-window');
  if (win) win.scrollTop = win.scrollHeight;
</script>

</body>
</html>
<?php $conn->close(); ?>
