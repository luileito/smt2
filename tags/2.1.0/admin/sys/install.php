<?php
session_start();
// this page does not include the login check routines
require '../../config.php';

include INC_DIR.'doctype.php';
?>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=CMS_TITLE?> | install</title>

  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>base.css" />
  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>theme.css" />
  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>install.css" />
  
  <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery-1.3.2.min.js"></script>
</head>

<body>

<div id="global">

<h1><strong>smt2</strong> installer</h1>

<?php
$cnx = db_connect();
// is already installed?
if (mysql_query("DESCRIBE ".TBL_PREFIX.TBL_RECORDS, $cnx))
{
?>

  <h3 class="ko">smt2 is already installed</h3>
  <?php
    // help the root user to unistall smt2 stuff
    $msg = (is_root()) ?
      'use <a href="uninstall.php">this script</a>'
      :
      'please delete all <em>'.TBL_PREFIX.'</em> tables from database';
    ?>
  <p>
    If you want to re-install it, <?=$msg?>.
  </p>
  
<?php
}
else 
{
  // before installing, ask user email (will be inserted on DB)
  if (isset($_POST['email'])) { $email = trim($_POST['email']); }
  // however, it can be changed later, so it won't be validated
  if ( !isset($_POST['submit']) || (isset($_POST['email']) && empty($email)) )
  {
    include 'install-check.php';
    ?>
    <p class="mt">Please write your email address.
       It will be used to send you a new password if you lose/forget it, so please double-check it before continuing.</p>

    <?php
      if ( isset($_POST['email']) && empty($email) ) {
        echo display_text($_displayType["ERROR"], 'Email address is empty.');
      }
    ?>
    
    <form action="install.php" method="post">
      <fieldset>
        <label for="email">Email</label>
        <input type="text" name="email" id="email" size="30" class="text" />
        <input type="submit" name="submit" value="Install" class="button round" />
      </fieldset>
    </form>
    
    <script type="text/javascript">
    //<![CDATA[
    $(function(){
      $('#email').focus();
    });
    //]]>
    </script>
    
    <?php
  }
  else
  {
  	include 'install-ready.php';
  }
}
?>
</div><!-- end global div -->

</body>

</html>