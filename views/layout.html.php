<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $site_name, ' // ', strip_tags($page_title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/css/styles.css">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
    <?php
    while ($style = stack('styles')) {
      ?>
      <link href="/css/<?php echo $style; ?>.css" rel="stylesheet">
      <?php        
    }
    ?>
    <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/scripts.js"></script>
    <?php
    while ($script = stack('scripts')) {
      ?>
      <script type="text/javascript" src="/js/<?php echo $script; ?>.js"></script>
      <?php        
    }
    ?>
  </head>
  <body>
    <?php 
    $msg = flash('error');
    if ($msg) {
      $class = 'danger';
    }
    else {
      $msg = flash('success');
      $class = 'success';
    }
    
    if ($msg) {
      echo partial('alert', array('class' => $class, 'msg' => $msg));
    }

    echo partial('navbar', array(
      'site_name' => $site_name,
      'page_title' => $page_title
    ));
    ?>

    <div class="container">
      <?php echo content(); ?>
    </div>
    
    <?php 
    while ($footer = stack('footer')) {
      echo $footer;
    }
    ?>
  </body>
</html>
