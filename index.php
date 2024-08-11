<?php

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sensors", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die($e);
}

// Récupération du nombre max de logs 
$limit = $_GET['limit'] ?? 50;
// Afficher ou non uniquement les erreurs
$only_errors = $_GET['only_errors'] ? $_GET['only_errors'] === '0' ? false :  true : false;

if ($only_errors) {
    // Filtre sur les logs qui ont metadata de définie
    $query = $pdo->prepare('SELECT * FROM (SELECT * FROM logs WHERE metadata IS NOT NULL ORDER BY id DESC LIMIT ?) subquery ORDER BY created_at ASC');
} else {
    $query = $pdo->prepare('SELECT * FROM (SELECT * FROM logs ORDER BY id DESC LIMIT ?) subquery ORDER BY id ASC');
}

$query->bindParam(1, $limit, PDO::PARAM_INT);
$query->execute();
$logs = $query->fetchAll();
?>

<?php include './head.php' ?>
    
    <table>
        <tr class="tr">
            <?php include './menu.php' ?>
            <td class="td" width="240px">
                <a href="/sensors?<?= http_build_query(['limit' => $limit, 'only_errors' => $only_errors ? '0' : '1']) ?>">
                    <?= $only_errors ? 'Afficher tout' : 'Afficher seulement les erreurs' ?>
                </a>
            </td>
            <td>
                <a href=<?= $_SERVER['REQUEST_URI'] ?>>Rafraichir</a>
            </td>
        </tr>
    </div>
    
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
    
    <table border="1">
        <tr>
            <td>
                <a href="/sensors?<?= http_build_query(['limit' => $limit + 50, 'only_errors' => $only_errors ? '1' : '0']) ?>">Charger plus</a>
            </td>
        </tr>
    </table>

</body>    

