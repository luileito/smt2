<?php
if (isset($_POST['submit'])) {
  // load (smt)2 system files
  require_once realpath(dirname(__FILE__).'/../').'/config.php';
  // process tracking data
  $_SESSION["smt-trackingServer"] = !empty($_POST['trackingServer']) ? $_POST['trackingServer'] : url_get_server();
  $_SESSION["smt-recTime"]        = $_POST['recTime'] > 0 ? $_POST['recTime'] : 3600;
  $_SESSION["smt-fps"]            = $_POST['fps'] > 0 ? $_POST['fps'] : 24;
  $_SESSION["smt-postInterval"]   = $_POST['postInterval'] > 0 ? $_POST['postInterval'] : 30;
  $_SESSION["smt-cookieDays"]     = $_POST['cookieDays'] > 0 ? $_POST['cookieDays'] : 365;
  $_SESSION["smt-layoutType"]     = !empty($_POST['layoutType']) ? $_POST['layoutType'] : "liquid";
  $_SESSION["smt-contRecording"]  = isset($_POST['contRecording']) ? "true" : "false";
  $_SESSION["smt-warn"]           = isset($_POST['warn']) ? "true" : "false";
  $_SESSION["smt-disabled"]       = isset($_POST['disabled']) ? "true" : "false";
  // set code
  require_once dirname(__FILE__) . '/conf/trackingcode.php';
}

require_once dirname(__FILE__) . '/conf/config.php';
require_once dirname(__FILE__) . '/lib/PHPProxy.class.php';
$url = isset($_REQUEST[URL_PARAM_NAME]) ? $_REQUEST[URL_PARAM_NAME] : null;

error_reporting(E_ALL ^ E_NOTICE);

if ($url == null):
?>

<head>
  <title>(smt) PHP Proxy</title>
  <link href="css/style.css" type="text/css" rel="stylesheet" />
</head>

<body>

<h1>Track External Websites</h1>

<form method="post" id="track" action="index.php">
  <fieldset class="noborder pad">
    <input type="hidden" name="action" value="new" />
    <label for="url">URL</label>
    <input id="url" name="<?php echo URL_PARAM_NAME ?>" size="50" />
    <input type="submit" class="button" name="submit" value="Go" />
  </fieldset>
  <fieldset class="nm">
    <legend>Tracking options</legend>
    <?php
    function form_field($type, $name, $value = "") 
    {
      $sn = "smt-".$name; // session name
      $field = '<label for="'.$name.'">'.$name.'</label>';
      if ($type == "str") {
        $v = isset($_SESSION[$sn]) ? $_SESSION[$sn] : $value;
        $field .= '<input id="'.$name.'" name="'.$name.'" value="'.$v.'" />';
      } else if ($type == "bol") {
        $s = (isset($_SESSION[$sn]) && $_SESSION[$sn] == "true") ? ' checked="checked"' : null;
        $field .= '<input type="checkbox" id="'.$name.'" name="'.$name.'" '.$s.' />';
      } else if ($type == "int") {
        $v = isset($_SESSION[$sn]) ? $_SESSION[$sn] : $value;
        $field .= '<input id="'.$name.'" name="'.$name.'" value="'.$v.'" size="4" />';
      }

      return $field;
    }
    ?>
    <?=form_field("str", "trackingServer", url_get_server())?>
    <?=form_field("int", "recTime", 3600)?>
    <?=form_field("int", "fps", 24)?>
    <?=form_field("int", "postInterval", 30)?>
    <?=form_field("int", "cookieDays", 365)?>
    <?=form_field("str", "layoutType", "liquid")?>
    <?=form_field("bol", "contRecording")?>
    <?=form_field("bol", "warn")?>
    <?=form_field("bol", "disabled")?>
  </fieldset>
</form>

</body>
</html>
<?php

else:
  // process request
	$decoded = base64_decode($url);
	if ($decoded === FALSE) {
		$url = base64_encode($url);
	}
	
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'new') {
		header('Location: ' . INDEX_FILE_NAME . '?' . URL_PARAM_NAME . '=' . base64_encode($url));
		exit;
	}
	
	if (isset($_GET['proxy_password'])) {
		$username = base64_decode($_GET['proxy_username']);
		$password = base64_decode($_GET['proxy_password']);
		$proxy = new PHPProxy($url, $username, $password);
	}
	else {
		$proxy = new PHPProxy($url);
	}
	
	$proxy->handleRequest();

endif;
?>
