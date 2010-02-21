<?php 
require '../../config.php';
include INC_PATH.'inc/doctype.php';
?>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>(smt) simple mouse tracking v2 | install</title>
  <link rel="stylesheet" type="text/css" href="<?=ADMIN_PATH?>css/install.css" />
</head>

<body>

<div id="global">

<h1>(smt) simple mouse tracking installation</h1>

<?php
$cnx = db_connect();
// is already installed?
if (mysql_query("DESC ".TBL_PREFIX.TBL_RECORDS, $cnx)) 
{
  echo '(smt) seems to be already installed. If you want to reinstall it, please delete all <em>'.TBL_PREFIX.'</em> tables from database.';
} 
else 
{
  // before installing, ask user email (will be inserted on DB)
  $EMAIL = isset($_POST['email']) ? trim($_POST['email']) : "";
  // however, it can be changed later, so it's up to the user entering a valid email address...
  if (!isset($_POST['submit']) || empty($EMAIL)) 
  {
    include 'install-check.php';
    ?>
    <p>Please write your email address. It will be used to send you a new password if you lose/forget it, so please double-check it before continuing.</p>
    <form action="install.php" method="post">
      <fieldset>
        <label for="email">Email</label>
        <input type="text" name="email" id="email" size="30" />
        <input type="submit" name="submit" value="Install" />
      </fieldset>
    </form>
    <p>You can always change it later, but prevention is better than cure.</p>
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
