<?php

require_once ('./Clanky.php');
define("POCET_CLANKU", 28);

$clanky = Clanky::getClanky(POCET_CLANKU);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Články</h1>
    <ul>
        <?php foreach ($clanky as $clanek): ?>
            <li><?php echo htmlspecialchars($clanek['title']); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>