<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog</title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="bg-gray-100 min-h-screen font-sans">
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Product Catalog</h1>
            <div class="text-sm space-x-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-600">Logged in as <span class="font-medium"><?= $_SESSION['role'] ?></span></span>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="index.php?action=add" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Add Product</a>
                    <?php endif; ?>
                    <a href="index.php?action=logout" class="text-red-600 hover:underline">Logout</a>
                <?php else: ?>
                    <a href="index.php?action=login" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Login</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <?php if (isset($currentCategory) && $currentCategory): ?>
                <div class="mb-3">
                    <a href="index.php<?= $currentCategory['parent_id'] ? '?category=' . $currentCategory['parent_id'] : '' ?>"
                        class="text-blue-600 hover:underline text-sm">
                        &larr; Back to <?= $currentCategory['parent_id'] ? 'Parent Category' : 'Main Menu' ?>
                    </a>
                </div>
                <h2 class="text-xl font-semibold text-gray-700 mb-4"><?= htmlspecialchars($currentCategory['name']) ?></h2>
            <?php else: ?>
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Main Menu</h2>
            <?php endif; ?>

            <?php if (!empty($categories)): ?>
                <div class="mb-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Subcategories</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?category=<?= $cat['id'] ?>"
                                class="group relative flex items-center gap-2 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                <span class="text-gray-500 group-hover:text-blue-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </span>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors truncate">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <span onclick="event.preventDefault(); event.stopPropagation(); if(confirm('Delete category <?= htmlspecialchars($cat['name']) ?> and all its items/subcategories?')) window.location='index.php?action=delete_category&id=<?= $cat['id'] ?>';"
                                        class="absolute top-1 right-1 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer p-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-md">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Add Category</h4>
                    <form action="index.php?action=add_category" method="POST" class="flex items-center gap-2">
                        <input type="text" name="name" placeholder="Category Name" required
                            class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php if (isset($currentCategory)): ?>
                            <input type="hidden" name="parent_id" value="<?= $currentCategory['id'] ?>">
                        <?php endif; ?>
                        <button type="submit"
                            class="bg-green-600 text-white px-3 py-1.5 rounded-md text-sm hover:bg-green-700">Add</button>
                    </form>
                </div>
            <?php endif; ?>

            <form method="GET" class="mt-4 flex items-center gap-2">
                <?php if (isset($currentCategory) && $currentCategory): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory['id']) ?>">
                <?php endif; ?>

                <label class="text-sm text-gray-600">Sort by:</label>
                <select name="sort" onchange="this.form.submit()"
                    class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="name" <?= isset($_GET['sort']) && $_GET['sort'] == 'name' ? 'selected' : '' ?>>Name</option>
                    <option value="price" <?= isset($_GET['sort']) && $_GET['sort'] == 'price' ? 'selected' : '' ?>>Price</option>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="w-full h-48 object-cover">
                    <?php endif; ?>
                    <div class="p-4">
                        <h3 class="font-semibold text-lg mb-1">
                            <a href="index.php?action=detail&id=<?= $product['id'] ?>"
                                class="text-gray-800 hover:text-blue-600"><?= htmlspecialchars($product['name']) ?></a>
                        </h3>
                        <p class="text-gray-500 text-xs mb-2"><?= htmlspecialchars($product['category_name']) ?></p>
                        <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : '') ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-green-600 font-bold text-lg">$<?= number_format($product['price'], 2) ?></span>
                            <a href="index.php?action=detail&id=<?= $product['id'] ?>"
                                class="text-blue-600 text-sm hover:underline">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>