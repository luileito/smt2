<?php
session_start();
// check data first
if (empty($_POST)) { exit; }

require '../../../config.php';


// check DB table
$form = $_POST['submit'];
// iterate over table options
$options = db_select_all(TBL_PREFIX.$form, "*", "1");
foreach ($options as $row => $arrValue) 
{
  $name = $arrValue['name'];
  if (isset($_POST[$name])) { $value = $_POST[$name]; }
   
  if ($arrValue['type'] == CMS_CHOICE) {
    // store int numbers instead of "on" string from checkboxes
    $value = isset($_POST[$name]) ? 1 : 0;
    // empty checkboxes will be set to zero
  }
  // update DB
  $success = db_update(TBL_PREFIX.$form, "value = '$value'", "id = '".$arrValue['id']."'");
}

// notify depending on each form name
notify_request($form, $success);
?>