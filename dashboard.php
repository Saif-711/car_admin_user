<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'USER') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "car_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id  = $_SESSION['user_id'];

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        $adm = $conn->query("SELECT id FROM users WHERE role='ADMIN' LIMIT 1")->fetch_assoc();
        $admin_id = $adm['id'] ?? 0;
        if ($admin_id) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $admin_id, $message);
            $stmt->execute();
        }
    }
    header("Location: dashboard.php");
    exit();
}

$imageFile = $_SESSION['image'] ?? 'default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
<meta charset="UTF-8" />
<title>User Control Panel</title>
<style>
  
  .userinfo-section {
  width: 400px;
  margin: 60px auto;
  padding: 30px;
  background: linear-gradient(to right, #e0eafc, #cfdef3);
  border-radius: 15px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  animation: slideFadeIn 1s ease;
}

.userinfo-section h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 20px;
  font-size: 26px;
  letter-spacing: 1px;
}

.user-card p {
  font-size: 18px;
  margin: 10px 0;
  color: #34495e;
}

.user-card strong {
  color: #2c3e50;
}

.error {
  color: red;
  text-align: center;
  font-weight: bold;
}
.center-love{
  margin:600px;
}table {
      width: 100%; border-collapse: collapse; margin-top: 10px;text-align: center;
      margin-tope:200px;
    }
    th,td {
      border: 1px solid #ccc; padding: 10px;  text-align: center;
    }
    th { background: #ecf0f1; }

@keyframes slideFadeIn {
  0% {
    opacity: 0;
    transform: translateY(30px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}


  body { margin:0; font-family:'Segoe UI',sans-serif; background:#f4f4f4; }
  .sidebar { position:fixed; top:0; left:0; width:250px; height:100vh; background:rebeccapurple; color:#fff; padding:20px; }
  .sidebar img { width:150px; height:150px; border-radius:50%; object-fit:cover; margin:0 auto 20px; display:block; border:2px solid #ccc; }
  .sidebar h2 { text-align:center; }
  .sidebar a { color:#fff; display:block; padding:10px; margin:5px 0; text-decoration:none; border-radius:5px; }
  .sidebar a.active { background:#34495e; }
  .logout-btn { margin-top:auto; text-align:center; }
  .logout-btn a { background:#e74c3c; padding:10px 20px; border-radius:5px; }
  .main { margin-left:250px; padding:30px; }
  .section { display:none; }
  .section.active { display:block; }
  .chat-box {    margin-top: 92px; max-width:600px; background:#fff; padding:20px; border-radius:8px; }
  .messages { max-height:300px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-bottom:10px; background:#fff; }
  .msg { margin:6px 0; }
  .msg.you span { background:#d1e7dd; }
  .msg.admin span { background:#cff4fc; }
  .msg span { display:inline-block; padding:6px 10px; border-radius:12px; }
  #favourites {
  max-width: 800px;
  margin: 30px auto;
  font-family: Arial, sans-serif;
}

#favourites h2 {
  text-align: center;
  color: #333;
  margin-bottom: 20px;
}

#favourites table {
  width: 100%;
  border-collapse: collapse;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

#favourites th, #favourites td {
  border: 1px solid #ddd;
  padding: 12px 15px;
  text-align: left;
}

#favourites th {
  background-color: #4CAF50;
  color: white;
  font-weight: bold;
}

#favourites tr:nth-child(even) {
  background-color: #f9f9f9;
}

#favourites tr:hover {
  background-color: #f1f1f1;
}

#favourites p {
  text-align: center;
  font-style: italic;
  color: #888;
  margin-top: 20px;
}

</style>
</head>
<body>

<div class="sidebar">
  <img src="uploads/<?= htmlspecialchars($imageFile) ?>" alt="Profile" />
  <h2><?= htmlspecialchars($_SESSION['name']) ?></h2>
 <a href="#chat" class="nav-link"><i class="fas fa-comments"></i> Chat with Admin</a>
<a href="index.php"><i class="fas fa-car"></i> Browse Cars</a>
<a href="#userinfo" class="nav-link"><i class="fas fa-user"></i> Personal Info</a>
<a href="#favourites" class="nav-link"><i class="fas fa-user"></i> Favourites</a>
  <div class="logout-btn">
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

</div>

<div class="main">
    <h1 style="top: -15px; left: 299px; position: absolute; width: 500px; background: rebeccapurple; padding: 10px; color: white;">
  <i style="margin-right:5px;" class="fas fa-tachometer-alt"></i> User Dashboard
</h1>

  <div id="chat" class="section">
    <div class="chat-box">
      <h2>Chat with Admin</h2>
      <div class="messages" id="chat-window">
        <?php
        $adm = $conn->query("SELECT id FROM users WHERE role='ADMIN'")->fetch_assoc();
        $admin_id = $adm['id'] ?? 0;
        if ($admin_id) {
            $stmt = $conn->prepare("
                SELECT sender_id, message, created_at
                FROM messages
                WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
                ORDER BY created_at ASC
            ");
            $stmt->bind_param("iiii", $user_id, $admin_id, $admin_id, $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($m = $res->fetch_assoc()):
                $who = $m['sender_id'] === $user_id ? 'you' : 'admin';
        ?>
          <div class="msg <?= $who ?>">
            <span><?= nl2br(htmlspecialchars($m['message'])) ?></span><br>
            <small><?= $m['created_at'] ?></small>
          </div>
        <?php endwhile; } ?>
      </div>

      <form method="post" action="">
        <input type="text" name="message" placeholder="Your message…" required style="width:80%; padding:8px;" />
        <button type="submit" name="send">Send</button>
      </form>
    </div>
  </div>

  <div id="userinfo" class="section" style="margin-left: 200px;display: block;margin-top: 100px;">
  <h2>Your Information</h2>
  <?php
    $host     = "localhost";
    $user     = "root";
    $password = "";
    $dbname   = "car_store";

    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $user_id = $_SESSION['user_id'] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user1 = $result->fetch_assoc();
  ?>

        <?php if ($user1): ?>
          <p><strong>Name:</strong> <?= htmlspecialchars($user1['name']) ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($user1['username']) ?></p>
          <p><strong>Logged in at:</strong> <?= date('Y-m-d H:i:s') ?></p>
              <a style="color: red; text-decoration: none;"
                        href="edit_user.php?user_id=<?=$user1['id']?>">                                            
                          Edit My Personal Information
                        </a>          
            <?php else: ?>
              <p>User not found.</p>
            <?php endif; ?>
      </div>
      <div id="favourites" class="section">                    
          <?php          
          $result = $conn->query("SELECT * FROM cars");
          $cars = $result->fetch_all(MYSQLI_ASSOC);
          $stmt = $conn->prepare("
          SELECT cars.*
          FROM favourites
          JOIN cars ON cars.id = favourites.car_id
          WHERE favourites.user_id = ?");      
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $result = $stmt->get_result();
            ?>
            <h2>Your Favourite Cars</h2>
      <?php if ($result->num_rows > 0): ?>
      <table border="1" cellpadding="10">
          <thead>
              <tr>
                  <th>Car ID</th>
                  <th>Title</th>
                  <th>Description</th>
                  <th>Price</th>
                  <th>Action</th>
                  <!-- Add more car fields if needed -->
              </tr>
          </thead>
          <tbody>
              <?php while ($car = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($car['id']) ?></td>
                      <td><?= htmlspecialchars($car['title']) ?></td>
                      <td><?= htmlspecialchars($car['description']) ?></td>
                      <td><?= htmlspecialchars($car['price']) ?></td>
                      <td>
                        <a style="color: red; text-decoration: none;"
                        href="delete_from_favourite.php?car_id=<?=$car['id']?>"
                          class="btn btn-del"
                          onclick="return confirm('Delete From Favourites?')">
                          Delete
                        </a>
                      </td>
                  </tr>
              <?php endwhile; ?>
          </tbody>
      </table>
  <?php else: ?>
      <p>No Favourites Cars</p>
  <?php endif; ?>
</div>


<script>
  const links = document.querySelectorAll('.nav-link');
  const sections = document.querySelectorAll('.section');

  function showSection(id) {
    sections.forEach(s => s.style.display = (s.id === id ? 'block' : 'none'));
    links.forEach(link => link.classList.toggle('active', link.getAttribute('href') === '#' + id));

    if(id === 'chat') {
      const chatWindow = document.getElementById('chat-window');
      if(chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
    }
  }

  // Load last active tab from localStorage or default to 'chat'
  const lastTab = localStorage.getItem('lastTab') || 'chat';
  showSection(lastTab);

  links.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const id = link.getAttribute('href').substring(1);
      showSection(id);
      localStorage.setItem('lastTab', id);
    });
  });
</script>

</body>
</html>

<?php $conn->close(); ?>
