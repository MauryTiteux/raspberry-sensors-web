<?php 

try {
  $pdo = new PDO("mysql:host=localhost;dbname=sensors", "root", "root");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  die($e);
}

$labels = ['Lundi', 'Mardi', 'Mercredi','Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

$query = $pdo->query('SELECT * FROM days');
$days = $query->fetchall();

foreach ($days as $index => $day) {
  $days[$index]['label'] = $labels[$index];
}

if (!empty($_POST['reset'])) {
  $query = $pdo->prepare('UPDATE days SET start_at_hour = NULL, is_recurrent = 0 WHERE id = ?');
  $query->execute([ $_POST['reset']]);

  $query = $pdo->query('SELECT * FROM days');
  $days = $query->fetchall();

  foreach ($days as $index => $day) {
    $days[$index]['label'] = $labels[$index];
  }  
} elseif (!empty($_POST['days'])) {
  $errors = [];

  foreach ($_POST['days'] as $key => $value) {
    $days[$key]['start_at_hour'] = htmlspecialchars($value['start_at_hour']);
    $days[$key]['is_recurrent'] = htmlspecialchars($value['is_recurrent']);

    if ($days[$key]['start_at_hour'] && ($days[$key]['start_at_hour'] < 0 || $days[$key]['start_at_hour'] > 24)) {
      $errors[$key]['start_at_hour'] = 'La valeur doit être entre 0 et 24';
    }

    if ($days[$key]['start_at_hour'] && !$days[$key]['is_recurrent']) {
      $errors[$key]['start_at_hour'] = 'La case doit être cochée';
    }

    if (!$days[$key]['start_at_hour'] && $days[$key]['is_recurrent']) {
      $errors[$key]['start_at_hour'] = 'La valeur doit être définie';
    }
  }

  if (empty($errors)) {
    $values = [];

    foreach ($days as $day) {
      $values = [...$values, $day['id'], $day['day'], $day['start_at_hour'] ? (int)$day['start_at_hour'] : null, $day['is_recurrent'] == 'on' ? 1 : 0];
    }

    $query = $pdo->prepare('INSERT INTO days (id, day, start_at_hour, is_recurrent) 
      VALUES (?, ?, ?, ?), (?, ?, ?, ?), (?, ?, ?, ?), (?, ?, ?, ?), (?, ?, ?, ?), (?, ?, ?, ?), (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE 
      id = VALUES(id),
      day = VALUES(day),
      start_at_hour = VALUES(start_at_hour),
      is_recurrent = VALUES(is_recurrent)
    ');

    $query->execute($values);
  };
}
?>

<?php include './head.php' ?>

<table border="1">
    <tr>
      <?php include './menu.php' ?>
    </tr>
</table>

  <form method="POST">
    <table border="1px">
      <thead>
        <tr>
          <th>Jour</th>
          <th>Est récurrent (oui/non)</th>
          <th>Heure d'ouverture</th>
      </tr>
      </thead>
      <?php foreach($days as $index => $day): ?>
        <tr>
          <td>
            <label>
              <span><?= $day['label'] ?></span>
              <input hidden name="days[<?= $index ?>][id]" value="<?= $index ?>">
            </label>
          </td>
          <td>
            <input name="days[<?= $index ?>][is_recurrent]" type="checkbox" <?= $day['is_recurrent'] ? 'checked' : '' ?>>
          </td>
          <td>
            <input name="days[<?= $index ?>][start_at_hour]" type="number" value="<?= $day['start_at_hour'] ?>">
            <span class="error"><?= $errors[$index]['start_at_hour'] ?></span>
          </td>
          <td>
            <button class="error-button" type="submit" name="reset" value="<?= $day['id'] ?>">Supprimer</button>
          </td>
        </tr>
      <?php endforeach ?>
    </table>
    <button>Sauvegarder</button>
  </form>
</body>