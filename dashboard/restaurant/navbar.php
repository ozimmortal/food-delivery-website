<?php 
  
  $userId = $_SESSION['user_id'];
  $name = $_SESSION['user_name'];

  require_once '../../includes/dbh.inc.php';

  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$userId]);
  $usr = $stmt->fetch();

  $image = $usr['image'];

?>
<!--Navbar-->

<div class="header">
          <i class="fa-solid fa-bars bar-item" >h</i>
          <div class="profile">
            <img src="../../<?php echo $image ?>" alt="No Image" />
            <p><?php echo $name ?></p>
          </div>
</div>