<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savory | Food Delivery Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #f97316 0%, #f59e0b 100%);
        }
        
        .food-card {
            transition: all 0.3s ease;
        }
        
        .food-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .testimonial-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #f97316;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-primary {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(249, 115, 22, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(249, 115, 22, 0.4);
        }
    </style>
</head>
<body class="bg-white text-gray-800">
    <!-- Navigation -->
    <nav class="fixed w-full bg-white shadow-md z-50">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="#" class="text-2xl font-bold text-orange-500 flex items-center">
                        <i class="fas fa-utensils mr-2"></i>
                        Sweet Bite
                    </a>
                    
                </div>
                
                <div class="hidden md:flex space-x-8">
                    <a href="#" class="nav-link">Home</a>
                    <a href="#restaurants" class="nav-link">Restaurants</a>
                    <a href="#how-it-works" class="nav-link">How It Works</a>
                    <a href="#testimonials" class="nav-link">Testimonials</a>
                    <a href="#contact" class="nav-link">Contact</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="auth/login.php" class="text-orange-500 hover:text-orange-600">Login</a>
                    <a href="onboarding.php" class="bg-orange-500 text-white px-4 py-2 rounded-full btn-primary">
                        Sign Up
                    </a>
                </div>
                
                <button class="md:hidden text-gray-600 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient pt-32 pb-20 text-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">
                        Delicious food delivered to your doorstep
                    </h1>
                    <p class="text-xl mb-8 opacity-90">
                        Order from your favorite local restaurants with just a few taps and enjoy restaurant-quality meals in the comfort of your home.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="auth/register.php" class="bg-white text-orange-500 px-6 py-3 rounded-full text-center font-semibold btn-primary">
                            Order Now
                        </a>
                        <a href="#restaurants" class="border-2 border-white text-white px-6 py-3 rounded-full text-center font-semibold hover:bg-white hover:text-orange-500 transition">
                            Browse Restaurants
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2 flex justify-center">
                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Delicious food delivery" 
                         class="rounded-lg shadow-2xl w-full max-w-md transform rotate-2 hover:rotate-0 transition duration-500">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Restaurants -->
    <section id="restaurants" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Featured Restaurants</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Discover the best local restaurants that deliver to your area. From cozy cafes to gourmet dining, we've got you covered.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Restaurant 1 -->
                <div class="food-card bg-white rounded-xl overflow-hidden shadow-md">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                             alt="Italian Restaurant" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 right-4 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            4.8 ★
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">Bella Italia</h3>
                        <p class="text-gray-600 mb-4">Authentic Italian cuisine with handmade pasta and wood-fired pizzas.</p>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">
                                <i class="fas fa-clock mr-1"></i> 25-35 min
                            </span>
                            <span class="text-gray-500">
                                <i class="fas fa-dollar-sign mr-1"></i> $$
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Restaurant 2 -->
                <div class="food-card bg-white rounded-xl overflow-hidden shadow-md">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                             alt="Sushi Restaurant" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 right-4 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            4.9 ★
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">Tokyo Sushi</h3>
                        <p class="text-gray-600 mb-4">Fresh sushi and Japanese specialties prepared by master chefs.</p>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">
                                <i class="fas fa-clock mr-1"></i> 20-30 min
                            </span>
                            <span class="text-gray-500">
                                <i class="fas fa-dollar-sign mr-1"></i> $$$
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Restaurant 3 -->
                <div class="food-card bg-white rounded-xl overflow-hidden shadow-md">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1559847844-5315695dadae?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                             alt="Burger Joint" 
                             class="w-full h-48 object-cover">
                        <div class="absolute top-4 right-4 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            4.7 ★
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">Burger Haven</h3>
                        <p class="text-gray-600 mb-4">Gourmet burgers with hand-cut fries and craft beer selection.</p>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">
                                <i class="fas fa-clock mr-1"></i> 15-25 min
                            </span>
                            <span class="text-gray-500">
                                <i class="fas fa-dollar-sign mr-1"></i> $
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="#" class="inline-block bg-orange-500 text-white px-6 py-3 rounded-full font-semibold btn-primary">
                    View All Restaurants
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">How It Works</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Getting your favorite meals delivered has never been easier. Just follow these simple steps.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center px-6">
                    <div class="bg-orange-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-orange-500 text-3xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Choose Your Restaurant</h3>
                    <p class="text-gray-600">
                        Browse through our selection of local restaurants and cuisines to find what you're craving.
                    </p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center px-6">
                    <div class="bg-orange-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-orange-500 text-3xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Customize Your Order</h3>
                    <p class="text-gray-600">
                        Select your favorite dishes, customize them to your liking, and add them to your cart.
                    </p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center px-6">
                    <div class="bg-orange-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-orange-500 text-3xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Track & Enjoy</h3>
                    <p class="text-gray-600">
                        Check out securely and track your order in real-time until it arrives at your doorstep.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Dishes -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Popular Dishes</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    These customer favorites are always a hit. Try them today!
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Dish 1 -->
                <div class="food-card bg-white rounded-lg overflow-hidden shadow-md">
                    <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Margherita Pizza" 
                         class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">Margherita Pizza</h3>
                        <p class="text-gray-600 text-sm mb-3">Classic tomato, mozzarella and basil</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-orange-500">$12.99</span>
                            <button class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm hover:bg-orange-600 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Dish 2 -->
                <div class="food-card bg-white rounded-lg overflow-hidden shadow-md">
                    <img src="https://images.unsplash.com/photo-1563379926898-05f4575a45d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="California Roll" 
                         class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">California Roll</h3>
                        <p class="text-gray-600 text-sm mb-3">Crab, avocado and cucumber</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-orange-500">$9.99</span>
                            <button class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm hover:bg-orange-600 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Dish 3 -->
                <div class="food-card bg-white rounded-lg overflow-hidden shadow-md">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Cheeseburger" 
                         class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">Classic Cheeseburger</h3>
                        <p class="text-gray-600 text-sm mb-3">Beef patty with cheese and veggies</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-orange-500">$8.99</span>
                            <button class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm hover:bg-orange-600 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Dish 4 -->
                <div class="food-card bg-white rounded-lg overflow-hidden shadow-md">
                    <img src="https://images.unsplash.com/photo-1544025162-d76694265947?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Pasta Carbonara" 
                         class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">Pasta Carbonara</h3>
                        <p class="text-gray-600 text-sm mb-3">Creamy sauce with pancetta and egg</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-orange-500">$14.99</span>
                            <button class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm hover:bg-orange-600 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-20 bg-orange-500 text-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">What Our Customers Say</h2>
                <p class="opacity-90 max-w-2xl mx-auto">
                    Don't just take our word for it. Here's what our customers have to say about their experience.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="testimonial-card p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" 
                             alt="Sarah J." 
                             class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Sarah J.</h4>
                            <div class="flex text-yellow-300">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="opacity-90">
                        "The food arrived hot and fresh, exactly as described. I love how easy it is to track my order in real-time. Will definitely order again!"
                    </p>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="testimonial-card p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/45.jpg" 
                             alt="Michael T." 
                             class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Michael T.</h4>
                            <div class="flex text-yellow-300">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="opacity-90">
                        "As a busy professional, Savory has been a lifesaver. The delivery is always prompt and the food quality is consistently excellent."
                    </p>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="testimonial-card p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" 
                             alt="Emily R." 
                             class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Emily R.</h4>
                            <div class="flex text-yellow-300">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="opacity-90">
                        "The variety of restaurants available is amazing. I've discovered so many new favorite places thanks to Savory!"
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- App Download -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-10 md:mb-0 flex justify-center">
                    <img src="https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Savory Mobile App" 
                         class="rounded-lg shadow-xl w-full max-w-sm">
                </div>
                <div class="md:w-1/2 md:pl-12">
                    <h2 class="text-3xl font-bold mb-6">Get the Savory App</h2>
                    <p class="text-gray-600 mb-8">
                        Download our mobile app for faster ordering, exclusive deals, and a seamless food delivery experience wherever you are.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="#" class="bg-black text-white px-6 py-3 rounded-lg flex items-center justify-center">
                            <i class="fab fa-apple text-2xl mr-2"></i>
                            <div class="text-left">
                                <div class="text-xs">Download on the</div>
                                <div class="font-semibold">App Store</div>
                            </div>
                        </a>
                        <a href="#" class="bg-black text-white px-6 py-3 rounded-lg flex items-center justify-center">
                            <i class="fab fa-google-play text-2xl mr-2"></i>
                            <div class="text-left">
                                <div class="text-xs">Get it on</div>
                                <div class="font-semibold">Google Play</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & Newsletter -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h2 class="text-3xl font-bold mb-6">Contact Us</h2>
                    <p class="text-gray-600 mb-6">
                        Have questions or feedback? We'd love to hear from you!
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-orange-500 mr-4 text-xl"></i>
                            <span>support@savory.com</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone-alt text-orange-500 mr-4 text-xl"></i>
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-orange-500 mr-4 text-xl"></i>
                            <span>123 Food Street, Culinary City, FC 12345</span>
                        </div>
                    </div>
                </div>
                <div class="md:w-1/2 md:pl-12">
                    <h2 class="text-3xl font-bold mb-6">Newsletter</h2>
                    <p class="text-gray-600 mb-6">
                        Subscribe to our newsletter to receive exclusive deals and updates about new restaurants in your area.
                    </p>
                    <form class="flex">
                        <input type="email" placeholder="Your email address" 
                               class="px-4 py-3 rounded-l-lg border border-r-0 focus:outline-none focus:ring-2 focus:ring-orange-500 w-full">
                        <button type="submit" class="bg-orange-500 text-white px-6 py-3 rounded-r-lg font-semibold hover:bg-orange-600 transition">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-utensils mr-2 text-orange-500"></i>
                        Savory
                    </h3>
                    <p class="text-gray-400">
                        Bringing your favorite restaurant meals to your doorstep with speed and care.
                    </p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="#restaurants" class="text-gray-400 hover:text-white transition">Restaurants</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition">How It Works</a></li>
                        <li><a href="#testimonials" class="text-gray-400 hover:text-white transition">Testimonials</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Refund Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">FAQs</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-4">Partners</h4>
                    <p class="text-gray-400 mb-4">
                        Are you a restaurant owner interested in joining our platform?
                    </p>
                    <a href="#" class="inline-block border border-orange-500 text-orange-500 px-4 py-2 rounded hover:bg-orange-500 hover:text-white transition">
                        Partner With Us
                    </a>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> Savory. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle functionality would go here
        document.addEventListener('DOMContentLoaded', function() {
            // You can add any JavaScript interactions here
            // For example, mobile menu toggle, smooth scrolling, etc.
        });
    </script>
</body>
</html>