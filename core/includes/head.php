<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= $pageTitle ?? 'Marsla Restaurant' ?></title>
  <meta name="description" content="<?= $pageDescription ?? '' ?>">

  <link rel="shortcut icon" sizes="32x32" href="assets/favicon.svg" type="image/xml + svg">
  <link rel="shortcut icon" sizes="48x48" href="assets/favicon.png" type="image/png">
  <link rel="shortcut icon" sizes="180x180" href="assets/favicon.svg" type="image/xml + svg">

  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/loader.css">
  <script src="js/loader.js"></script>

  <?php
  if (!empty($pageCSS)) {
      if (is_array($pageCSS)) {
          foreach ($pageCSS as $cssFile) {
              echo '<link rel="stylesheet" href="css/' . $cssFile . '">';
          }
      } else {
          echo '<link rel="stylesheet" href="css/' . $pageCSS . '">';
      }
  }
  ?>
</head>
<body>
