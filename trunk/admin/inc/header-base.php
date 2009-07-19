  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>(smt) simple mouse tracking v2 | admin interface</title>
  
  <link href="<?=ADMIN_PATH?>favicon.ico" rel="icon" type="image/x-icon" />
  <link href="<?=ADMIN_PATH?>favicon.ico" rel="shortcut icon" type="image/x-icon" />

  <link rel="stylesheet" type="text/css" href="<?=ADMIN_PATH?>css/base.css" />
  <link rel="stylesheet" type="text/css" href="<?=ADMIN_PATH?>css/admin.css" />
  <link rel="stylesheet" type="text/css" href="<?=ADMIN_PATH?>css/theme.css" />
  
  <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery-1.3.2.min.js"></script>
  
  <?php
  // check custom headers
  if (count($HEAD_ADDED) > 0) 
  {
    foreach ($HEAD_ADDED as $tag) 
    {
      echo $tag.PHP_EOL;
    }
  }
  ?>