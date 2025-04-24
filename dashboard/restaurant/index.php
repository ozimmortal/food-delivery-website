<?php
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../public/css/all.min.css" />
    <link rel="stylesheet" href="../../public/css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
      href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&amp;display=swap"
      rel="stylesheet"
    />

    <title>Dashboard</title>
  </head>
  <body>
    <div class="loader">
      <h1>Loading<span>....</span></h1>
    </div>
    <div class="page-content index-page">
      <?php 
        include('sidebar.php');
      ?>
      
      <main>
        <?php 
          include('navbar.php');
        ?>
      </main>
    </div>
    <script src="../../public/js/script.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    lucide.createIcons();
  </script>

  </body>
</html>
