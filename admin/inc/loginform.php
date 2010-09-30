<?php
// supress MySQL error if there are no database tables yet
$isInstalled = @db_query("DESCRIBE ".TBL_PREFIX.TBL_RECORDS);
if (!$isInstalled) {
  echo '<p class="center error">Database not found or misconfigured!</p>';
}
?>
<div class="loginwrap">

  <h1 class="title center">(smt)<sup>2</sup></h1>
  <h2 class="center">simple mouse tracking</h2>
  <?php
  if (!empty($_SESSION['login'])) {
    echo '<p class="center"><em>You are already <a href="'.ADMIN_PATH.'">logged in</a> as <strong>'.$_SESSION['login'].'</strong></em></p>'; 
  }
  ?>
  
  
  <form action="<?=ADMIN_PATH?>sys/login.php" method="post" id="loginform" class="round">
    <?php
    if ( isset($_GET['lostpassword']) ) 
    {
      echo '<p class="warning">Write your login name and soon you will receive an email with your new password.</p>';
    } 
    else if ( isset($_SESSION['error']) ) 
    {
      $e = $_SESSION['error'];    
      echo '<h1>'.$_loginMsg[ $e ].'</h1>';
      switch ($e) 
      {
        case "AUTH_FAILED":
          echo '<p><a href="'.ABS_PATH.'?lostpassword">Lost your password?</a></p>';
          break;
        case "USER_ERROR":
          echo '<p>The login name that you requested does not exist.</p>';
          break;
        case "MAIL_SENT":
          echo '<p>Check your mailbox for instructions. Just in case you should check also your SPAM folder.</p>';
          break;
        case "MAIL_ERROR":
          echo '<p class="error">Could not send email.</p>';
          break;
        case "RESET_PASS":
          echo '<p>A new password was sent to your mailbox.</p>';
          break;
        case "UNDEFINED":
          echo '<p>An unknown error occurred.</p>';
          break;
        default:
          break;
      }
      unset($_SESSION['error']);
    }
    ?>
    <fieldset>
      <label for="login">Login name</label>
      <input type="text" name="login" id="login" class="text" size="200" />
    </fieldset>
    
    <?php
    if (!isset($_GET['lostpassword'])) {
    ?>
    <fieldset>  
      <label for="pass">Password</label>
      <input type="password" name="pass" id="pass" class="text" size="200" />
    </fieldset>

    <fieldset> 
      <input type="checkbox" name="remember" id="remember" />
      <label for="remember">Remember me</label>
      <?php 
      if (isset($_GET['redirect'])) {
        echo '<input type="hidden" name="redirect" value="'.$_GET['redirect'].'" />';
      }
      ?>
      <div class="right">
        <input type="hidden" name="action" value="login" />
        <input type="submit" value="Enter" class="button round" />
      </div>
    </fieldset>
    <?php
    } else {
    ?>
    <div>
      <input type="hidden" name="action" value="lostpass" />
      <input type="submit" value="Request password" class="button round recover" />
    </div>
    <?php
    }
    ?>
  </form>
  
  
  <script type="text/javascript">
  // <![CDATA[
  $(function(){
    $('input#login').focus();
  });
  // ]]>
  </script>
</div><!-- end loginwrap -->