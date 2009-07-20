<?php
session_start();
// check data first
if (empty($_POST)) { exit; }

require '../../../config.php';

// dynamic (clean) variable creation
foreach ($_POST as $var => $value) 
{
  $$var = strip_tags(trim($value));
}

//var_dump($_POST); exit;

// ------------------------------------------------------------- delete user ---
if ($form == "delete") {
  // override $form to display the message under <h1 id="manage"> 
  $form = "manage";
  db_delete(TBL_PREFIX.TBL_USERS, "login='".$login."'");
  
  notify_request($form, 'User <em>'.$login.'</em> was deleted.');
}

// ------------------------------------------------------------- basic check ---
if ($form == "create" && empty($login)) {
  notify_request($form, false, "You must write the user login.");
}
else if ($pass1 != $pass2 ) {
  notify_request($form, false, "You must verify the password.");
}


// ----------------------------------------------------------------- actions ---      
switch ($form) 
{
    case 'create':
      // check if login exists
      $exists = db_select(TBL_PREFIX.TBL_USERS, "id", "login='".$login."'");
      if ($exists) {
        notify_request($form, false, 'The user <em>'.$login.'</em> already exists');
      }
      $values = "'".$role_id."', '".$login."', MD5('".$pass1."'), '".$name."', '".$email."', '".$website."', NOW()";
      $success = db_insert(TBL_PREFIX.TBL_USERS, "role_id,login,pass,name,email,website,registered", $values);
      break;
      
    case 'manage':
      $tuples = "";
      // check password
      if (!empty($pass1)) {
        $tuples .= "pass=MD5('".$pass1."'),";
      }
      // dont't remove root permissions!
      if (is_root()) { $tuples .= "role_id='1',"; }
      $tuples .= "name='".$name."', email='".$email."', website='".$website."'";
      $success = db_update(TBL_PREFIX.TBL_USERS, $tuples, "login='".$login."'");
      break;
      
    default:
      break;
}
// ---------------------------------------------------------------- redirect ---
notify_request($form, $success);
?>