<?php
include INC_DIR.'doctype.php';
?>

<head>
  <?php include INC_DIR.'header-base.php'; ?>
</head>

<body>

  <div id="header" class="foothead">
      
    <h1><strong>(smt)<sup>2</sup></strong> &middot; simple mouse tracking</h1>
    
    <p id="logged"><a href="<?=ABS_PATH?>">Logged in</a> as <strong><?=$_SESSION['login']?></strong> &mdash;
    <a id="logout" class="smallround" href="<?=ADMIN_PATH?>sys/logout.php">disconnect</a></p>
    
  </div><!-- end header -->
    
  <div id="nav">
    <ul>
      <?php 
      $basedir = filename_to_str( ext_name() );
      $basecss = ($basedir == "admin") ? ' class="current"' : null;
      // display always the dashboard
      echo '<li'.$basecss.'><a href="'.ADMIN_PATH.'">Dashboard</a></li>';
      // display allowed sections
      echo ext_format();    
      ?>
    </ul>
  </div><!-- end nav -->
  
  <div id="global">
  
  <?php 
    // Custom admin content ("extension" from here onwards) should start here 
  ?>