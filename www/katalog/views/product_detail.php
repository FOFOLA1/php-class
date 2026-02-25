<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="page-padded">
    <div class="detail-container">
        <div class="detail-layout">
            <div>
                <h1 class="detail-title-mobile"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="detail-category-mobile">Kategorie: <?= htmlspecialchars($product['category_name']) ?></div>

                <?php if (!empty($images)): ?>
                    <div class="image-browser" id="image-browser">
                        <div class="image-viewport">
                            <?php foreach ($images as $i => $img): ?>
                                <img src="<?= htmlspecialchars($img['image_url']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    data-index="<?= $i ?>"
                                    class="browser-image <?= $i === 0 ? '' : 'hidden' ?>">
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($images) > 1): ?>
                            <button onclick="changeImage(-1)" type="button"
                                class="image-nav-btn image-nav-left">
                                &#8249;
                            </button>
                            <button onclick="changeImage(1)" type="button"
                                class="image-nav-btn image-nav-right">
                                &#8250;
                            </button>
                            <div class="image-dots">
                                <?php foreach ($images as $i => $img): ?>
                                    <button onclick="goToImage(<?= $i ?>)" type="button"
                                        data-dot="<?= $i ?>"
                                        class="browser-dot <?= $i === 0 ? 'browser-dot--active' : '' ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="image-placeholder">
                        <p class="image-placeholder-text">Žádné obrázky k dispozici.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <h1 class="detail-title-desktop"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="detail-category-desktop">Kategorie: <?= htmlspecialchars($product['category_name']) ?></div>

                <div class="detail-price"><?= number_format($product['price'], 2, ',', ' ') ?> Kč</div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <div class="detail-actions">
                        <a href="index.php?action=delete&id=<?= $product['id'] ?>"
                            onclick="return confirm('Jsi si jistý, že chceš smazat tento produkt?');"
                            class="btn-delete">
                            Smazat produkt
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-description">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>

        <a href="index.php" class="link-back">&larr; Zpět na Katalog</a>
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
                    dot.classList.remove('browser-dot--active');
                });
                images[index].classList.remove('hidden');
                if (dots[index]) {
                    dots[index].classList.add('browser-dot--active');
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