<?php
  include './layout/headerLayout.php'
?>

<nav class="navbar-h">
  <div class="title">
    <h2 class="logo">Sweet Bites</h2>
  </div>
  <div class="auth">
    <a class="button is-light is-outline is-rounded">SignUp</a>
    <a class="button is-dark  is-outline is-rounded" href="auth//login.php">Login</a>
  </div>
</nav>
  <section class="hero is-success is-halfheight">
  <div class="hero-body">
    <div class="container has-text-centered">
      <h1 class="is-1 text-1">
        Craving Something Delicious?
      </h1>
      <p class="is-3 text-2">
        Discover the best restaurants in your area
      </p>
      
      <!-- Food Search Input -->
      <div class="field has-addons is-justify-content-center mt-5">
        <div class="control is-expanded" style="max-width: 500px;">
          <input class="input is-large" type="text" placeholder="What are you craving today?">
        </div>
        <div class="control">
          <button class="button is-warning is-large">
            <span class="icon">
              <i class="fas fa-search"></i>
            </span>
            <span>Find Food</span>
          </button>
        </div>
      </div>
      
      
    </div>
  </div>
</section>

<?php
  include './layout/footerLayout.php'
?>
