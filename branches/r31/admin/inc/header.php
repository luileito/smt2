<?php
include INC_PATH.'inc/doctype.php'; 
?>

<head>
  <?php include INC_PATH.'inc/header-base.php'; ?>
</head>

<body>

  <div id="header" class="foothead">
      
    <h1>(smt) simple mouse tracking</h1>
    
    <p id="logged">Logged in as <strong><?=$_SESSION['login']?></strong> &mdash; 
    <a id="logout" href="<?=ADMIN_PATH?>sys/logout.php">disconnect</a></p>
    
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