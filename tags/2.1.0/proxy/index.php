<?php
require_once(dirname(__FILE__) . '/conf/config.php');
require_once(dirname(__FILE__) . '/lib/PHPProxy.class.php');

$url = $_REQUEST[URL_PARAM_NAME];
if ($url == ''):

include INC_PATH.'inc/doctype.php';
?>

<head>
	<title>(smt) PHP Proxy</title>
	
	<link href="css/style.css" type="text/css" rel="stylesheet" />
	
</head>

<body>

<h1>Track External Websites</h1>

<form method="post" id="track" action="index.php">
  <fieldset class="noborder">
    <input type="hidden" name="action" value="new" />
    <label for="url">URL</label>
    <input id="url" name="<?php echo URL_PARAM_NAME ?>" size="50" />
    <input type="submit" class="button" value="Go" />
  </fieldset>
</form>

</body>
</html>
<?php
else:
	$decoded = base64_decode($url);
	if ($decoded === FALSE) {
		$url = base64_encode($url);
	}
	
	if ($_REQUEST['action'] == 'new') {
		header('Location: ' . INDEX_FILE_NAME . '?' . URL_PARAM_NAME . '=' . base64_encode($url));
		die();
	}
	
	if ($_GET['proxy_password']) {
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
