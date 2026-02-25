<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog</title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="page-catalog">
    <div class="container-xl">
        <div class="catalog-header">
            <h1 class="page-title-lg">Katalog</h1>
            <div class="catalog-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="nav-user-info">Přihlášen jako <span class="nav-role"><?= htmlspecialchars($_SESSION['role']) ?></span></span>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="index.php?action=add" class="btn-nav">Přidat produkt</a>
                    <?php endif; ?>
                    <a href="index.php?action=logout" class="link-danger">Odhlásit se</a>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn-nav">Přihlásit se</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-card">
            <?php if (isset($currentCategory) && $currentCategory): ?>
                <div class="back-nav-wrapper">
                    <a href="index.php<?= $currentCategory['parent_id'] ? '?category=' . $currentCategory['parent_id'] : '' ?>"
                        class="link-back-nav">
                        &larr; Zpět na <?= $currentCategory['parent_id'] && isset($parentCategory['name']) ? htmlspecialchars($parentCategory['name']) : 'Hlavní menu' ?>
                    </a>
                </div>
                <h2 class="section-title"><?= htmlspecialchars($currentCategory['name']) ?></h2>
            <?php else: ?>
                <h2 class="section-title">Hlavní menu</h2>
            <?php endif; ?>

            <?php if (!empty($categories)): ?>
                <div class="subcategories">
                    <p class="category-label">Podkategorie</p>
                    <div class="category-grid">
                        <?php foreach ($categories as $cat): ?>
                            <?php $deleteCategoryText = 'Odebrat kategorii ' . $cat['name'] . ' a všechny její položky/podkategorie?'; ?>
                            <a href="index.php?category=<?= $cat['id'] ?>"
                                class="category-link">
                                <span class="category-icon">
                                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </span>
                                <span class="category-name">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <span onclick="event.preventDefault(); event.stopPropagation(); if(confirm(<?= htmlspecialchars(json_encode($deleteCategoryText, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>)) window.location='index.php?action=delete_category&id=<?= (int) $cat['id'] ?>';"
                                        class="category-delete">
                                        <svg class="icon-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="add-category-box">
                    <h4 class="add-category-title">Přidat kategorii</h4>
                    <form action="index.php?action=add_category" method="POST" class="add-category-form">
                        <input type="text" name="name" placeholder="Název kategorie" required
                            class="add-category-input">
                        <?php if (isset($currentCategory)): ?>
                            <input type="hidden" name="parent_id" value="<?= (int) $currentCategory['id'] ?>">
                        <?php endif; ?>
                        <button type="submit"
                            class="add-category-btn">Přidat</button>
                    </form>
                </div>
            <?php endif; ?>

            <form method="GET" class="sort-form">
                <?php if (isset($currentCategory) && $currentCategory): ?>
                    <input type="hidden" name="category" value="<?= (int) $currentCategory['id'] ?>">
                <?php endif; ?>

                <label class="sort-label">Seřadit podle:</label>
                <select name="sort" onchange="this.form.submit()"
                    class="sort-select">
                    <option value="name" <?= isset($_GET['sort']) && $_GET['sort'] == 'name' ? 'selected' : '' ?>>Název</option>
                    <option value="price" <?= isset($_GET['sort']) && $_GET['sort'] == 'price' ? 'selected' : '' ?>>Cena</option>
                </select>
            </form>
        </div>

        <div class="product-grid">
            <?php
            $productsPerPage = 12;
            $totalProducts = count($products);
            $totalPages = (int) ceil($totalProducts / $productsPerPage);
            if ($totalPages < 1) {
                $totalPages = 1;
            }

            if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                $page = (int) $_GET['page'];
            } else {
                $page = 1;
            }
            if ($page < 1) {
                $page = 1;
            }
            if ($page > $totalPages) {
                $page = $totalPages;
            }

            $offset = ($page - 1) * $productsPerPage;
            $displayed_products = array_slice($products, $offset, $productsPerPage);
            foreach ($displayed_products as $product):
            ?>
                <div class="product-card">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="product-card-image">
                    <?php endif; ?>
                    <div class="product-card-body">
                        <h3 class="product-card-title">
                            <a href="index.php?action=detail&id=<?= $product['id'] ?>"
                                class="product-card-title-link"><?= htmlspecialchars($product['name']) ?></a>
                        </h3>
                        <p class="product-card-category"><?= htmlspecialchars($product['category_name']) ?></p>
                        <p class="product-card-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : '') ?></p>
                        <div class="product-card-footer">
                            <span class="product-card-price"><?= number_format($product['price'], 2, ',', ' ') ?> Kč</span>
                            <a href="index.php?action=detail&id=<?= $product['id'] ?>"
                                class="link-sm">Zobrazit detaily</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="pagination">
            <?php
            $paginationQuery = $_GET;
            unset($paginationQuery['page']);

            if ($page > 1):
                $prevQuery = http_build_query(array_merge($paginationQuery, ['page' => $page - 1]));
            ?>
                <a href="index.php?<?= htmlspecialchars($prevQuery) ?>"
                    class="btn-pagination">Předchozí</a>
            <?php endif; ?>
            <?php
            if ($page < $totalPages):
                $nextQuery = http_build_query(array_merge($paginationQuery, ['page' => $page + 1]));
            ?>
                <a href="index.php?<?= htmlspecialchars($nextQuery) ?>"
                    class="btn-pagination">Další</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>