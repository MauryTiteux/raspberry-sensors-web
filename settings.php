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

<table border="1px">
  <tr>
    <td>
      <a href="/sensors/">Logs</a>
    </td>
  </tr>
</table>

<form method="POST">
  <fieldset>
    <h2>Heures ouvertures/fermetures</h2>
    <label>
      <span>Début de journée en été</span>
      <input name="summer_opening_hour" type="number" value="<?= $settings['summer_opening_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['summer_opening_hour'] ?></span>
    </label>
    <label>
      <span>Tombée de la nuit en été</span>
      <input name="summer_closing_hour" type="number" value="<?= $settings['summer_closing_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['summer_closing_hour'] ?></span>
    </label>
    <label>
      <span>Début de journée en hiver</span>
      <input name="winter_opening_hour" type="number" value="<?= $settings['winter_opening_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['winter_opening_hour'] ?></span>
    </label>
    <label>
      <span>Tombée de la nuit en hiver</span>
      <input name="winter_closing_hour" type="number" value="<?= $settings['winter_closing_hour']  ?>" required min="0" max="24">
      <span class="error"><?= $errors['winter_closing_hour'] ?></span>
    </label>
  </fieldset>

  <fieldset>
    <h2>Température (C°)</h2>
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
    <h2>Humidité (en %)</h2>
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
    <div style="display: <?= $settings['custom_solar_blind_status'] ? 'block' : 'none' ?>" data-resume-at>
      <label>
        <span>Heure de reprise du mode automatique</span>
        <input type="datetime-local" name="resume_at" value="<?= $settings['resume_at'] ?>">
        <?= $settings['resume_at'] ? "<span data-resume-at-timer></span>" : '' ?>
        <span class="error"><?= $errors['resume_at'] ?></span>
      </label>
      <div style="display: flex; gap: 0.5rem;" data-presets>
        <button type="button" data-preset="2">2h</button>
        <button type="button" data-preset="10">10h</button>
        <button type="button" data-preset="24">24h</button>
        <!-- <button type="button" data-preset="168">Une semaine</button> -->
      </div>
      <?= $settings['resume_at'] ? '<button name="action" value="automatic">Supprimer le décompte</button>' : '' ?>
    </div>
  </fieldset>

  <div class="buttons">
    <button type="reset">Réinitialiser</button>
    <button name="action" value="save">Sauvegarder</button>
  </div>
<form>

<script>
  // Format date JS vers date input html
  const convertToDateTimeLocalString = (date) => {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const day = date.getDate().toString().padStart(2, "0");
    const hours = date.getHours().toString().padStart(2, "0");
    const minutes = date.getMinutes().toString().padStart(2, "0");

    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  const resumeAtBlock = document.querySelector('[data-resume-at]');
  const resumeAtField = document.querySelector('[name="resume_at"]');
  
  // Gestion des boutons 2h/10h/24h
  document.querySelector('[data-presets]').addEventListener('click', ({ target }) => {
    const hour = Number(target.dataset.preset);
    if (!hour) return; 
    
    const date = new Date();
    date.setHours(date.getHours() + hour);
    
    resumeAtField.value = convertToDateTimeLocalString(date);
  });
  
  // Quand les valeurs du formulaire changent, on regarde si le statut ON / OFF.
  // On affiche ou masque le champ date en fonction
  document.querySelector('form').addEventListener('change', () => {
    // Check si un des deux est cochés
    if (document.querySelector('input[name=custom_solar_blind_status]:checked')) {
      resumeAtBlock.style.display = 'block';
      return;
    }
    
    resumeAtBlock.style.display = 'none';
  });
  
  // Récupération de la span pour insérez le décompte
  const resumeAtTimer = document.querySelector('[data-resume-at-timer]');
  const resumeAtTimerDate = new Date(resumeAtField.value);

  // Décompte 1s
  const timerInterval = setInterval(() => {
    if (!resumeAtTimer) return;

    const now = new Date();
    const timeDifference = resumeAtTimerDate - now;

    // Quand le timer est terminé on coupe l'interval
    if (timeDifference <= 0) {
        clearInterval(timerInterval);
        return;
    }

    const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

    resumeAtTimer.textContent = `Temps restant : ${hours} heures, ${minutes} minutes, ${seconds} secondes.`
  }, 1000)
</script>