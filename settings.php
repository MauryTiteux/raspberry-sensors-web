<?php

try {
  $pdo = new PDO("mysql:host=localhost;dbname=sensors", "root", "root");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  die($e);
}

date_default_timezone_set("Europe/Brussels");

$query = $pdo->query('SELECT * FROM settings');
$settings = $query->fetch();

if (!empty($_POST)) {
  if ($_POST['action'] == 'automatic') {
    $query = $pdo->prepare('UPDATE settings SET custom_solar_blind_status = NULL, resume_at = NULL WHERE id = 1');
    $query->execute();
    $settings['custom_solar_blind_status'] = null;
    $settings['resume_at'] = null;
  } else {
      $required_fields = [
        'summer_opening_hour',
        'summer_closing_hour',
        'winter_opening_hour',
        'winter_closing_hour',
        'temperature_min',
        'temperature_max',
        'humidity_min',
        'humidity_max',
        'lux'
      ];
    
      $days_fields = [
        'summer_opening_hour',
        'summer_closing_hour',
        'winter_opening_hour',
        'winter_closing_hour'
      ];
    
      $temperature_fields = ['temperature_min', 'temperature_max'];
      $humidity_fields = ['humidity_min', 'humidity_max'];
      
      $errors = [];
    
      foreach ($_POST as $key => $value) {
        $settings[$key] = htmlspecialchars($value);
        
        if (in_array($key, $required_fields) && empty($settings[$key])) {
          $errors[$key] = 'Ce champ est requis';
        }
    
        if (in_array($key, $days_fields) && ($settings[$key] < 0 || $settings[$key] > 24)) {
          $errors[$key] = 'La valeur doit être entre 0 et 24';
        }
    
        if (in_array($key, $temperature_fields) && ($settings[$key] < -50 || $settings[$key] > 50)) {
          $errors[$key] = 'La valeur doit être entre -50 et 50';
        }
    
        if (in_array($key, $humidity_fields) && ($settings[$key] < 0 ||$settings[$key] > 100)) {
          $errors[$key] = 'La valeur doit être entre 0 et 100';
        }
      }
    
      if ($settings['temperature_min'] > $settings['temperature_max']) {
        $errors['temperature_min'] = 'Doit être inférieure à la température max';
        $errors['temperature_max'] = 'Doit être supérieure à la température min';
      }
    
      if ($settings['temperature_min'] > $settings['temperature_max']) {
        $errors['temperature_min'] = 'Doit être inférieure à la température max';
        $errors['temperature_max'] = 'Doit être supérieure à la température min';
      }
    
      if ($settings['lux'] < 0 || $settings['lux'] > 130000) {
        $errors['lux'] = 'La valeur doit être entre 0 et 130000';
      }
    
      if (!empty($settings['resume_at']) && empty($settings['custom_solar_blind_status'])) {
        $errors['custom_solar_blind_status'] = 'La valeur doit être définie';
      }
    
      if (!empty($settings['custom_solar_blind_status']) && empty($settings['resume_at'])) {
        $errors['resume_at'] = 'La valeur doit être définie';
      }
    
      if (!empty($settings['custom_solar_blind_status']) && !empty($settings['resume_at'])) {
        if (strtotime($settings['resume_at']) < time()) {
          $errors['resume_at'] = 'Doit se passer dans le futur';
        }
      }
    
      if (empty($errors)) {
        $query = $pdo->prepare('UPDATE settings SET summer_opening_hour = ?, summer_closing_hour = ?, winter_opening_hour = ?, winter_closing_hour = ?, temperature_min = ?, temperature_max = ?, humidity_min = ?, humidity_max = ?, lux = ?, custom_solar_blind_status = ?, resume_at = ? WHERE id = 1');
        $query->execute([$settings['summer_opening_hour'], $settings['summer_closing_hour'], $settings['winter_opening_hour'], $settings['winter_closing_hour'], $settings['temperature_min'], $settings['temperature_max'], $settings['humidity_min'], $settings['humidity_max'], $settings['lux'], empty($settings['custom_solar_blind_status']) ? NULL : $settings['custom_solar_blind_status'], empty($settings['resume_at']) ? NULL : $settings['resume_at']]);
      };
  }
}

$timestamp_date_cible = strtotime($settings['resume_at']);
$timestamp_actuel = time();

$temps_restant = $timestamp_date_cible - $timestamp_actuel;

$heures = floor($temps_restant / 3600);
$minutes = floor(($temps_restant % 3600) / 60);
$secondes = $temps_restant % 60;
?>

