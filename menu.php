<?php

$links = [
    [
        'label' => 'Paramètres',
        'url' => '/sensors/settings.php',
    ],
    [
        'label' => 'Agenda',
        'url' => '/sensors/agenda.php',
    ],
    [
        'label' => 'Récurrence',
        'url' => '/sensors/recurrent.php',
    ],
    [
        'label' => 'Logs',
        'url' => '/sensors/',
    ]
]; 

?>

<?php foreach ($links as $link): ?>
    <td class="td" <?= parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) == $link['url'] ? "aria-current='page'" : '' ?>>
        <a href="<?= $link['url'] ?>"> <?= $link['label'] ?></a>
    </td>
<?php endforeach ?>

