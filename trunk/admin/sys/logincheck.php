<?php
session_start();
/** 
 * This file is included in any PHP file that needs to check the user login.
 * In that way, anonymous or ungranted users cannot access.  
 */

// check persistent login first
if (isset($_COOKIE['smt-login'])) {
  $_SESSION['login'] = $_COOKIE['smt-login'];
}

if (empty($_SESSION['login'])) 
{
  // redirect to root dir, where user authentication will prompt
  $_SESSION['error'] = "NOT_LOGGED";
  header("Location: ".ABS_PATH."?redirect=".urlencode(url_get_current(true)));
  exit;
}
else 
{
  // check current session login
  $user = db_select(TBL_PREFIX.TBL_USERS, "role_id", "login='".$_SESSION['login']."'");
  $role = db_select(TBL_PREFIX.TBL_ROLES, "ext_allowed", "id='".$user['role_id']."'");
  // save session
  $_SESSION['role_id'] = (int) $user['role_id'];
  $_SESSION['allowed'] = explode(",", $role['ext_allowed']);
  // root user have wide access
  if ($_SESSION['role_id'] === 1) { $_SESSION['allowed'] = ext_available(); }
  // always set available the dashboard!
  array_push($_SESSION['allowed'], "admin");
  
  if (!in_array(ext_name(), $_SESSION['allowed'])) 
  {
    // redirect to admin dir
    $_SESSION['error'] = "NOT_ALLOWED";
    header("Location: ".ADMIN_PATH);
    exit;
  } 
  else 
  {
    // update status
    db_update(TBL_PREFIX.TBL_USERS, "last_access = NOW()", "login = '".$_SESSION['login']."'");
  }
}
?>