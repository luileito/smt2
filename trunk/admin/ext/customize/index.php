<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants

// insert custom css and JS files
add_head('<link rel="stylesheet" type="text/css" href="custom.css" />');
add_head('<script type="text/javascript" src="interface.js"></script>');
add_head('<script type="text/javascript" src="sort.js"></script>');

include INC_PATH.'inc/header.php';

// helper function
function display_options($arrOptions) 
{
  foreach ($arrOptions as $o) {
    $list .= '<p>'.$o['description'].'</p>'.PHP_EOL;
    $list .= '<div class="customize-wrap">'.PHP_EOL;
    $list .= ' <div class="customize block">'.PHP_EOL;
    $list .= '  <label for="'.$o['name'].'">'.$o['name'].':</label>'.PHP_EOL;
    $list .= '  <div class="input-wrap">'.PHP_EOL;
    if ($o['type'] == CMS_CHOICE) {
      $checked = ($o['value']) ? 'checked="checked" ' : null;
      $list .= '  <input id="'.$o['name'].'" type="checkbox" name="'.$o['name'].'" '.$checked.'/>'.PHP_EOL;
    } elseif ($o['type'] == CMS_TYPE) {
      $list .= '  <input id="'.$o['name'].'" type="text" name="'.$o['name'].'" class="text center" value="'.$o['value'].'" size="10" maxlength="7" />'.PHP_EOL;  
    }
    $list .= '  </div>'.PHP_EOL;
    $list .= ' </div>'.PHP_EOL;
    $list .= '</div>'.PHP_EOL;
  }
  
  return $list;
}
?>


<h1 id="<?=TBL_CMS?>">CMS &amp; Misc Options</h1>

<?php check_notified_request(TBL_CMS); ?>

<?php if (is_root()) { ?>

  <div id="cms-sortables" class="round">
  
    <div id="info"></div>
    
    <p class="mb">
      <small>Change or <a href="#resetorder" id="resetorder">reset</a> sorting:</small>
    </p>
    
    <div id="sort" class="groupWrapper">
      <?php include 'reset.php'; ?>
    </div><!-- end groupWrapper -->
    
  </div><!-- end cms-sortables -->

<?php } ?>

<div id="cms-options" class="mb pb">
  <form action="savesettings.php" method="post">
    <?php
    $cmsoption = db_select_all(TBL_PREFIX.TBL_CMS, "*", "1");
    echo display_options($cmsoption);
    ?>
    <fieldset>  
      <input type="hidden" name="submit" value="<?=TBL_CMS?>" />
      <input type="submit" class="button round" value="Set CMS options" />
    </fieldset>
  </form>
</div>

<h1>Tracking Visualization Options</h1>

<h2>Flash API</h2>
<p>When using the Flash (SWF) visualization API, all options are customized at runtime. 
These options are stored on a local Shared Object (aka <em>Flash cookie</em>).</p>
<p>You should use this API to visualize the mouse tracking data, since it is dramatically more advanced that the JavaScript one.
However, it relies on the <em>wmode</em> parameter of Flash, 
so if you cannot see the HTML page behind the tracking layer, please use the old (but revised) JavaScript API.</p>


<h2 id="<?=TBL_JSOPT?>" class="vspace">JavaScript API</h2>

<?php check_notified_request(TBL_JSOPT); ?>

<p>If you wish to use the JavaScript (JS) visualization API, you can customize it here.
These options are stored on your MySQL database. <em>Leave fields blank for default values</em>.
</p>

<form action="savesettings.php" method="post">
  <?php
  $jsoption = db_select_all(TBL_PREFIX.TBL_JSOPT, "*", "1");   
  echo display_options($jsoption);
  ?>
  <fieldset>  
    <input type="hidden" name="submit" value="<?=TBL_JSOPT?>" />
    <input type="submit" class="button round" value="Set JS replay options" />
  </fieldset>
</form>

<?php include INC_PATH.'inc/footer.php'; ?>