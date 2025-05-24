<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "car_store");
$adminName = $_SESSION['name'];
$image_admin=$_SESSION['image'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="styles/style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f4f4;
    }
    .sidebar {
      position: fixed;
      top: 0; left: 0;
      width: 250px; height: 100vh;
      background-color:rgb(0, 0, 0); color: white;
      display: flex; flex-direction: column; padding: 20px;
    }
    .sidebar img {
       width: 150px;            
      height: 150px;
      border-radius: 50%;      /* Makes the image circular */
      object-fit: cover;       /* Ensures the image fills the box without distortion */
      border: 2px solid #ccc;  /* Optional: subtle border */
      box-shadow: 0 2px 5px rgba(0,0,0,0.15); /* Optional: soft shadow */
      display: block;
      margin: 0 auto;          /* Optional: center the image horizontally */
      margin-bottom:20px;
    }
    .sidebar h2 { text-align: center; margin-bottom: 30px; }
    .sidebar a {
      color: white; padding: 10px; margin: 5px 0;
      text-decoration: none; border-radius: 5px;
    }
    
    .sidebar a.active-link {
      background-color: #34495e;
    }
    .logout-btn {
      margin-top: auto; padding: 10px;
      background-color: #e74c3c; text-align: center; border-radius: 5px;
    }
    .logout-btn a { color: white; text-decoration: none; }

    .main-content {
      margin-left: 250px;
      padding: 30px; height: 100vh; overflow-y: auto;
    }

    /* All sections hidden by default */
    .section { display: none; }

    /* Only the active one shows */
    .section.active { display: block; }

    .card {
      background: white;
      padding: 20px; 
      margin-top: 20px;
      border-radius: 10px; 
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      opacity: 0; 
      transform: translateY(20px);
      animation: fadeInUp 0.5s forwards ease-out;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0);    }
    }

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  text-align: center;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  overflow: hidden;
}

th, td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}

th {
  background: #ecf0f1;
  font-weight: 600;
}

tr:nth-child(even) {
  background-color: #f9f9f9;
}

tr:hover {
  background-color: #e0e7ff;
  cursor: pointer;
}

/* Optional buttons inside table */
.btn {
  display: inline-block;
  padding: 8px 12px;
  border-radius: 5px;
  text-decoration: none;
  margin-right: 5px;
  font-weight: 600;
}

.btn-add {
  margin-top: 5px;
  background: #3498db;
  color: #fff;
}

.btn-con {
  color: rgb(0, 178, 222);
}

.btn-edit {
  color: green;
}

.btn-del {
  color: red;
}

.notif-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  background: red;
  border-radius: 50%;
  margin-left: 5px;
  vertical-align: middle;
}

    .notif-dot {
      display: inline-block;
      width: 10px;
      height: 10px;
      background: red;
      border-radius: 50%;
      margin-left: 5px;
      vertical-align: middle;
    }

  </style>
</head>
<body>

  <div class="sidebar">
    <img src="uploads/<?=$image_admin?>" alt="Admin">
    <h2><?= htmlspecialchars($adminName) ?></h2>
    <a href="#users" class="nav-link active-link">
  <i class="fas fa-users-cog"></i> Manage Users
</a>

<a href="#cars" class="nav-link">
  <i class="fas fa-car-side"></i> Manage Cars
</a>

<a href="#requests" class="nav-link">
  <i class="fas fa-handshake"></i> Buy Requests
</a>
<a href="#favourites" class="nav-link">
  <i class="fa-solid fa-heart"></i>
 Favourites
</a>


<div class="logout-btn">
  <a href="logout.php">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</div>

  </div>

  <div class="main-content">     
     <h1 style="top:14px;left:256px;position:absolute; width:500px; background-color: black;padding:10px;color:white;">
      <i style="margin-right:5px;" class="fas fa-tachometer-alt"></i>Admin Dashboard</h1>
    <section id="users" class="section active">
      <h1>All Users</h1>
      <?php
      $res = $conn->query("SELECT * FROM users");
      if ($res->num_rows > 0): ?>
        <a href="add_user_form.php" class="btn btn-add">Insert User</a>
        <table>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
          <?php while($u = $res->fetch_assoc()): ?>
            <?php if($u['role']!=='ADMIN'):?>
            <tr>
              <td><?= $u['id'] ?></td>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['username']) ?></td>              
              <td>           
                <a href="delete_user.php?id=<?= $u['id'] ?>"
                   class="btn btn-del"
                   onclick="return confirm('Delete this user?')">
                   Delete
                </a>
             <?php

            $uid = $u['id'];
            $sender_id = (int)$uid;
            $receiver_id = (int)$_SESSION['user_id'];

            $sql = "SELECT COUNT(*) AS cnt 
            FROM messages 
            WHERE sender_id = $sender_id 
            AND receiver_id = $receiver_id 
            AND is_read = 0";

        $checkMsg = $conn->query($sql);

        if ($checkMsg) {
            $msgData = $checkMsg->fetch_assoc();
            $hasNotification = ($msgData['cnt'] > 0);
        } else {
            $hasNotification = false; // fallback if query fails
        }
    ?>

