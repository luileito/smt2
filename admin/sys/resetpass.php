<?php
$u = $_GET['u'];  // user id
$v = $_GET['v'];  // verification code

if (empty($u) || empty($v))  exit;

require '../../config.php';

$user = db_select(TBL_PREFIX.TBL_USERS, "email", "id='".$u."'");

if (md5($user['email']) === $v) 
{
  $newpass = generate_password();
  $update = db_update(TBL_PREFIX.TBL_USERS, "pass='".$newpass."'", "id='".$u."'");
  if (!$update) { 
    $_SESSION['error'] = UNDEFINED;
    exit;
  }
  // set message
  $msg  = "Your new password is: ".$newpass.PHP_EOL;
  $msg .= "Once logged in, you can change it on the 'users' section.".PHP_EOL;
  $msg .= "--------------".PHP_EOL;
  $msg .= "(smt) simple mouse tracking";
  // send new password
  require './class.phpmailer.php';
  $mail = new PHPMailer;
  $mail->FromName  = "(smt)2";
  $mail->From      = "no-reply@".$_SERVER['HTTP_HOST'];
  $mail->CharSet   = "utf-8";
  $mail->Subject   = "(smt)2 - Password reset for ".$login;
  $mail->Body      = $msg;
  $mail->AddAddress($user['email']);
  
  $_SESSION['error'] = $mail->Send() ? RESET_PASS : MAIL_ERROR;
} 
else 
{
  $_SESSION['error'] = NOT_ALLOWED;
}
// go to login page
header("Location: ".ABS_PATH);
?>