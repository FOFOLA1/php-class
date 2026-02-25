<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení</title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="page-centered">
    <div class="auth-card">
        <h1 class="page-title-centered">Přihlášení</h1>
        <?php if (isset($error) && $error): ?>
            <p class="alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="index.php?action=login" class="form-group">
            <div>
                <label class="form-label">Uživatelské jméno:</label>
                <input type="text" name="username" required
                    class="form-input">
            </div>
            <div>
                <label class="form-label">Heslo:</label>
                <input type="password" name="password" required
                    class="form-input">
            </div>
            <button type="submit"
                class="btn btn-primary btn-full">
                Přihlásit se
            </button>
        </form>
        <p class="footer-text">Výchozí admin: admin / admin (musí být vytvořen manuálně)</p>
        <p class="footer-link">Nemáš účet? <a href="index.php?action=register" class="link">Registruj se zde</a></p>
    </div>
</body>

</html>