<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>(smt) simple mouse tracking v2 | install</title>
  
  <style type="text/css">
  fieldset { border:none; }
  body { font:100% Georgia,Times,serif; background-color:#888; }
  #global { width:80%; margin:0 auto; padding:1em; background-color:#FFF; border:1px solid #000; }
  h1 { margin-top:.3em; }
  .ok { color:green; }
  .ko { color:red; }
  </style>
</head>

<body>

<div id="global">

<h1>(smt) simple mouse tracking installation</h1>

<?php
require '../../config.php';
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