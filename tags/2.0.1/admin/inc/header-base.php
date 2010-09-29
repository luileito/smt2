  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=CMS_TITLE?> | admin interface</title>
  
  <link href="<?=ADMIN_PATH?>favicon.ico" rel="icon" type="image/x-icon" />
  <link href="<?=ADMIN_PATH?>favicon.ico" rel="shortcut icon" type="image/x-icon" />

  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>base.css" />
  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>admin.css" />
  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>theme.css" />

  <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery-1.3.2.min.js"></script>
  <script type="text/javascript" src="<?=ADMIN_PATH?>js/flashdetect.min.js"></script>
  <script type="text/javascript" src="<?=ADMIN_PATH?>js/setupcms.js"></script>
  <script type="text/javascript" src="<?=SMT_AUX?>"></script>
  
  <?php
  // check custom headers
  if (count($_headAdded) > 0)
  {
    foreach ($_headAdded as $tag)
    {
      echo $tag.PHP_EOL;
    }
  }
  ?>