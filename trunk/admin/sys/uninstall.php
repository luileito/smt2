<?php 
session_start();
/**
 * Uninstall script.
 * Drops all (smt) tables from database and delete cache logs.
 * It will try to remove also JavaScript cookies and Flash LSO.
 * @date 16 Jan 2010
 */
require '../../config.php';

// supress MySQL error if there are no database tables yet
$isInstalled = @db_query("DESCRIBE ".TBL_PREFIX.TBL_RECORDS);

include INC_DIR.'doctype.php';
?>


<head>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=CMS_TITLE?> | uninstall</title>

  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>base.css" />
  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>theme.css" />
  <link rel="stylesheet" type="text/css" href="<?=CSS_PATH?>install.css" />
  
  <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery-1.7.2.min.js"></script>
  <script type="text/javascript" src="<?=ADMIN_PATH?>js/flashdetect.min.js"></script>
  <script type="text/javascript" src="<?=ADMIN_PATH?>js/setupcms.js"></script>
  
  <script type="text/javascript" src="<?=SMT_AUX?>"></script>
  <script type="text/javascript" src="<?=SWFOBJECT?>"></script>

</head>


<body>

<div id="global">

<h1><strong>smt2</strong> uninstaller</h1>

<?php
if ($isInstalled) {

  // is root logged?
  if (!is_root()) { die_msg($_loginMsg["NOT_ALLOWED"]); }
  
  if (isset($_POST['submit']))
  {
    $msgs = array();
    
    if (isset($_POST['droptables'])) {
      // delete cache logs first
      $logs = db_select_all(TBL_PREFIX.TBL_CACHE, "file", 1);
      foreach ($logs as $log) {
        if (is_file(CACHE_DIR.$log)) {
          unlink(CACHE_DIR.$log);
        }
      }
      // then delete (smt) tables
      foreach ($_lookupTables as $table) {
        db_query("DROP TABLE ".TBL_PREFIX.$table);
      }
      // notify
      $msgs[] = 'Tables were dropped.';
      $msgs[] = 'Cache logs were deleted.';
    }

    if (isset($_POST['dropdb'])) {
      db_query("DROP DATABASE ".DB_NAME);
      $msgs[] = 'Database was dropped.';
    }
?>


    <h3 class="ok">uninstall tasks were performed</h3>
    <ul class="ml">
      <?php
      foreach ($msgs as $m) {
        echo '<li>' . $m . '</li>';
      }
      ?>
    </ul>

    <?php if (isset($_POST['removejs'])) { ?>

      <script type="text/javascript">
      //<![CDATA[
      $(function(){
        // shorcut to (smt)2 aux functions
    		var aux = smt2fn;

        var cookies = document.cookie.split('; ');
        for (var i = 0, c = cookies.length; i < c; ++i)
        {
          var cookie = cookies[i];
          if ( /smt-/i.test(cookie) ) {
            aux.cookies.deleteCookie(cookie);
          }
        }
        // notify
        $('h3.ok + ul').append("<li>JavaScript cookies were removed.</li>");
      });
      //]]>
      </script>

    <?php } ?>
  
  
    <?php if (isset($_POST['removeswf'])) { ?>

      <p id="delLSO">SWF cookies uninstaller should replace this text.</p>
      <script type="text/javascript">
      //<![CDATA[
      $(function(){
        swfobject.embedSWF("<?=SWF_PATH?>deleteLSO.swf", "delLSO", "100%", 5, "9.0.0");
        // notify
        $('h3.ok + ul').append("<li>Flash cookies were removed.</li>");
      });
      //]]>
      </script>

    <?php } ?>
  
  
<?php
  }
  else
  {
    // no POST data yet; show form
?>


  <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
  <fieldset>
    <input type="checkbox" id="droptables" name="droptables" checked="checked" class="ml" />
      <label for="droptables">Drop tables (will delete also cache logs)</label>
    <input type="checkbox" id="dropdb" name="dropdb" class="ml" />
      <label for="dropdb">Drop database</label>
    <input type="checkbox" id="removejs" name="removejs" checked="checked" class="ml" />
      <label for="removejs">Remove JS cookies</label>
    <input type="checkbox" id="removeswf" name="removeswf" checked="checked" class="ml" />
      <label for="removeswf">Remove Flash settings</label>
  </fieldset>
  <fieldset>
    <input type="submit" name="submit" value="Uninstall" class="button round conf" />
  </fieldset>
  </form>


<?php
  }
  
  
} else {
  // (smt) is not installed
?>


  <h3 class="ko">smt2 is not installed</h3>
  <p>You can safely remove the directory <?=ABS_PATH?></p>
  
  
<?php
}
?>


</div><!-- end global div -->

</body>

</html>
