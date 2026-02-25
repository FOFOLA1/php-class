<?php
session_start();
require_once 'PizzaData.php';
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sestavení pizzy</title>
    <link rel="stylesheet" href="output.css">
</head>

<body>
    <header>
        <h1>Sestavení pizzy</h1>
        <div class="cart-link">
            <a href="cart.php">Přejít do košíku (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
        </div>
    </header>
    <main>
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Objednávka byla úspěšně odeslána!</div>
        <?php endif; ?>

        <form action="cart.php" method="post" id="pizzaForm">
            <input type="hidden" name="action" value="add">

            <section>
                <div class="section-header">
                    <h2>Velikost</h2>
                    <span id="sizeTotal" class="section-total">0 Kč</span>
                </div>
                <?php foreach ($sizes as $key => $component): ?>
                    <label title="<?php echo $component->getPrice(); ?> Kč">
                        <input type="radio" name="size" value="<?php echo $key; ?>"
                            data-price="<?php echo $component->getPrice(); ?>"
                            <?php echo $key === '30cm' ? 'checked' : ''; ?>>
                        <?php echo $component->getName(); ?>
                    </label>
                <?php endforeach; ?>
            </section>

            <section>
                <div class="section-header">
                    <h2>Základ (vyber jeden)</h2>
                    <span id="baseTotal" class="section-total">0 Kč</span>
                </div>
                <?php foreach ($bases as $key => $component): ?>
                    <label title="<?php echo $component->getPrice(); ?> Kč">
                        <input type="radio" name="base" value="<?php echo $key; ?>"
                            data-price="<?php echo $component->getPrice(); ?>"
                            <?php echo $key === 'tomato' ? 'checked' : ''; ?>>
                        <?php echo $component->getName(); ?>
                    </label>
                <?php endforeach; ?>
            </section>

            <section>
                <div class="section-header">
                    <h2>Přísady (libovolný počet)</h2>
                    <span id="toppingsTotal" class="section-total">0 Kč</span>
                </div>

                <?php
                $toppingGroups = [
                    'Sýry' => ['emmental', 'eidam', 'mozzarella', 'niva'],
                    'Maso' => ['ham', 'salami', 'bacon', 'chicken'],
                    'Zelenina a ovoce' => ['mushrooms', 'onion', 'peppers', 'corn', 'pineapple']
                ];
                ?>

                <?php foreach ($toppingGroups as $groupName => $groupKeys): ?>
                    <div class="topping-group">
                        <h3><?php echo $groupName; ?></h3>
                        <?php foreach ($groupKeys as $key):
                            if (!isset($toppings[$key])) continue;
                            $component = $toppings[$key];
                        ?>
                            <label title="<?php echo $component->getPrice(); ?> Kč">
                                <input type="checkbox" name="toppings[]" value="<?php echo $key; ?>"
                                    data-price="<?php echo $component->getPrice(); ?>">
                                <?php echo $component->getName(); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <div class="total-price-container">
                <button type="submit">Vložit do košíku</button>
                <span>Celková cena: <span id="totalPrice">0 Kč</span></span>
            </div>
        </form>
    </main>

    <script>
        function calculateTotal() {
            let total = 0;

            // Size
            let sizePrice = 0;
            const selectedSize = document.querySelector('input[name="size"]:checked');
            if (selectedSize) {
                sizePrice = parseInt(selectedSize.dataset.price || 0);
            }
            document.getElementById('sizeTotal').textContent = sizePrice + ' Kč';
            total += sizePrice;

            // Base
            let basePrice = 0;
            const selectedBase = document.querySelector('input[name="base"]:checked');
            if (selectedBase) {
                basePrice = parseInt(selectedBase.dataset.price || 0);
            }
            document.getElementById('baseTotal').textContent = basePrice + ' Kč';
            total += basePrice;

            // Toppings
            let toppingsPrice = 0;
            const selectedToppings = document.querySelectorAll('input[name="toppings[]"]:checked');
            selectedToppings.forEach(topping => {
                toppingsPrice += parseInt(topping.dataset.price || 0);
            });
            document.getElementById('toppingsTotal').textContent = toppingsPrice + ' Kč';
            total += toppingsPrice;

            document.getElementById('totalPrice').textContent = total + ' Kč';
        }

        // Add event listeners
        const form = document.getElementById('pizzaForm');
        form.addEventListener('change', calculateTotal);

        // Initial calculation
        calculateTotal();
    </script>
</body>

</html>