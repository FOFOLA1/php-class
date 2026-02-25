<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto bg-white p-6 md:p-8 rounded-lg shadow-md">

        <!-- Mobile layout: stacked / Desktop layout: 2 columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">

            <!-- Left column: Image browser -->
            <div>
                <!-- Name & category (mobile only, hidden on desktop) -->
                <h1 class="text-2xl font-bold text-gray-800 mb-1 md:hidden"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="text-gray-500 text-sm mb-4 md:hidden">Category: <?= htmlspecialchars($product['category_name']) ?></div>

                <?php if (!empty($images)): ?>
                    <div class="relative w-full" id="image-browser">
                        <!-- Current image -->
                        <div class="w-full aspect-square bg-gray-50 rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center">
                            <?php foreach ($images as $i => $img): ?>
                                <img src="<?= htmlspecialchars($img['image_url']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    data-index="<?= $i ?>"
                                    class="browser-image max-w-full max-h-full object-contain <?= $i === 0 ? '' : 'hidden' ?>">
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($images) > 1): ?>
                            <!-- Left arrow -->
                            <button onclick="changeImage(-1)" type="button"
                                class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white w-10 h-10 rounded-full flex items-center justify-center text-xl transition-colors">
                                &#8249;
                            </button>
                            <!-- Right arrow -->
                            <button onclick="changeImage(1)" type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white w-10 h-10 rounded-full flex items-center justify-center text-xl transition-colors">
                                &#8250;
                            </button>
                            <!-- Dots indicator -->
                            <div class="flex justify-center gap-2 mt-3">
                                <?php foreach ($images as $i => $img): ?>
                                    <button onclick="goToImage(<?= $i ?>)" type="button"
                                        data-dot="<?= $i ?>"
                                        class="browser-dot w-2.5 h-2.5 rounded-full transition-colors <?= $i === 0 ? 'bg-blue-600' : 'bg-gray-300' ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="w-full aspect-square bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center">
                        <p class="text-gray-400 italic">No images available.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right column: Name, category, price, admin actions -->
            <div class="flex flex-col">
                <!-- Name & category (desktop only, hidden on mobile) -->
                <h1 class="text-3xl font-bold text-gray-800 mb-1 hidden md:block"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="text-gray-500 text-sm mb-4 hidden md:block">Category: <?= htmlspecialchars($product['category_name']) ?></div>

                <div class="text-green-600 text-3xl font-bold mb-4">$<?= htmlspecialchars($product['price']) ?></div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <div class="mb-4">
                        <a href="index.php?action=delete&id=<?= $product['id'] ?>"
                            onclick="return confirm('Are you sure you want to delete this product?');"
                            class="inline-block bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm">
                            Delete Product
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description full-width below both columns -->
        <div class="text-gray-700 leading-relaxed mt-6">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>

        <a href="index.php" class="inline-block mt-6 text-blue-600 hover:underline">&larr; Back to Catalog</a>
    </div>

    <?php if (!empty($images) && count($images) > 1): ?>
        <script>
            let currentIndex = 0;
            const images = document.querySelectorAll('.browser-image');
            const dots = document.querySelectorAll('.browser-dot');
            const total = images.length;

            function showImage(index) {
                images.forEach(img => img.classList.add('hidden'));
                dots.forEach(dot => {
                    dot.classList.remove('bg-blue-600');
                    dot.classList.add('bg-gray-300');
                });
                images[index].classList.remove('hidden');
                if (dots[index]) {
                    dots[index].classList.remove('bg-gray-300');
                    dots[index].classList.add('bg-blue-600');
                }
                currentIndex = index;
            }

            function changeImage(direction) {
                let newIndex = (currentIndex + direction + total) % total;
                showImage(newIndex);
            }

            function goToImage(index) {
                showImage(index);
            }
        </script>
    <?php endif; ?>
</body>

</html>