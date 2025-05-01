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
                                            <img id="owner-image-preview" src="<?= !empty($owner['profile_pic']) ? '../../' . $owner['profile_pic'] : 'https://via.placeholder.com/150?text=Profile+Picture' ?>" 
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
                                        <input type="text" id="first_name" name="name" value="<?= htmlspecialchars($owner['first_name'] ?? '') ?>" 
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
                                        <button onclick="document.getElementById('add-item-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <form action="add_menu_item.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="restaurant_id" value="<?= $restaurantId ?>">
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Item Image</label>
                                                <div class="mt-1 flex items-center">
                                                    <img id="menu-item-preview" src="data:image/jpeg;base64,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" 
                                                         class="h-32 w-32 rounded-lg object-cover shadow-md">
                                                    <div class="ml-4">
                                                        <input type="file" name="image" accept="image/*" class="file-upload-input" onchange="previewImage(this, 'menu-item-preview')">
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
                                            <button type="button" onclick="document.getElementById('add-item-modal').classList.add('hidden')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                                        <button onclick="document.getElementById('edit-item-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <form action="edit_menu_item.php" method="POST" enctype="multipart/form-data" id="edit-item-form">
                                        <input type="hidden" name="id" id="edit-item-id">
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Item Image</label>
                                                <div class="mt-1 flex items-center">
                                                    <img id="edit-item-preview" src="" class="h-32 w-32 rounded-lg object-cover shadow-md">
                                                    <div class="ml-4">
                                                        <input type="file" name="image" accept="image/*" class="file-upload-input" onchange="previewImage(this, 'edit-item-preview')">
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
                                            <button type="button" onclick="document.getElementById('edit-item-modal').classList.add('hidden')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
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
        function openEditModal(item) {
            const form = document.getElementById('edit-item-form');
            form.querySelector('#edit-item-id').value = item.id;
            form.querySelector('#edit-item-name').value = item.name;
            form.querySelector('#edit-item-description').value = item.description;
            form.querySelector('#edit-item-price').value = item.price;
            form.querySelector('#edit-item-available').checked = item.available;
            
            const preview = document.getElementById('edit-item-preview');
            preview.src = item.image ? '../../' + item.image : 'https://via.placeholder.com/300x200?text=Item+Image';
            
            document.getElementById('edit-item-modal').classList.remove('hidden');
        }

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