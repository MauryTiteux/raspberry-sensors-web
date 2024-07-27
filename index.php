<?php

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sensors", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die($e);
}

$query = $pdo->query('SELECT * FROM logs');
$logs = $query->fetchAll();

?>

<style>
    td, th {
        padding: 8 32;
    }
</style>

<a href="/sensors/settings.php">Paramètres</a>

<table border="1" style="text-align:center;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Température</th>
            <th>Humidité</th>
            <th>LUX</th>
            <th>Statut du store</th>
            <th>Statut du script</th>
            <th>Message de l'écran LCD</th>
            <th>Message</th>
            <th>Date de création</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach(array_reverse($logs) as $log): ?>
            <tr>
                <td><?= $log['id'] ?></td>
                <td><?= $log['temperature'] ?></td>
                <td><?= $log['humidity'] ?></td>
                <td><?= $log['lux'] ?></td>
                <td><?= $log['solar_blind_status'] ? $log['solar_blind_status'] : '-' ?></td>
                <td><?= $log['script_status'] ?></td>
                <td><?= $log['message'] ?></td>
                <td><?= $log['metadata'] ? $log['metadata'] : '-' ?></td>
                <td><?= $log['created_at'] ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
<table>

<script>
    setInterval(() => location.reload(), 1000)
</script>