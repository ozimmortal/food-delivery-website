<?php
// Set page title
$pageTitle = "Sweet Bites - Join Us";
include './layout/headerLayout.php';
?>

<section class="hero fs is-fullheight">
  <div class="hero-body ">
    <div class="container ">
      <div class="columns is-centered ">
        <div class="column is-8-tablet is-6-desktop is-7-widescreen">
          <div class="box has-text-centered p-6">
            <h1 class="title is-2 mb-6 ">Join Sweet Bites</h1>
            <p class="subtitle is-5 mb-6">Are you a Customer, Delivery Partner, or Restaurant?</p>
            
            <div class="columns is-multiline is-centered ">
              <!-- Customer Option -->
              <div class="column is-4">
                <div class="card role-card" data-role="customer">
                  <div class="card-content">
                    <span class="icon is-large has-text-primary mb-4">
                      <i class="fas fa-utensils fa-3x"></i>
                    </span>
                    <h3 class="title is-4">Customer</h3>
                    <p>Order delicious food from local restaurants</p>
                  </div>
                </div>
              </div>
              
              <!-- Delivery Option -->
              <div class="column is-4">
                <div class="card role-card" data-role="delivery">
                  <div class="card-content">
                    <span class="icon is-large has-text-info mb-4">
                      <i class="fas fa-motorcycle fa-3x"></i>
                    </span>
                    <h3 class="title is-4">Delivery Partner</h3>
                    <p>Deliver food and earn money</p>
                  </div>
                </div>
              </div>
              
              <!-- Restaurant Option -->
              <div class="column is-4">
                <div class="card role-card" data-role="restaurant">
                  <div class="card-content">
                    <span class="icon is-large has-text-danger mb-4">
                      <i class="fas fa-store fa-3x"></i>
                    </span>
                    <h3 class="title is-4">Restaurant</h3>
                    <p>Reach more customers with our platform</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="buttons is-centered mt-6">
              <a href="index.php" class="button is-primary">Back to Home</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// JavaScript to handle role selection and redirection
document.addEventListener('DOMContentLoaded', function() {
  const roleCards = document.querySelectorAll('.role-card');
  
  roleCards.forEach(card => {
    card.addEventListener('click', function() {
      const role = this.getAttribute('data-role');
      
      // Add visual feedback
      roleCards.forEach(c => c.classList.remove('has-background-light'));
      this.classList.add('has-background-light');
      
      // Redirect after a short delay for better UX
      setTimeout(() => {
        switch(role) {
          case 'customer':
            window.location.href = 'auth/customer-signup.php';
            break;
          case 'delivery':
            window.location.href = 'auth/delivery-signup.php';
            break;
          case 'restaurant':
            window.location.href = 'auth/restaurant-signup.php';
            break;
        }
      }, 300);
    });
  });
});
</script>

<style>
.role-card {
  cursor: pointer;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.role-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.role-card .card-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.role-card .title {
  margin-top: 1rem;
}
</style>

<?php
include './layout/footerLayout.php';
?>