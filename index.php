<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sweet Bites | Premium Food Delivery Experience</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    
    :root {
    --primary: #ff7b25;
    --primary-light: #ff9d5c;
    --background: #EDEDEDFF;
    --background-dark: #1a1a1a;
    --text: #363636;
    --text-dark: #f5f5f5;
    --card-bg: #ffffff;
    --card-bg-dark: #2d2d2d;
    --secondary: #ff7b25;
      --dark: #292F36;
      --light: #F7FFF7;
      --accent: #FFE66D
}
   .is-primarys{
    background-color: var(--primary);
    color: white;
   } 
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--light);
      color: var(--dark);
      scroll-behavior: smooth;
    }
    
    .navbar {
      background-color: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
      padding: 1rem 2rem;
      transition: all 0.3s ease;
    }
    
    .navbar.scrolled {
      padding: 0.5rem 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .hero {
      background: linear-gradient(135deg, rgba(78, 205, 196, 0.9), rgba(255, 107, 107, 0.9)), 
                  url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center/cover;
      position: relative;
      overflow: hidden;
    }
    
    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 50%, transparent 0%, rgba(0,0,0,0.2) 100%);
    }
    
    .hero-title {
      font-weight: 700;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .search-bar {
      max-width: 700px;
      margin: 0 auto;
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
      border-radius: 50px;
      overflow: hidden;
    }
    
    .search-input {
      border: none;
      padding: 1.5rem;
      font-size: 1.1rem;
    }
    
    .search-button {
      background: var(--primary);
      color: white;
      border: none;
      transition: all 0.3s ease;
    }
    
    .search-button:hover {
      background: var(--primary-light);
    }
    
    .feature-card {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: 1px solid rgba(0,0,0,0.03);
      height: 100%;
    }
    
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
    
    .feature-icon {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      font-size: 1.8rem;
    }
    
    .cta-section {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      border-radius: 16px;
      overflow: hidden;
      position: relative;
    }
    
    .cta-section::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .app-badge {
      height: 50px;
      transition: transform 0.3s ease;
    }
    
    .app-badge:hover {
      transform: scale(1.05);
    }
    
    .footer {
      background: var(--dark);
      color: white;
    }
    
    .footer-links a {
      color: rgba(255,255,255,0.7);
      transition: color 0.3s ease;
    }
    
    .footer-links a:hover {
      color: white;
    }
    
    .social-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,0.1);
      color: white;
      transition: all 0.3s ease;
    }
    
    .social-icon:hover {
      background: var(--primary);
      transform: translateY(-3px);
    }
    
    /* Floating animation for hero elements */
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }
    
    .floating {
      animation: float 6s ease-in-out infinite;
    }
    
    /* Pulse animation for CTA button */
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
      70% { box-shadow: 0 0 0 15px rgba(255, 107, 107, 0); }
      100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
    }
    
    .pulse {
      animation: pulse 2s infinite;
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
      background: var(--primary);
      border-radius: 10px;
    }
    
    /* Gradient text */
    .gradient-text {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar is-fixed-top" role="navigation" aria-label="main navigation">
  <div class="container">
    <div class="navbar-brand">
      <a class="navbar-item" href="#">
        <span class="icon-text">
          <span class="icon">
            <i class="fas fa-utensils has-text-primary"></i>
          </span>
          <span class="title is-4 has-text-weight-bold gradient-text">Sweet Bites</span>
        </span>
      </a>
      
      <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasic">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>

    <div id="navbarBasic" class="navbar-menu">
      <div class="navbar-end">
        <a class="navbar-item has-text-weight-medium" href="#restaurants">Restaurants</a>
        <a class="navbar-item has-text-weight-medium" href="#how-it-works">How It Works</a>
        
        <div class="navbar-item">
          <div class="buttons">
            <a class="button is-light is-rounded has-text-weight-medium" href="./onboarding.php">Sign Up</a>
            <a class="button is-primary is-rounded has-text-weight-medium pulse" href="./auth/login.php">
              <span class="icon">
                <i class="fas fa-user"></i>
              </span>
              <span>Login</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero is-fullheight">
  <div class="hero-body">
    <div class="container has-text-centered">
      <div class="columns is-centered">
        <div class="column is-8">
          <h1 class="title is-1 hero-title has-text-white mb-5 animate__animated animate__fadeInDown">
            Savor the flavor, delivered to your door
          </h1>
          
          <p class="subtitle is-4 has-text-white mb-6 animate__animated animate__fadeIn animate__delay-1s">
            Discover <span class="has-text-weight-bold">1,00+</span> restaurants and get your favorite meals in minutes
          </p>
          
          <div class="search-bar animate__animated animate__fadeInUp animate__delay-1s">
            <div class="field has-addons">
              <div class="control is-expanded">
                <form action="get">
                    <input class="input search-input" type="text" placeholder="Search for  dishes or cuisines...">
                </form>
              </div>
              <div class="control">
                <button class="button search-button is-large">
                  <i class="fas fa-search"></i>
                  <span class="ml-2">Search</span>
                </button>
              </div>
            </div>
          </div>
          
          <div class="mt-6 animate__animated animate__fadeIn animate__delay-2s">
            <span class="has-text-white mr-4">Popular:</span>
            <a class="tag is-rounded is-light mr-2">Pizza</a>
            <a class="tag is-rounded is-light mr-2">Sushi</a>
            <a class="tag is-rounded is-light mr-2">Burgers</a>
            <a class="tag is-rounded is-light mr-2">Pasta</a>
            <a class="tag is-rounded is-light">Salad</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="hero-foot">
    <div class="container">
      <div class="tabs is-centered">
        <ul>
          <li class="animate__animated animate__fadeInUp animate__delay-2s">
            <a class="has-text-white">
              <span class="icon">
                <i class="fas fa-motorcycle"></i>
              </span>
              <span>Fast Delivery</span>
            </a>
          </li>
          <li class="animate__animated animate__fadeInUp animate__delay-2-5s">
            <a class="has-text-white">
              <span class="icon">
                <i class="fas fa-star"></i>
              </span>
              <span>4.8 Rating</span>
            </a>
          </li>
          <li class="animate__animated animate__fadeInUp animate__delay-3s">
            <a class="has-text-white">
              <span class="icon">
                <i class="fas fa-utensils"></i>
              </span>
              <span>100+ Restaurants</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="section" id="how-it-works">
  <div class="container">
    <div class="has-text-centered mb-6">
      <h2 class="title is-2 mb-3">How Sweet Bites Works</h2>
      <p class="subtitle is-5 has-text-grey">Get your favorite food in 3 simple steps</p>
    </div>
    
    <div class="columns is-centered">
      <div class="column is-4">
        <div class="feature-card animate__animated animate__fadeInUp">
          <div class="feature-icon floating">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <h3 class="title is-4 has-text-centered mb-4">1. Set Your Location</h3>
          <p class="has-text-centered">Tell us where you are and we'll show you all available restaurants in your area that deliver to your doorstep.</p>
        </div>
      </div>
      
      <div class="column is-4">
        <div class="feature-card animate__animated animate__fadeInUp animate__delay-1s">
          <div class="feature-icon floating" style="animation-delay: 0.5s;">
            <i class="fas fa-utensils"></i>
          </div>
          <h3 class="title is-4 has-text-centered mb-4">2. Choose Your Meal</h3>
          <p class="has-text-centered">Browse hundreds of menus from top-rated restaurants and select your favorite dishes.</p>
        </div>
      </div>
      
      <div class="column is-4">
        <div class="feature-card animate__animated animate__fadeInUp animate__delay-2s">
          <div class="feature-icon floating" style="animation-delay: 1s;">
            <i class="fas fa-truck"></i>
          </div>
          <h3 class="title is-4 has-text-centered mb-4">3. Fast Delivery</h3>
          <p class="has-text-centered">Track your order in real-time as our delivery partner brings your food hot and fresh to your door.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Restaurant Showcase -->
<section class="section has-background-light" id="restaurants">
  <div class="container">
    <div class="has-text-centered mb-6">
      <h2 class="title is-2 mb-3">Popular Restaurants</h2>
      <p class="subtitle is-5 has-text-grey">Curated selection of top-rated eateries</p>
    </div>
    
    <div class="columns is-multiline">
      <!-- Restaurant Card 1 -->
      <div class="column is-4">
        <div class="card animate__animated animate__fadeIn">
          <div class="card-image">
            <figure class="image is-4by3">
              <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Italian Restaurant">
            </figure>
          </div>
          <div class="card-content">
            <div class="media">
              <div class="media-content">
                <p class="title is-4"> Cucina</p>
                <p class="subtitle is-6">
                  <span class="icon has-text-warning">
                    <i class="fas fa-star"></i>
                  </span>
                  <span>4.9 (1,24)</span>
                  <span class="ml-3">• Adiss Abeba • $$$</span>
                </p>
              </div>
            </div>
            <div class="content">
              Authentic Italian cuisine with handmade pasta and wood-fired pizzas. 20-30 min delivery.
              <div class="mt-3">
                <span class="tag is-primary is-light">Free Delivery</span>
                <span class="tag is-primary is-light">20% Off First Order</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Restaurant Card 2 -->
      <div class="column is-4">
        <div class="card animate__animated animate__fadeIn">
          <div class="card-image">
            <figure class="image is-4by3">
              <img src="https://lh3.googleusercontent.com/p/AF1QipOoDRh6uAD-Ipw794nAAjFsPMc3PGkri8enjkE_=s1360-w1360-h1020" alt="Sushi Restaurant">
            </figure>
          </div>
          <div class="card-content">
            <div class="media">
              <div class="media-content">
                <p class="title is-4">Etete Restaurant</p>
                <p class="subtitle is-6">
                  <span class="icon has-text-warning">
                    <i class="fas fa-star"></i>
                  </span>
                  <span>4.5 (11)</span>
                  <span class="ml-3">• Adama • $$$$</span>
                </p>
              </div>
            </div>
            <div class="content">
                Has all you can eat · Has outdoor seating · Serves great cocktails
                              <div class="mt-3">
                <span class="tag is-primary is-light">Fastival Special</span>
                <span class="tag is-primary is-light">15% Off Shirro</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Restaurant Card 3 -->
      <div class="column is-4">
        <div class="card animate__animated animate__fadeIn">
          <div class="card-image">
            <figure class="image is-4by3">
              <img src="https://lh3.googleusercontent.com/p/AF1QipNicLMfoQnhmHbU4BTTYg2xmfijX5TUpUA06ksr=w325-h218-n-k-no" alt="Burger Restaurant">
            </figure>
          </div>
          <div class="card-content">
            <div class="media">
              <div class="media-content">
                <p class="title is-4">Mafi Restaurant </p>
                <p class="subtitle is-6">
                  <span class="icon has-text-warning">
                    <i class="fas fa-star"></i>
                  </span>
                  <span>4.2</span>
                  <span class="ml-3">• Adama • $$</span>
                </p>
              </div>
            </div>
            <div class="content">
                Has all you can eat · Has outdoor seating · Serves vegan dishes
              <div class="mt-3">
                <span class="tag is-primary is-light">Combo Deals</span>
                <span class="tag is-primary is-light">Free Fries</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="has-text-centered mt-6">
      <a class="button is-primarys is-medium">
        <span>View All Restaurants</span>
        <span class="icon">
          <i class="fas fa-arrow-right"></i>
        </span>
  </a>
    </div>
  </div>
</section>



<!-- Footer -->
<footer class="footer">
  <div class="container">
    <hr>
    
    <div class="columns is-vcentered">
      <div class="column is-6">
        <p>© 2025 Sweet Bites Technologies Inc. All rights reserved.</p>
      </div>
      <div class="column is-6 has-text-right">
        <div class="buttons is-right">
          <a href="#" class="button is-small is-light is-outlined">Privacy Policy</a>
          <a href="#" class="button is-small is-light is-outlined">Terms of Service</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script>
  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });
  
  // Burger menu functionality
  document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('.navbar-burger');
    const menu = document.querySelector('.navbar-menu');
    
    burger.addEventListener('click', () => {
      burger.classList.toggle('is-active');
      menu.classList.toggle('is-active');
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
    
    // Animate elements when they come into view
    const animateOnScroll = function() {
      const elements = document.querySelectorAll('.animate__animated');
      
      elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementPosition < windowHeight - 100) {
          const animationClass = element.classList[1];
          element.classList.add(animationClass);
        }
      });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on page load
  });
</script>

</body>
</html>