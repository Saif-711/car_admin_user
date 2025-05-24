<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "car_store";
    
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') {
       header("Location:Admin_Dashboard.php");
         exit(); 
     }
    $successMessage='';
    if(isset($_GET['msg'])&&$_GET['msg']==='Request_sent_successfully'){
        $successMessage='Request Sent Successfully';
    }

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get category from URL, default to 'all'
$category = $_GET['category'] ?? 'all';

// Query by category or get all
if ($category === 'all') {
    $sql = "SELECT * FROM cars ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM cars WHERE category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Store</title>
    <link rel="stylesheet" href="styles/style.css">
    <script src="script1.js"></script>
    <style>
        /* Responsive styles */
        @media (max-width: 992px) {
            .car-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .car-list {
                grid-template-columns: 1fr;
            }
            .list-of-categories ul {
                flex-wrap: wrap;
                gap: 10px;
            }
        }
        #home{
            top:0;
        }
    </style>
</head>
<body>
    <header id="home">
        <div class="header">
            <div class="name-page">
                <h1><a href="#home" style="text-decoration: none; color: inherit;">Car Store</a></h1>
            </div>
            <div class="left-side">
                <nav class="nav-left">
                    <ul>
                        <li class="nav-item">
                            <a href="#home">
                                <img src="images/home_logo.png" alt="Home">
                                <span>Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#vehicles">
                                <img src="images/home_logo.png" alt="Vehicles">
                                <span>Vehicles</span>
                            </a>
                            <ul class="services-list">
                                <li><a href="?category=all#vehicles">All Models</a></li>
                                <li><a href="?category=electric#vehicles">Electric Models</a></li>
                                <li><a href="?category=sedan#vehicles">Sedan Models</a></li>
                                <li><a href="?category=truck#vehicles">Truck Models</a></li>
                                <li><a href="?category=suv#vehicles">SUV Models</a></li>
                                <li><a href="?category=van#vehicles">Van Models</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="#aboutMyPage">
                                <img src="images/home_logo.png" alt="About">
                                <span>About Page</span>
                            </a>
                        </li>                        
                    </ul>
                </nav>
            </div>

<div class="right-side">
    <?php if (isset($_SESSION['user_id'])): ?>
        <button onclick="window.location.href='dashboard.php'">
            <p><i class="fas fa-tachometer-alt"></i>Dashboard</p>
        </button>
        <button onclick="window.location.href='logout.php'">
            <p><i class="fas fa-sign-out-alt"></i>Logout</p>
        </button>
    <?php else: ?>
        <button onclick="window.location.href='login.php'">
            <img src="images/add-user.png" alt="Login">
            <p>Login</p>
        </button>
        <button onclick="window.location.href='add_user_form.php'">
            <img src="images/add-user.png" alt="Register">
            <p>Register</p>
        </button>
    <?php endif; ?>
</div>
        </div>
    </header>
       <?php if(!empty($successMessage)): ?>
                        <div style="background-color: #d4edda; 
                        color: #155724; padding: 10px; 
                        margin: 15px 0; border: 1px solid #c3e6cb; 
                        border-radius: 5px;">
                    <?=$successMessage?>                        
                    </div>
                    <?php endif; ?>
    <div class="centeralize">
        <div class="image-project">
            <img class="cover-page-image" src="images/R.jpg" alt="Cover Image">
        </div>
        <div class="head-category">
            <h1>Vehicles</h1>
        </div>
        <div class="categories-list" id="vehicles">
            <nav class="list-of-categories">
                <ul>
                    <?php
                    $categories = [
                        'all' => 'Show All',
                        'electric' => 'Electric',
                        'sedan' => 'Sedan',
                        'truck' => 'Trucks',
                        'suv' => 'SUV',
                        'van' => 'Vans'
                    ];
                    foreach ($categories as $key => $label) {
                        $active = ($category === $key) ? 'style="font-weight:bold;"' : '';
                        echo "<li><a href='?category=$key#vehicles' $active>$label</a></li>";
                    }
                    ?>
                </ul>
            </nav>
        </div>
        <div class="car-list">
            <?php
            if ($result->num_rows > 0) {
                while ($car = $result->fetch_assoc()) {
                    ?>                 
                    <div class="car-card">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['title']) ?>">
                        <h3><?= htmlspecialchars($car['title']) ?></h3>
                        <p><strong>Type:</strong> <?= htmlspecialchars($car['category']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($car['description']) ?></p>
                        <p><strong>Price:</strong> $<?= htmlspecialchars($car['price']) ?></p>
                          <span class="buttonsCard">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="dashboard.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id']) ?>">
                                    <button type="submit">Contact With Admin</button>
                                </form>
                            <?php else: ?>
                                <button onclick="window.location.href='login.php'">Contact Admin</button>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="buy_request.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id']) ?>">
                                    <button type="submit">Sell Request</button>
                                </form>
                            <?php else: ?>
                                <button onclick="window.location.href='login.php'">Sell Request</button>
                            <?php endif; ?>

                            <?php if(isset($_SESSION['user_id'])):?>      
                                <form action="addToFavourite.php" method="POST">   
                                <input type="hidden" name="car_id" value="<?=htmlspecialchars($car['id'])?>">
                                <button type="submit">Add To Favourite</button>
                            </form>   
                             <?php else: ?>
                                <button onclick="window.location.href='login.php'">Add To Favourite</button>
                            <?php endif; ?>
                          </span>                      

                    </div>
                    <?php
                }
            } else {
                echo "<h3>No cars found in this category.</h3>";
            }
            $conn->close();
            ?>
        </div>
 <section id="aboutMyPage" class="about-section hidden">
  <div class="container">
    <h2 class="section-title">🚗 Welcome to Car Market</h2>
    <p class="intro">
      Discover a smarter way to buy and sell cars – fast, safe, and reliable.
    </p>

    <div class="features-grid">
      <div class="feature-box delay-1">
        <i class="fa fa-car feature-icon"></i>
        <h3>Massive Selection</h3>
        <p>Electric, sedan, SUV, truck – find your match from thousands of listings.</p>
      </div>
      <div class="feature-box delay-2">
        <i class="fa fa-bolt feature-icon"></i>
        <h3>Best Deals</h3>
        <p>Unlock exclusive offers and car bargains tailored for you.</p>
      </div>
      <div class="feature-box delay-3">
        <i class="fa fa-shield-alt feature-icon"></i>
        <h3>Verified & Secure</h3>
        <p>Every listing goes through strict verification for your peace of mind.</p>
      </div>
    </div>

    <div class="how-it-works fade-in">
      <h3>✨ How It Works</h3>
      <ul>
        <li><span>📝</span> Sign up as a buyer or seller.</li>
        <li><span>📸</span> Post or explore listings with details & photos.</li>
        <li><span>🔗</span> Connect directly with verified users.</li>
        <li><span>💳</span> Make secure transactions confidently.</li>
      </ul>
    </div>

    <div class="call-to-action fade-in">
      <p>🚀 Ready to upgrade your car journey?</p>
      <button onclick="window.location.href='add_user_form.php'" class="cta-button">Get Started</button>
    </div>
  </div>
</section>


    </div>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <h3>Mercedes</h3>
                <p>Luxury & Innovation - Discover your dream car today.</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#vehicles">Vehicles</a></li>
                    <li><a href="#aboutMyPage">About Us</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contact Us</h4>
                <p>Email: mosab@gmail.com</p>
                <p>Email: saif@gmail.com</p>
                <p>Phone: +123 456 7890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Mercedes | All Rights Reserved</p>
        </div>
    </footer>
    <script src="javascript/script1.js"></script>
</body>
</html>


