<?php

$dsn = 'mysql:host=db;dbname=airports;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$pdo = new PDO($dsn, $user, $pass);

echo "<h1>Airports Data</h1>";



echo "<h2>1. Kolik je letišť v Německu?</h2>";
$sql1 = "
    SELECT COUNT(*) as count
    FROM a_airports a
    JOIN a_countries c ON a.country = c.id
    WHERE c.name = 'Germany'
";
$query = $pdo->query($sql1);
$result = $query->fetch();
echo "<p>" . $result['count'] . "</p>";




echo "<h2>2. Kolik letišť v provozu má každý stát v Africe?</h2>";
$sql2 = "
    SELECT c.name, COUNT(a.ident) as airport_count
    FROM a_airports a
    JOIN a_countries c ON a.country = c.id
    JOIN a_continents cont ON c.continent = cont.id
    WHERE cont.name = 'Africa' AND a.service = 1
    GROUP BY c.name
    ORDER BY airport_count DESC
";
$query = $pdo->query($sql2);
echo "<ul>";
while ($row = $query->fetch()) {
    echo "<li>" . htmlspecialchars($row['name']) . ": " . $row['airport_count'] . "</li>";
}
echo "</ul>";



echo "<h2>3. Do kterých destinací se dá dostat z Prahy?</h2>";
$sql3 = "
    SELECT DISTINCT dest_airport.name, dest_airport.iatacode
    FROM a_lines l
    JOIN a_airports dest_airport ON l.dest = dest_airport.iatacode OR l.orig = dest_airport.iatacode
    WHERE (l.orig = 'PRG' OR l.dest = 'PRG') AND dest_airport.iatacode != 'PRG'
    ORDER BY dest_airport.name ASC
";
$query = $pdo->query($sql3);
echo "<ul>";
while ($row = $query->fetch()) {
    echo "<li>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['iatacode']) . ")</li>";
}
echo "</ul>";


?>