<a href="contact.php?user_id=<?= $uid ?>" class="btn btn-con">
    Contact
    <?php if ($hasNotification): ?>
        <span class="notif-dot"></span>
    <?php endif; ?>
</a>
      </td>
            </tr>
             <?php endif; ?>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <p>No users found.</p>
      <?php endif; ?>
    </section>

    <!-- CARS SECTION -->
    <section id="cars" class="section">
      <h1>Car Listings</h1>
      <a href="insert_car.php" class="btn btn-add">Insert Car</a>
      <?php
      $res = $conn->query("SELECT * FROM cars");
      if ($res->num_rows > 0): ?>
        <table>
          <tr><th>ID</th><th>Car Name</th><th>Car Description</th><th>Car Type</th><th>Price</th><th>Created At</th><th>Actions</th></tr>
          <?php while($c = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $c['id'] ?></td>
              <td><?= htmlspecialchars($c['title']) ?></td>
              <td><?= htmlspecialchars($c['description']) ?></td>
              <td><?= htmlspecialchars($c['category']) ?></td>
              <td>$<?= htmlspecialchars($c['price']) ?></td>
              <td><?= htmlspecialchars($c['created_at']) ?></td>
              <td>
                <a href="edit_car.php?car_id=<?=$c['id']?>"
                     class="btn btn-edit"
                        onclick="return confirm('Edit this car?')">
                   Edit
                </a>
                 <a href="delete_car.php?car_id=<?=$c['id']?>"
                     class="btn btn-del"
                        onclick="return confirm('Edit this car?')">
                   Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <p>No cars listed.</p>
      <?php endif; ?>
    </section>

    <!-- REQUESTS SECTION -->
    <section id="requests" class="section">
      <h1>Buy Requests</h1>
      <?php
      $res = $conn->query("
        SELECT u.id AS user_id, u.name, br.id, u.username, c.title, c.id AS car_id
        FROM orders br
        JOIN users u ON br.user_id = u.id
        JOIN cars c ON br.car_id = c.id
      ");
      
      if ($res->num_rows > 0): ?>
        <table>
          <tr><th>Request ID</th><th>User Id</th><th>User name</th><th>Email</th><th>Car ID</th><th>Car</th><th>Actions</th></tr>
          <?php while($r = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['user_id']) ?></td>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= htmlspecialchars($r['username']) ?></td>
              <td><?= htmlspecialchars($r['car_id']) ?></td>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td>
                <a href="delete_orders.php?order_id=<?= $r['id'] ?>" 
                  onclick="return confirm('Are you sure you want to delete this order?')"
                  class="btn btn-del">
                  Delete
                </a>   
                 <a href="confirm_request.php?order_id=<?= $r['id'] ?>" 
                  onclick="return confirm('Are you sure you want to confirm this request?')"
                  class="btn btn-del"
                  style="color:blue"
                  >
                  Delete
                </a>   
                <!-- <a href="contact.php?user_id=<?= $r['user_id'] ?>" class="btn btn-con">Contact</a> -->
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <p>No requests found.</p>
      <?php endif; ?>
    </section>

     <section id="favourites" class="section">
        <h1>Favourite Order</h1>        
        <?php
 
        $result = $conn->query("
             SELECT c.id   AS car_id,
             c.title AS car_title,
             c.description AS car_description,
             COUNT(*) AS freq
             FROM favourites fav
             JOIN cars c   ON c.id = fav.car_id
             GROUP BY c.id, c.title, c.description
             ORDER BY freq DESC
            ");

          if($result->num_rows>0):?>
          <table>
             <tr><th>Car ID</th><th>Car Name</th><th>Car Description</th><th>Count of Favourite</th></tr>
             <?php while($c=$result->fetch_assoc()):?>
              <tr>
              <td><?=htmlspecialchars($c['car_id'])?></td>           
              <td><?=htmlspecialchars($c['car_title'])?></td>           
              <td><?=htmlspecialchars($c['car_description'])?></td>           
              <td><?=htmlspecialchars($c['freq'])?></td>         
              </tr>  
               <?php endwhile; ?>
          </table>
          <?php else:?>
            <p>No Favourites Found</p>
             <?php endif; ?>        
      </section>
  </div>


  <script>
    document.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        // clear active link
        document.querySelector('.sidebar .active-link')
                .classList.remove('active-link');
        link.classList.add('active-link');
        // hide all sections
        document.querySelectorAll('.section')
                .forEach(sec => sec.classList.remove('active'));
        // show target
        const target = link.getAttribute('href').slice(1);
        document.getElementById(target)
                .classList.add('active');
      });
    });
  </script>
</body>
</html>
