<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';
// now you have access to all CMS API

include INC_DIR.'header.php';
?>

<h1>Classify pages by user behavior</h1>

<?php
if (!isset($_POST['domain_id'])) {
  include 'form.php';
} else {
  include 'cluster.php';
}
?>

<?php include INC_DIR.'footer.php'; ?>
