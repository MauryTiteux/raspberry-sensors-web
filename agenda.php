<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sensors", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
    die($e);
}

date_default_timezone_set("Europe/Brussels");

$query = $pdo->query('SELECT * FROM events');
$events = $query->fetchall();

if (!empty($_POST['delete'])) {
    $query = $pdo->prepare('DELETE FROM events WHERE id = ?');
    $query->execute([$_POST['delete']]);
  
    $query = $pdo->query('SELECT * FROM events');
    $events = $query->fetchall();
} else if (!empty($_POST)) {
    $errors = [];

    $required_fields = ['start_at', 'end_at'];

    foreach ($_POST as $key => $value) {
        if (in_array($key, $required_fields) && empty($_POST[$key])) {
            $errors[$key] = 'Ce champ est requis';
        }
    }

    if (strtotime($_POST['end_at']) < strtotime($_POST['start_at'])) {
        $errors['end_at'] = 'Doit se passer après la date de début';
    }

    if (strtotime($_POST['end_at']) < time()) {
        $errors['end_at'] = 'Doit se passer dans le futur';
    }
    
    if (strtotime($_POST['start_at']) < time()) {
        $errors['start_at'] = 'Doit se passer dans le futur';
    }

    if (empty($errors)) {
        $query = $pdo->prepare('INSERT INTO events(start_at, end_at) VALUES (?, ?)');
        $query->execute([$_POST['start_at'], $_POST['end_at']]);
        $_POST['start_at'] = null;
        $_POST['end_at'] = null;

        $query = $pdo->query('SELECT * FROM events');
        $events = $query->fetchall();
    }
}
?>

<?php include './head.php' ?>

    <table border="1">
        <tr>
          <?php include './menu.php' ?>
        </tr>
    </table>

    <form method="POST">
        <fieldset>
            <h2>Ajouter un nouvel évenement</h2>
            <label>
                <span>Date de début</span>
                <input type="datetime-local" name="start_at" value="<?= $_POST['start_at'] ?>">
                <span class="error"><?= $errors['start_at'] ?></span>
            </label>
            <label>
                <span>Date de fin</span>
                <input type="datetime-local" name="end_at" value="<?= $_POST['end_at'] ?>">
                <span class="error"><?= $errors['end_at'] ?></span>
            </label>
        </fieldset>
        <button>Sauvegarder</button>
    </form>

    <table border="1px">
      <thead>
        <tr>
          <th>Date début</th>
          <th>Date de fin</th>
          <th></th>
      </tr>
      </thead>
      <?php foreach($events as $event): ?>
        <tr>
          <td><?= date_format(date_create($event['start_at']),"d/m/Y") ?></td>
          <td><?= date_format(date_create($event['end_at']),"d/m/Y") ?></td>
          <td>
            <form method="POST">
                <button class="error-button" type="submit" name="delete" value="<?= $event['id'] ?>">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach ?>
    </table>
</body>