<style>
  body {
    font-family: sans-serif;
    max-width: 800px;
    margin: 2rem auto;
  }

  form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  fieldset {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    padding: 2rem;
    gap: 1rem;
  }

  h2 {
    grid-column: span 2;
    line-height: 1;
    margin: 0 0 1rem 0;
  }

  label {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    line-height: 1;
  }

  .radio {
    display: grid;
    grid-template-colums: repeat(2, 1fr);
  }

  .radio > span {
    grid-column: span 2;
  }

  .radio label {
    flex-direction: row;
    align-items: center;
  }

  .error {
    color: red;
    font-size: 12px;
  }
  
  .buttons {
    margin-left: auto;
    display: flex;
    gap: 1rem;
  }
</style>

<a href="/sensors/">Logs</a>

<form method="POST">
  <fieldset>
    <h2>Heures ouvertures/fermetures</h2>
    <label>
      <span>Ouverture en été</span>
      <input name="summer_opening_hour" type="number" value="<?= $settings['summer_opening_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['summer_opening_hour'] ?></span>
    </label>
    <label>
      <span>Fermeture en été</span>
      <input name="summer_closing_hour" type="number" value="<?= $settings['summer_closing_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['summer_closing_hour'] ?></span>
    </label>
    <label>
      <span>Ouverture en hiver</span>
      <input name="winter_opening_hour" type="number" value="<?= $settings['winter_opening_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['winter_opening_hour'] ?></span>
    </label>
    <label>
      <span>Fermeture en hiver</span>
      <input name="winter_closing_hour" type="number" value="<?= $settings['winter_closing_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['winter_closing_hour'] ?></span>
    </label>
  </fieldset>

  <fieldset>
    <h2>Température</h2>
    <label>
      <span>Minimale</span>
      <input name="temperature_min" type="number" value="<?= $settings['temperature_min']  ?>" required min="-50" max="50">
      <span class="error"><?= $errors['temperature_min'] ?></span>
    </label>
    <label>
      <span>Maximale</span>
      <input name="temperature_max" type="number" value="<?= $settings['temperature_max']  ?>" required min="-50" max="50">
      <span class="error"><?= $errors['temperature_max'] ?></span>
    </label>
  </fieldset>

  <fieldset>
    <h2>Humidité</h2>
    <label>
      <span>Minimale</span>
      <input name="humidity_min" type="number" value="<?= $settings['humidity_min'] ?>" required min="0" max="100">
      <span class="error"><?= $errors['humidity_min'] ?></span>
    </label>
    <label>
      <span>Maximale</span>
      <input name="humidity_max" type="number" value="<?= $settings['humidity_max'] ?>" required min="0" max="100">
      <span class="error"><?= $errors['humidity_max'] ?></span>
    </label>
  </fieldset>

  <fieldset>
    <h2>Luminosité</h2>
    <label>
      <span>Lux pour fermeture</span>
      <input name="lux" type="number" value="<?= $settings['lux'] ?>" required min="0" max="130000">
      <span class="error"><?= $errors['lux'] ?></span>
    </label>
  </fieldset>

  <fieldset>
    <h2>Gestion manuelle</h2>
    <div class="radio">
      <span>Statut du store</span>
      <label>
        <span>Ouvert</span>
        <input name="custom_solar_blind_status" type="radio" value="on" <?= $settings['custom_solar_blind_status'] == 'on' ? 'checked' : '' ?>>
      </label>
      <label>
        <span>Fermé</span>
        <input name="custom_solar_blind_status" type="radio" value="off" <?= $settings['custom_solar_blind_status'] == 'off' ? 'checked' : '' ?>>
      </label>
      <span class="error"><?= $errors['custom_solar_blind_status'] ?></span>
    </div>
    <div>
      <label>
        <span>Heure de reprise du mode automatique</span>
        <input type="datetime-local" name="resume_at" value="<?= $settings['resume_at'] ?>">
        <?= $settings['resume_at'] ? "Temps restant : $heures heures, $minutes minutes, $secondes secondes." : '' ?>
        <span class="error"><?= $errors['resume_at'] ?></span>
      </label>
      <?= $settings['resume_at'] ? '<button name="action" value="automatic">Supprimer le décompte</button>' : '' ?>
    </div>
  </fieldset>

  <div class="buttons">
    <button type="reset">Réinitialiser</button>
    <button name="action" value="save">Sauvegarder</button>
  </div>
<form>
