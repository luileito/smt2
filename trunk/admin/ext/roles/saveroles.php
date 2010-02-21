<?php
session_start();
// check data first
if (empty($_POST)) { exit; }

require '../../../config.php';

$form   = $_POST['form'];
$success  = null;

// ------------------------------------------------------------ manage roles ---
if ($form == "manage") 
{
  $exts       = isset($_POST['exts']) ? $_POST['exts']      : null;   // form array
  $role_id    = isset($_POST['id'])   ? (int) $_POST['id']  : null;   // role id...
  $role_name  = isset($_POST['name']) ? $_POST['name']      : null;   // ...and name
  
  if (!isset($exts)) {
    // nothing to change...
    notify_request($form, false, "You should check at least one section for that role.");
  }
  
  // check if role name exists
  $exists = db_select(TBL_PREFIX.TBL_ROLES, "id", "name='".$role_name."'");
  if ($exists) {
    notify_request($form, false, "The role <em>".$role_name."</em> already exists.");
  }
  
  if (isset($_POST['create'])) {
    // insert new role with allowed sections
    $success = db_insert(TBL_PREFIX.TBL_ROLES, "name,ext_allowed", "'".$role_name."', '".implode(",", $exts)."'");
  } else if (isset($_POST['update'])) {
    $success = db_update(TBL_PREFIX.TBL_ROLES, "ext_allowed='".implode(",", $exts)."'", "id='".$role_id."'");
  } else if (isset($_POST['delete'])) {
    $success = db_delete(TBL_PREFIX.TBL_ROLES, "id='".$role_id."'");
  }
} 
// ---------------------------------------------------------- describe roles ---
else if ($form == "describe") 
{
  $description  = $_POST['description'];  // form array
  $name  = $_POST['name'];                // form array
  
  if (!isset($_POST['check'])) {
    // nothing to change...
    notify_request($form, false, "You should check at least one role to describe.");
  }
  
  foreach ($_POST['check'] as $id) {
    $success = db_update(TBL_PREFIX.TBL_ROLES, "name='".$name[$id]."', description='".$description[$id]."'", "id='".$id."'");
  }
}

// ---------------------------------------------------------------- redirect ---
notify_request($form, $success);
?>