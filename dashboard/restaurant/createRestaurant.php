<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Your Restaurant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto my-10 p-6 bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Form Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Add Your Restaurant</h1>
            <p class="text-gray-600 mt-2">Share your culinary vision with the world</p>
        </div>

        <!-- Restaurant Image Upload Preview -->
        <div class="mb-8 text-center">
            <div id="image-preview" class="hidden mb-4">
                <img id="preview" class="h-48 w-full object-cover rounded-lg mx-auto shadow-md" src="#" alt="Restaurant preview">
            </div>
            <label for="image-upload" class="cursor-pointer">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-orange-400 transition-colors">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-camera text-4xl text-orange-500 mb-2"></i>
                        <p class="text-gray-700 font-medium">Upload Restaurant Image</p>
                        <p class="text-sm text-gray-500 mt-1">JPEG or PNG, Max 2MB</p>
                    </div>
                </div>
                <input id="image-upload" type="file" name="image" accept="image/*" class="hidden">
            </label>
        </div>

        <!-- Restaurant Form -->
        <form action="resPros.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Restaurant Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Restaurant Name</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-utensils"></i>
                    </span>
                    <input type="text" id="name" name="name" required
                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           placeholder="e.g. Gourmet Paradise">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                    <textarea id="address" name="address" rows="3" required
                              class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                              placeholder="Full address including city and postal code"></textarea>
                </div>
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="tel" id="phone" name="phone" required
                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           placeholder="e.g. +1 234 567 8900">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-plus-circle mr-2"></i> Create Restaurant
                </button>
            </div>
        </form>
    </div>

    <!-- Image Preview Script -->
    <script>
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview').src = event.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>