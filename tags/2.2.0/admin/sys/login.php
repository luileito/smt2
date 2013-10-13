<?php
session_start();

if (empty($_POST)) exit;

require '../../config.php';

// common field
$login = trim(strip_tags($_POST['login']));

switch ($_POST['action']) 
{
  case 'login':
    $pass = trim(strip_tags($_POST['pass']));
    // get user pass
    $user = db_select(TBL_PREFIX.TBL_USERS, "pass", "login='".$login."'");
    // authenticate user
    if (md5($pass) === $user['pass'])
    {
      // user can proceed
      $_SESSION['login'] = $login;
      
      $expires = time() + 2592000; // 30 days: 60 * 60 * 24 * 30 = 2592000      
      // if remember is checked, set login cookie
      if (isset($_POST['remember'])) {
        setcookie('smt-login', $login, $expires, "/");
      }
      // in any case, flag *all* registered users to not being recorded
      setcookie('smt-usr', 1, $expires, "/");
      
      // check redirection (if any) or go to Dashboard
      $goto = (isset($_POST['redirect'])) ? urldecode($_POST['redirect']) : ADMIN_PATH;
      header("Location: ".$goto);
      exit;
    } 
    else 
    {
      $_SESSION['error'] = "AUTH_FAILED";
    }
    break;
    
  case 'lostpass':
    $user = db_select(TBL_PREFIX.TBL_USERS, "id,email", "login='".$login."'");
    
    if (!$user) {
      $_SESSION['error'] = "USER_ERROR";
      break;
    }
    // set message
    $msg  = "It seems that you requested a new (smt) password. If you did not request it, please ignore this email.".PHP_EOL.PHP_EOL;
    
    $msg .= "To reset $login's password follow this link:".PHP_EOL;
    $msg .= ADMIN_PATH.'sys/resetpass.php?u='.$user['id'].'&v='.md5($user['email']).PHP_EOL;
    
    $msg .= PHP_EOL."--------------".PHP_EOL;
    $msg .= "(smt) simple mouse tracking";
    
    // compose email    
    require './class.phpmailer.php';
    $mail = new PHPMailer;
    $mail->FromName  = "smt2";
    $mail->From      = "no-reply@".$_SERVER['HTTP_HOST'];
    $mail->CharSet   = "utf-8";
    $mail->Subject   = "smt2 - Password requested for ".$login;
    $mail->Body      = $msg;
    $mail->AddAddress($user['email']);
    // send
    $_SESSION['error'] = $mail->Send() ? "MAIL_SENT" : "MAIL_ERROR";
    break;
    
  default:
    // otherwise, require authentication
    $_SESSION['error'] = "AUTH_FAILED";
    break;
}

// by default, redirect to login page
header("Location: ".ABS_PATH);
?>
