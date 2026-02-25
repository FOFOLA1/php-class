<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$errors = [];
$formData = ['name' => '', 'addressStreet' => '', 'addressCity' => '', 'addressPostalCode' => '', 'email' => ''];

require_once 'PizzaData.php';

function calculatePizzaPrice(array $item, array $sizes, array $bases, array $toppings): int
{
    $price = 0;
    $price += getComponent($item['size'], $sizes)->getPrice();
    $price += getComponent($item['base'], $bases)->getPrice();
    if (!empty($item['toppings'])) {
        foreach ($item['toppings'] as $topping) {
            $price += getComponent($topping, $toppings)->getPrice();
        }
    }
    return $price;
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Add to cart
            $size = $_POST['size'] ?? '30cm';
            $base = $_POST['base'] ?? 'tomato';
            $toppings = $_POST['toppings'] ?? [];

            $item = [
                'size' => htmlspecialchars($size),
                'base' => htmlspecialchars($base),
                'toppings' => array_map('htmlspecialchars', $toppings)
            ];

            $_SESSION['cart'][] = $item;
            header('Location: cart.php'); // Redirect to avoid form resubmission
            exit;
        } elseif ($_POST['action'] === 'checkout') {
            // Checkout validation
            $formData['name'] = trim($_POST['name'] ?? '');
            $formData['addressStreet'] = trim($_POST['addressStreet'] ?? '');
            $formData['addressCity'] = trim($_POST['addressCity'] ?? '');
            $formData['addressPostalCode'] = trim($_POST['addressPostalCode'] ?? '');
            $formData['email'] = trim($_POST['email'] ?? '');

            if (empty($formData['name']) || !preg_match('/^[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+(( )+[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+)*$/', $formData['name'])) {
                $errors[] = 'Jméno je povinné.';
            }
            if (empty($formData['addressStreet']) || !preg_match('/^[\p{L}0-9\.\-\s]+\s\d+(?:\/\d+)?[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]?$/u', $formData['addressStreet'])) {
                $errors[] = 'Ulice je povinná.';
            }
            if (empty($formData['addressCity']) || !preg_match('/^[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+(( )+[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+)*$/', $formData['addressCity'])) {
                $errors[] = 'Město je povinné.';
            }
            if (empty($formData['addressPostalCode']) || !preg_match('/^\d{3}\s?\d{2}$/', $formData['addressPostalCode'])) {
                $errors[] = 'PSČ je povinné.';
            }
            if (empty($formData['email']) || !preg_match('/^[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $formData['email'])) {
                $errors[] = 'Zadejte platný email.';
            }
            if (empty($_SESSION['cart'])) {
                $errors[] = 'Košík je prázdný.';
            }

            if (empty($errors)) {
                // Success
                unset($_SESSION['cart']);
                header('Location: index.php?success=1');
                exit;
            }
        }
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'remove' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex
        }
        header('Location: cart.php'); // Redirect to update site
        exit;
    } elseif ($_GET['action'] === 'clear') {
        unset($_SESSION['cart']);
        header('Location: cart.php'); // Redirect to update site
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Košík</title>
    <link rel="stylesheet" href="output.css">
</head>

<body>
    <header>
        <h1>Váš košík</h1>
        <div class="cart-link">
            <p><a href="index.php">Zpět na výběr pizzy -></a></p>
        </div>
    </header>

    <main>
        <section>
            <?php if (empty($_SESSION['cart'])): ?>
                <p class="empty-cart">Košík je prázdný.</p>
            <?php else: ?>
                <?php
                $totalPrice = 0;
                foreach ($_SESSION['cart'] as $index => $item):
                    $itemPrice = calculatePizzaPrice($item, $sizes, $bases, $toppings);
                    $totalPrice += $itemPrice;
                    $sizeComp = getComponent($item['size'], $sizes);
                    $baseComp = getComponent($item['base'], $bases);
                ?>
                    <div class="cart-item">
                        <div>
                            <strong>Pizza <?php echo $sizeComp->getName(); ?></strong> (<?php echo $itemPrice; ?> Kč)<br>
                            Základ: <?php echo $baseComp->getName(); ?><br>
                            <?php if (!empty($item['toppings'])): ?>
                                Přísady: <?php echo implode(', ', array_map(function ($t) use ($toppings) {
                                                return getComponent($t, $toppings)->getName();
                                            }, $item['toppings'])); ?>
                            <?php else: ?>
                                Bez přísad
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="cart.php?action=remove&id=<?php echo $index; ?>" class="btn-danger">Odebrat</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; margin-bottom: 40px;">
                    <a href="cart.php?action=clear" class="btn-danger" style="padding: 10px 20px;">Vyprázdnit košík</a>
                    <div style="font-size: 1.2em;">
                        <strong>Celková cena: <?php echo $totalPrice; ?> Kč</strong>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <section style="margin-top: 40px; border-top: 2px solid #eee; padding-top: 20px;">
            <h1>Dokončení objednávky</h1>

            <?php if (!empty($errors)): ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="cart.php" method="post">
                <input type="hidden" name="action" value="checkout">

                <div class="form-group">
                    <label for="name">Jméno a příjmení:</label>
                    <input type="text" id="name" name="name" pattern="^[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+(( )+[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+)*$" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="addressStreet">Doručovací adresa:</label>
                    <input type="text" id="addressStreet" name="addressStreet" pattern="^[\p{L}0-9\.\-\s]+\s\d+(?:\/\d+)?[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]?$" value="<?php echo htmlspecialchars($formData['addressStreet']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="addressCity">Město:</label>
                    <input type="text" id="addressCity" name="addressCity" pattern="^[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+(( )+[a-zěščřžýáíéóňťA-ZĚŠČŘŽÝÁÍÉÓŇŤ]+)*$" value="<?php echo htmlspecialchars($formData['addressCity']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="addressPostalCode">PSČ:</label>
                    <input type="text" id="addressPostalCode" name="addressPostalCode" pattern="^\d{3}\s?\d{2}$" value="<?php echo htmlspecialchars($formData['addressPostalCode']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                </div>

                <button type="submit" class="btn-primary" <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>Odeslat objednávku</button>
            </form>
        </section>
    </main>
</body>

</html>