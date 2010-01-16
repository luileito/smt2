<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants
include INC_PATH.'inc/header.php';

$ROLES = db_select_all(TBL_PREFIX.TBL_ROLES, "*", "1");
$ROOT = is_root();

// begin helper function -------------------------------------------------------
function format_fields($user, $isAdmin)
{
  global $ROLES, $ROOT;
  $rnd = mt_rand(); // to match correctly label with id
  $self = ($user['login'] === $_SESSION['login']);
  
  $f = "";
  
  if (!$isAdmin && $user !== null) 
  {
    $role = db_select(TBL_PREFIX.TBL_ROLES, "*", "id='".$user['role_id']."'");
    if ($role) {
      $f .= 'Your current role is <strong>'.$role['name'].'</strong>';
      if (!empty($role['description'])) {
        $f .= ' (' . $role['description'] . ').';
      }
    } else {
      $f .= 'You do not have a role assigned.';
    }
  }
  
  if ($isAdmin) {
    $f .= '<div class="wrapper pl">'.PHP_EOL;
  }
  // check user status ---------------------------------------------------------
  if ($user !== null) 
  {
    $timediff = time() - strtotime($user['last_access']);
    if ($timediff < 5*60) {
      $status = "online";
    } else if ($timediff < 10*60) {
      $status = "away";
    } else {
      $status = "offline";
    }
    
    if ($isAdmin) {
      // show pretty dates instead of timestamps if PHP >= 5.2.0
      if (check_systemversion("php", "5.2.0")) {
        $usePrettyDate = true;
        require_once INC_PATH.'sys/class.prettydate.php';
      }
      if ($user !== null) {
        $reg = ($usePrettyDate) ? prettyDate::getStringResolved($user['registered']) : $user['registered'];
        $upd = ($usePrettyDate) ? prettyDate::getStringResolved($user['last_access']) : $user['last_access'];
        $accesses = ' <small class="mini">Registered '.$reg.'. Last access: <em>'.$upd.'</em>.</small>';
      }
    }
    $f .= '<h2>';
    $f .= '<img src="'.ADMIN_PATH.'css/user-'.$status.'.png" alt="['.$status.']" title="User '.$status.'" /> '; 
    $f .= $user['login'].$accesses;
    $f .= '</h2>'.PHP_EOL;
  }
  
  // create form ---------------------------------------------------------------
  $f .= '<form action="saveaccount.php" method="post">'.PHP_EOL;

  $f .= '<fieldset>'.PHP_EOL;
  // the superadmin user cannot change its own role
  if ($ROOT && !$self) {
    $f .= '<div class="fl mr">'.PHP_EOL;
    $f .= '<label for="role_id'.$rnd.'">role</label>'.PHP_EOL;
    // begin select ------------------------------------------------------------
    $f .= '<select id="role_id'.$rnd.'" name="role_id" class="text block">'.PHP_EOL;
    $f .= '<option value="0">...</option>'.PHP_EOL;
    foreach ($ROLES as $role) {
      $selected = ($user['role_id'] == $role['id']) ? ' selected="selected"': null;
      $f .= '<option value="'.$role['id'].'"'.$selected.'>'.$role['name'].'</option>'.PHP_EOL;
    }
    $f .= '</select>'.PHP_EOL;
    // end select --------------------------------------------------------------
    $f .= '</div>'.PHP_EOL;
  }

  $disabled = ($ROOT || ($user['role_id'] != 1 || $self)) ? null : ' disabled="disabled"';
  
  // diplay login
  if ($user === null) {
    $f .= '<div class="fl mr">'.PHP_EOL;
    $f .= '<label for="login'.$rnd.'">login</label>'.PHP_EOL;
    $f .= '<input type="text" id="login'.$rnd.'" name="login" class="text block"'.$disabled.' />'.PHP_EOL;
    $f .= '</div>'.PHP_EOL;
  }
  // common fields
  $f .= '<div class="fl mr">'.PHP_EOL;
  $f .= '<label for="name'.$rnd.'">full name</label>'.PHP_EOL;
  $f .= '<input type="text" id="name'.$rnd.'" name="name" class="text block"'.$disabled.' value="'.$user['name'].'" />'.PHP_EOL;
  $f .= '</div>'.PHP_EOL;
  
  $f .= '<div class="fl mr">'.PHP_EOL;
  $f .= '<label for="email'.$rnd.'">email</label>'.PHP_EOL;
  $f .= '<input type="text" id="email'.$rnd.'" name="email" class="text block"'.$disabled.' value="'.$user['email'].'" />'.PHP_EOL;
  $f .= '</div>'.PHP_EOL;
  
  $f .= '<div class="fl mr">'.PHP_EOL;
  $f .= '<label for="website'.$rnd.'">website</label>'.PHP_EOL;
  $f .= '<input type="text" id="website'.$rnd.'" name="website" class="text block"'.$disabled.' value="'.$user['website'].'" />'.PHP_EOL;
  $f .= '</div>'.PHP_EOL;
  
  // user password prefix
  $display = ($user === null) ? "set" : "change";
  $f .= '<div class="fl mr">'.PHP_EOL;
  $f .= '<label for="pass1'.$rnd.'">'.$display.' password</label>'.PHP_EOL;
  $f .= '<input type="password" id="pass1'.$rnd.'" name="pass1" class="text block"'.$disabled.' />'.PHP_EOL;
  $f .= '</div>'.PHP_EOL;
  // password must be verified
  $f .= '<div class="fl mr">'.PHP_EOL;
  $f .= '<label for="pass2'.$rnd.'">retype password</label>'.PHP_EOL;
  $f .= '<input type="password" id="pass2'.$rnd.'" name="pass2" class="text block"'.$disabled.' />'.PHP_EOL;
  $f .= '</div>'.PHP_EOL;
  
  $f .= '</fieldset>'.PHP_EOL;
  
  // float right
  $f .= '<div class="fr">'.PHP_EOL;
  $form = ($user === null) ? "create" : "manage";
  $f .= '<input type="hidden" name="form" value="'.$form.'" />'.PHP_EOL;
  // override user login if admin is going to update
  if ($user !== null) {
    $f .= '<input type="hidden" name="login" value="'.$user['login'].'" />'.PHP_EOL;
  }
  
  $display = ($user === null) ? "Create new" : "Update";
  $f .= '<input type="submit" class="button round"'.$disabled.' value="'.$display.' account" />'.PHP_EOL;
  $f .= '</div>'.PHP_EOL;
  
  $f .= '</form>'.PHP_EOL;
  
  // the superadmin user cannot delete itself 
  if (is_root() && !$self && $user !== null) {
    $f .= '<form action="saveaccount.php" method="post">'.PHP_EOL;
    $f .= '<div class="fr">'.PHP_EOL;
    $f .= '<input type="hidden" name="login" value="'.$user['login'].'" />'.PHP_EOL;
    $f .= '<input type="hidden" name="form" value="delete" />'.PHP_EOL;
    $f .= '<input type="submit" class="button round delete conf" value="Delete account" />'.PHP_EOL;
    $f .= '</div>'.PHP_EOL;
    $f .= '</form>'.PHP_EOL;
  }
  // insert a small padding
  $f .= '<p class="clear"></p>'.PHP_EOL;
  
  if ($isAdmin) {
    $f .= '</div><!-- end wrapper -->'.PHP_EOL;
  }

  return $f;
}
// end helper function ---------------------------------------------------------


// call this function once
$isadmin = is_admin();

if ($isadmin) 
{
  echo '<h1 id="create">Create new user</h1>'.PHP_EOL;
  check_notified_request("create");
  
  echo format_fields(null, false);
  
  echo '<h1 id="manage">Manage registered users</h1>'.PHP_EOL;
  check_notified_request("manage");
  
  $users = db_select_all(TBL_PREFIX.TBL_USERS, "*", "1");
  foreach ($users as $user) {
    echo format_fields($user, true);
  }
  
} 
else 
{
  // other users can edit their own account 
  echo '<h1 id="manage">My account</h1>';
  check_notified_request("manage");
  
  $user = db_select(TBL_PREFIX.TBL_USERS, "*", "login='".$_SESSION['login']."'");
  echo format_fields($user, $isadmin);
}
?>

<?php include INC_PATH.'inc/footer.php'; ?>