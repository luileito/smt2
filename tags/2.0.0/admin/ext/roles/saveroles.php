<?php
session_start();
// check data first
if (empty($_POST)) { exit; }

define('REL_URL', "../../../");
require REL_URL.'config.php';

$form = $_POST['submit'];

// ------------------------------------------------------------ manage roles ---
if ($form == "manage") 
{
  $action = $_POST['action'];
  // IE cannot handle the attributes name="action" value="delete" on input image buttons :o
  if (isset($_POST['update_x'])) { $action = "update"; }
  if (isset($_POST['create_x'])) { $action = "create"; }
  if (isset($_POST['delete_x'])) { $action = "delete"; }
  
  $exts = $_POST['exts'];         // form array
  $role_id = (int) $_POST['id'];  // role id...
  $role_name = $_POST['name'];    // ...and name
  
  if (!isset($exts)) {
    // nothing to change...
    notify_request($form, false, "You should check at least one section for that role.");
  }
  
  // check if role name exists
  $exists = db_select(TBL_PREFIX.TBL_ROLES, "id", "name='".$role_name."'");
  if ($exists) {
    notify_request($form, false, "The role <em>".$role_name."</em> already exists.");
  }
  
  switch ($action) 
  {
    case 'create':
      // insert new role with allowed sections
      $values = "'".$role_name."', '".implode(",", $exts)."'";
      $success = db_insert(TBL_PREFIX.TBL_ROLES, "name,ext_allowed", $values);
      break;
      
    case 'update':
      $success = db_update(TBL_PREFIX.TBL_ROLES, "ext_allowed='".implode(",", $exts)."'", "id='".$role_id."'");
      break;
      
    case 'delete':
      $success = db_delete(TBL_PREFIX.TBL_ROLES, "id='".$role_id."'");
      break;
      
    default:
      break;
  }
} 
// ---------------------------------------------------------- describe roles ---
else if ($form == "describe") 
{
  $description  = $_POST['description'];  // form array
  $check = $_POST['check']; // form array
  $name  = $_POST['name'];  // form array
  
  if (!isset($check)) {
    // nothing to change...
    notify_request($form, false, "You should check at least one role to describe.");
  }
  
  foreach ($check as $id) {
    $success = db_update(TBL_PREFIX.TBL_ROLES, "name='".$name[$id]."', description='".$description[$id]."'", "id='".$id."'");
  }
}

// ---------------------------------------------------------------- redirect ---
notify_request($form, $success);
?>