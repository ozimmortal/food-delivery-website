<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Fetch all needed data
$userId = $_SESSION['user_id'];
$restaurant = [];
$owner = [];
$menuItems = [];

// Get restaurant data
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE user_id = ?");
$stmt->execute([$userId]);
$restaurant = $stmt->fetch();
$restaurantId = $restaurant['id'] ?? null;

// Get owner data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$owner = $stmt->fetch();

// Get menu items if restaurant exists
if ($restaurantId) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id = ? ORDER BY name");
    $stmt->execute([$restaurantId]);
    $menuItems = $stmt->fetchAll();
}

// Determine active tab
$activeTab = $_GET['tab'] ?? 'restaurant';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css" />
    <style>
        .file-upload-input {
            position: absolute;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            outline: none;
            opacity: 0;
            cursor: pointer;
        }
        .image-upload-wrap {
            border: 2px dashed #d1d5db;
            position: relative;
        }
        .image-upload-wrap:hover {
            border-color: #f97316;
            background-color: rgba(249, 115, 22, 0.05);
        }
        .drag-text {
            text-align: center;
        }
        .page-content {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            margin-left: 250px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="page-content">
        <?php include('./sidebar.php'); ?>
        
        <div class="main-content">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-utensils mr-2 text-orange-500"></i>
                        Restaurant Settings
                    </h1>
                    <a href="../dashboard.php" class="text-orange-500 hover:text-orange-700">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </header>

            <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <div class="px-4 py-6 sm:px-0">
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <nav class="flex -mb-px">
                                <a href="?tab=restaurant" class="<?= $activeTab === 'restaurant' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                    <i class="fas fa-store mr-2"></i>Restaurant Info
                                </a>
                                <a href="?tab=owner" class="<?= $activeTab === 'owner' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                    <i class="fas fa-user mr-2"></i>Owner Info
                                </a>
                              
                                <a href="?tab=menu" class="<?= $activeTab === 'menu' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                    <i class="fas fa-list-alt mr-2"></i>Menu
                                </a>
                            </nav>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle h-5 w-5 text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700"><?= htmlspecialchars($_GET['success']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php elseif (isset($_GET['error'])): ?>
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle h-5 w-5 text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700"><?= htmlspecialchars($_GET['error']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Restaurant Info Tab -->
                        <div id="restaurant-tab" class="tab-content <?= $activeTab === 'restaurant' ? 'active' : '' ?>">
                            <?php if (!$restaurant): ?>
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                                <p class="text-sm text-red-700">Restaurant not found</p>
                            </div>
                            <?php else: ?>
                            <form action="updateRestaurant.php" method="POST" enctype="multipart/form-data" class="px-6 py-5">
                                <input type="hidden" name="id" value="<?= $restaurant['id'] ?>">

                                <div class="mb-8">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant Image</label>
                                    <div class="mt-1 flex items-center">
                                        <div class="relative group">
                                            <img id="restaurant-image-preview" src="<?= !empty($restaurant['image']) ? '../../' . $restaurant['image'] : 'https://via.placeholder.com/300x200?text=Restaurant+Image' ?>" 
                                                 class="h-32 w-32 rounded-lg object-cover shadow-md">
                                        </div>
                                        <div class="ml-4">
                                            <div class="image-upload-wrap relative w-64 h-32 flex items-center justify-center rounded-lg">
                                                <input id="restaurant-image-upload" type="file" name="image" accept="image/*" class="file-upload-input">
                                                <div class="drag-text">
                                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Restaurant Name</label>
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($restaurant['name']) ?>" 
                                           class="focus:ring-orange-500 focus:border-orange-500 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                <div class="mb-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea id="description" name="description" rows="3" 
                                              class="focus:ring-orange-500 focus:border-orange-500 block w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($restaurant['description'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-6">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <textarea id="address" name="address" rows="2" 
                                              class="focus:ring-orange-500 focus:border-orange-500 block w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($restaurant['address']) ?></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($restaurant['phone']) ?>" 
                                               class="focus:ring-orange-500 focus:border-orange-500 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    </div>
                                  
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <i class="fas fa-save mr-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>

                        <!-- Owner Info Tab -->
                        <div id="owner-tab" class="tab-content <?= $activeTab === 'owner' ? 'active' : '' ?>">
                            <form action="update_owner.php" method="POST" enctype="multipart/form-data" class="px-6 py-5">
                                <input type="hidden" name="id" value="<?= $owner['id'] ?>">

                                <div class="mb-8">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                                    <div class="mt-1 flex items-center">
                                        <div class="relative group">
                                            <img id="owner-image-preview" src="<?= !empty($owner['image']) ? '../../' . $owner['image'] : 'https://via.placeholder.com/150?text=Profile+Picture' ?>" 
                                                 class="h-32 w-32 rounded-full object-cover shadow-md">
                                        </div>
                                        <div class="ml-4">
                                            <div class="image-upload-wrap relative w-64 h-32 flex items-center justify-center rounded-lg">
                                                <input id="owner-image-upload" type="file" name="profile_pic" accept="image/*" class="file-upload-input">
                                                <div class="drag-text">
                                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                        <input type="text" id="first_name" name="name" value="<?= htmlspecialchars($owner['name'] ?? '') ?>" 
                                               class="focus:ring-orange-500 focus:border-orange-500 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    </div>
              
                                </div>

                                <div class="mb-6">
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($owner['email'] ?? '') ?>" 
                                       disabled    class="focus:ring-orange-500 focus:border-orange-500 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                <div class="mb-6">
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($owner['phone'] ?? '') ?>" 
                                           class="focus:ring-orange-500 focus:border-orange-500 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <i class="fas fa-save mr-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- Menu Management Tab -->
                        <div id="menu-tab" class="tab-content <?= $activeTab === 'menu' ? 'active' : '' ?>">
                            <?php if (!$restaurantId): ?>
                            <div class="px-6 py-5">
                                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                                    <p class="text-sm text-red-700">You need to create a restaurant first</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="px-6 py-5">
                                <button onclick="document.getElementById('add-item-modal').classList.remove('hidden')" 
                                        class="mb-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <i class="fas fa-plus mr-2"></i> Add New Item
                                </button>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($menuItems as $item): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <?php if ($item['image']): ?>
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <img class="h-10 w-10 rounded-full object-cover" src="../../<?= htmlspecialchars($item['image']) ?>" alt="">
                                                        </div>
                                                        <?php endif; ?>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900 max-w-xs truncate"><?= htmlspecialchars($item['description']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    $<?= number_format($item['price'], 2) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $item['available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                        <?= $item['available'] ? 'Available' : 'Unavailable' ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($item)) ?>)" class="text-orange-600 hover:text-orange-900 mr-3">Edit</button>
                                                    <form action="delete_menu_item.php" method="POST" class="inline">
                                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Add Item Modal -->
                                <div id="add-item-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
                                    <div class="relative top-20 mx-auto p-5 border w-full md:w-1/2 shadow-lg rounded-md bg-white">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-lg font-medium text-gray-900">Add Menu Item</h3>
                                            <button onclick="closeModal('add-item-modal')" class="text-gray-400 hover:text-gray-500">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <form action="add_menu_item.php" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="restaurant_id" value="<?= $restaurantId ?>">
                                            
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Item Image</label>
                                                    <div class="mt-1 flex items-center">
                                                        <img id="menu-item-preview" src="https://via.placeholder.com/300x200?text=Item+Image" 
                                                            class="h-32 w-32 rounded-lg object-cover shadow-md">
                                                        <div class="ml-4">
                                                            <div class="image-upload-wrap relative w-64 h-32 flex items-center justify-center rounded-lg">
                                                                <input type="file" name="image" accept="image/*" class="file-upload-input" onchange="previewImage(this, 'menu-item-preview')">
                                                                <div class="drag-text">
                                                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                                    <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                                    <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"></textarea>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Price</label>
                                                    <input type="number" name="price" step="0.01" min="0" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                                                </div>
                                                
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="available" id="available" checked class="h-4 w-4 text-orange-600 border-gray-300 rounded">
                                                    <label for="available" class="ml-2 block text-sm text-gray-700">Available</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-5 flex justify-end">
                                                <button type="button" onclick="closeModal('add-item-modal')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                                                    Add Item
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            <!-- Edit Item Modal -->
                                <div id="edit-item-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
                                    <div class="relative top-20 mx-auto p-5 border w-full md:w-1/2 shadow-lg rounded-md bg-white">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-lg font-medium text-gray-900">Edit Menu Item</h3>
                                            <button onclick="closeModal('edit-item-modal')" class="text-gray-400 hover:text-gray-500">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <form action="edit_menu_item.php" method="POST" enctype="multipart/form-data" id="edit-item-form">
                                            <input type="hidden" name="id" id="edit-item-id">
                                            <input type="hidden" name="restaurant_id" value="<?= $restaurantId ?>">
                                            
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Item Image</label>
                                                    <div class="mt-1 flex items-center">
                                                        <img id="edit-item-preview" src="" class="h-32 w-32 rounded-lg object-cover shadow-md">
                                                        <div class="ml-4">
                                                            <div class="image-upload-wrap relative w-64 h-32 flex items-center justify-center rounded-lg">
                                                                <input type="file" name="image" accept="image/*" class="file-upload-input" onchange="previewImage(this, 'edit-item-preview')">
                                                                <div class="drag-text">
                                                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                                    <input type="text" name="name" id="edit-item-name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                                    <textarea name="description" id="edit-item-description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"></textarea>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Price</label>
                                                    <input type="number" name="price" id="edit-item-price" step="0.01" min="0" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                                                </div>
                                                
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="available" id="edit-item-available" class="h-4 w-4 text-orange-600 border-gray-300 rounded">
                                                    <label for="edit-item-available" class="ml-2 block text-sm text-gray-700">Available</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-5 flex justify-end">
                                                <button type="button" onclick="closeModal('edit-item-modal')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Image preview function
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Open edit modal with item data
       // Open modal function
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    // Close modal function
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Open edit modal with item data
    function openEditModal(item) {
        // Set form values
        document.getElementById('edit-item-id').value = item.id;
        document.getElementById('edit-item-name').value = item.name;
        document.getElementById('edit-item-description').value = item.description;
        document.getElementById('edit-item-price').value = item.price;
        document.getElementById('edit-item-available').checked = item.available;
        
        // Set image preview
        const preview = document.getElementById('edit-item-preview');
        if (item.image) {
            preview.src = '../../' + item.image;
        } else {
            preview.src = 'https://via.placeholder.com/300x200?text=Item+Image';
        }
        
        // Open modal
        openModal('edit-item-modal');
    }


    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Add Item button click handler
        document.querySelector('[onclick*="add-item-modal"]').addEventListener('click', function() {
            openModal('add-item-modal');
        });

        // Initialize image upload previews
        const initImageUpload = (inputId, previewId) => {
            const input = document.querySelector(`#${inputId} input[type="file"]`);
            if (input) {
                input.addEventListener('change', function() {
                    previewImage(this, previewId);
                });
            }
        };

        initImageUpload('restaurant-image-upload', 'restaurant-image-preview');
        initImageUpload('owner-image-upload', 'owner-image-preview');
        initImageUpload('menu-item-upload', 'menu-item-preview');
        initImageUpload('edit-item-upload', 'edit-item-preview');
    });

        // Initialize image previews
        document.addEventListener('DOMContentLoaded', function() {
            const restaurantUpload = document.getElementById('restaurant-image-upload');
            if (restaurantUpload) {
                restaurantUpload.addEventListener('change', function() {
                    previewImage(this, 'restaurant-image-preview');
                });
            }

            const ownerUpload = document.getElementById('owner-image-upload');
            if (ownerUpload) {
                ownerUpload.addEventListener('change', function() {
                    previewImage(this, 'owner-image-preview');
                });
            }
        });
    </script>
</body>
</html>