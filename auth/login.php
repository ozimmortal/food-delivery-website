<?php
  include '../layout/headerLayout.php'
?>

<section class="a-nav">
    <div class="buttons is-link none">
        <button id="themeToggle" class="button is-light none">
            <span class="icon">
                    <i class="fas fa-moon"></i>

             </span>
        </button>
                            
    </div>
</section>
    
<section class="hero is-fullheight a">
  <div class="hero-body">
    <div class="container">
      <div class="columns is-centered">
        <div class="column is-5-tablet is-4-desktop is-3-widescreen">
          <div class="box has-shadow" style="border-top: 4px solid #ff7b25;">
            <div class="has-text-centered mb-5">
              <span class="icon-text">
                <span class="title is-3 has-text-weight-bold">Sweet Bite</span>
              </span>
              <p class="subtitle is-5 mt-2">Sign in to your account</p>
            </div>
            <form action="ls-login.php" method="POST">
              <div class="field">
                <label class="label">Email</label>
                <div class="control has-icons-left">
                  <input class="input" type="email" name="email" placeholder="e.g. alex@example.com" required>
                  <span class="icon is-small is-left">
                    <i class="fas fa-envelope"></i>
                  </span>
                </div>
              </div>
              <div class="field">
                <label class="label">Password</label>
                <div class="control has-icons-left">
                  <input class="input" type="password" name="password" placeholder="********" required>
                  <span class="icon is-small is-left">
                    <i class="fas fa-lock"></i>
                  </span>
                </div>
                
              </div>
              
             
              <div class="field">
                <button class="button is-primary is-fullwidth" type="submit">
                  <span class="icon">
                    <i class="fas fa-sign-in-alt"></i>
                  </span>
                  <span>Login</span>
                </button>
              </div>
            </form>
            <div class="has-text-centered">
              <p class="is-size-7">Don't have an account? <a href="signup.php">Sign up</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<?php
  include '../layout/footerLayout.php'
?>
