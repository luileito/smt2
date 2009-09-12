<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants

// use ajax settings
require './includes/settings.php';

// insert custom css and (smt)2 aux functions for cookie management
add_head('<link rel="stylesheet" type="text/css" href="styles/table.css" />');
add_head('<script type="text/javascript" src="'.SMT_AUX.'"></script>');

include INC_PATH.'inc/header.php';

// display a warning message for javascript-disabled browsers
echo check_noscript();

// check defaults
$show = (!empty($_SESSION['limit'])) ? $_SESSION['limit'] : db_option(TBL_PREFIX.TBL_CMS, "recordsPerTable");
// sanitize
if (!$show) { $show = 20; }
?>

    <div class="center">
      <!-- Order by date (descending) and client ID -->
      <h1>User logs</h1>
      
      <div id="records">
        <?php check_notified_request("records") ?>
        <table border="0" cellpadding="10" cellspacing="1">
        <thead>
        <tr>
          <th>client ID</th>
          <th>page ID</th>
          <th>date</th>
          <th>time</th>
          <th>visualize</th>
          <th>action</th>
        </tr>
        </thead>
        <tbody>
          <?php include './includes/tablerows.php'; ?>
        </tbody>
        </table>
        
        <?php
        // the 'more' button
        if ($displayMoreButton) {
          echo '<a href="./?page='.++$page.'&amp;'.$resetFlag.'" class="round morebtn" id="more">'.$showMoreText.'</a>';
        } else {
          echo $noMoreText;
        }
        
        // helper functions
        function checkbox($id, $label)
        {
          $select = (isset($_SESSION[$id])) ? 'checked="checked"' : null;
          $c  = '<input type="checkbox" '.$select.' id="'.$id.'" name="'.$id.'" />';
          $c .= ' <label for="'.$id.'" class="mr">'.$label.'</label>';
          return $c;
        }
        function select_tbl($table,$id,$label)
        {
          $s  = '<label for="'.$id.'">'.$label.'</label> ';
          $s .= '<select id="'.$id.'" name="'.$id.'" class="mr">';
          $s .= '<option value="">---</option>';
          $row = db_select_all($table, "*", "1");
          foreach ($row as $entry) {
            $select = ($entry['id'] == $_SESSION[$id]) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$entry['id'].'">'.$entry['name'].'</option>'; 
          }
          $s .= '</select>';
          return $s;
        }
        function select_date($id)
        { 
          $d  = '<label for="'.$id.'" class="ml">'.ucfirst($id).'</label> ';
          $d .= '<select id="'.$id.'" name="'.$id.'day">';
          $d .= '<option value="">---</option>';
          for ($i = 1; $i <= 31; ++$i) {
            $select = ($i == $_SESSION[$id.'day']) ? 'selected="selected"' : null;
            $d .= '<option '.$select.' value="'.$i.'">'.date("d", mktime(0,0,0,date("n"),$i)).'</option>';
          }
          $d .= '</select>';
          
          $d .= '<select name="'.$id.'month">';
          $d .= '<option value="">---</option>';
          for ($i = 1; $i <= 12; ++$i) {
            $select = ($i == $_SESSION[$id.'month']) ? 'selected="selected"' : null;
            $d .= '<option '.$select.' value="'.$i.'">'.date("F", mktime(0,0,0,$i)).'</option>';
          }
          $d .= '</select>';
          
          $d .= '<select name="'.$id.'year">';
          $d .= '<option value="">---</option>';
          for ($i = 1; $i >= -1; --$i) {
            $year = date("Y", mktime(0,0,0,1,1,date("Y")-$i) );
            $select = ($year == $_SESSION[$id.'year']) ? 'selected="selected"' : null;
            $d .= '<option '.$select.' value="'.$year.'">'.$year.'</option>';
          }
          $d .= '</select>';
          
          $d .= ' <label for="'.$id.'hour">@</label> ';
          $d .= '<select id="'.$id.'hour" name="'.$id.'hour">';
          $d .= '<option value="">---</option>';
          for ($i = 0; $i <=23; ++$i) {
            $select = (isset($_SESSION[$id.'hour']) && $i == $_SESSION[$id.'hour']) ? 'selected="selected"' : null;
            $d .= '<option '.$select.' value="'.$i.'">'.$i.'</option>';
          }
          $d .= '</select>';
          $d .= "<span> : </span>"; // marked for jQuery toggle()
          $d .= '<select name="'.$id.'minute">';
          $d .= '<option value="">---</option>';
          for ($i = 0; $i <=59; ++$i) {
            $select = (isset($_SESSION[$id.'minute']) && $i == $_SESSION[$id.'minute']) ? 'selected="selected"' : null;
            $d .= '<option '.$select.' value="'.$i.'">'.$i.'</option>';
          }
          $d .= '</select>';
          
          return $d;
        }
        function select_cache() 
        {
          $s  = '<label for="cache">Page ID</label> ';
          $s .= '<select id="cache" name="cache_id" class="mr">';
          $s .= '<option value="">---</option>';
          $row = db_select_all(TBL_PREFIX.TBL_CACHE, "*", "1");
          foreach ($row as $entry) {  
            $select = ($entry['id'] == $_SESSION['cache_id']) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$entry['id'].'">'.$entry['id'].': '.trim_text($entry['title']).'</option>'; 
          }
          $s .= '</select>';
          return $s;
        }
        function select_client() 
        {
          $s  = '<label for="client">Client ID</label> ';
          $s .= '<select id="client" name="client_id" class="mr">';
          $s .= '<option value="">---</option>';
          $row = db_select_all(TBL_PREFIX.TBL_RECORDS, "DISTINCT client_id", "1");
          foreach ($row as $entry) {
            $select = ($entry['client_id'] == $_SESSION['client_id']) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$entry['client_id'].'">'.mask_client($entry['client_id']).'</option>'; 
          }
          $s .= '</select>';
          return $s;
        }
        function select_group() 
        {
          $s  = '<label for="groupby">Group result by</label> ';
          $s .= '<select id="groupby" name="groupby" class="mr">';
          $s .= '<option value="">---</option>';
          $opt = array(
                        "client_id"  => "Client ID",
                        "cache_id"   => "Page ID"
                      );
          foreach ($opt as $key => $entry) {
            $select = (!empty($_SESSION['groupby']) && $key == $_SESSION['groupby']) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$key.'">'.$entry.'</option>'; 
          }
          $s .= '</select>';
          return $s;
        }
        function select_records()
        { 
          $s  = '<label for="limit">Records per query</label> ';
          $s .= '<select id="limit" name="limit" class="mr">';
          $s .= '<option value="">---</option>';
          $num = array(10,20,50,100,200,500,1000);
          foreach ($num as $n) {
            $select = ($n == $_SESSION['limit']) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$n.'">'.$n.'</option>'; 
          }
          $s .= '</select>';
          return $s;
        }
        function input($id) 
        {
          $value = (!empty($_SESSION[$id]) && strlen($_SESSION[$id]) < 5) ? $_SESSION[$id] : "";
          $c  = '<label for="'.$id.'" class="ml">'.$id.'</label> ';
          $c .= '<input type="text" class="text" size="2" id="'.$id.'" name="'.$id.'" value="'.$value.'" />';
          return $c;
        }
        ?>
      </div>
      
		<?php if (db_records()) { ?>
		
      <hr />
		
      <h1 class="mt">Mine results</h1>
      <em>Leave fields blank for default values</em>
      <form id="filter" class="center" action="filter.php" method="post">
        <fieldset>
          <legend>Filter by</legend>
          <?php
            echo select_cache();
            echo select_client();
            echo select_tbl(TBL_PREFIX.TBL_OS, "os_id", "Operating System");
            echo select_tbl(TBL_PREFIX.TBL_BROWSERS, "browser_id", "Browser");
            echo checkbox("newusers", "Show only first visits");
          ?>
        </fieldset>
        <fieldset class="clear">
          <legend>Grouping</legend>
          <?=select_records()?>
          <?=select_group()?>
        </fieldset>
        <fieldset class="clear">
          <legend>Date range</legend>
          <?=select_date("from")?>
          <?=select_date("to")?>
        </fieldset>
        <fieldset class="clear">
          <legend>Time range (seconds)</legend>
          <?=input("min")?>
          <?=input("max")?>
        </fieldset>
        <fieldset class="clear">
          <legend>Action</legend>
          <input type="submit" class="button round" value="Apply filter" />
          <input type="submit" name="reset" class="button round" value="Reset filter" />
        </fieldset>
      </form>
      
	 <?php } ?>
	 
    </div><!-- end centered table -->
    
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.stripy.js"></script>
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript">
    //<![CDATA[
    $(function(){
		// shorcut to (smt)2 aux functions
		var aux = smtAuxFn;
		// check saved cookie
		if (aux.cookies.checkCookie('hiddenFieldsets')) {
			var hide = aux.cookies.getCookie('hiddenFieldsets').split(",");
			for (var i = 0; i < hide.length; ++i) {
				$('fieldset legend').eq(hide[i]).nextAll().toggle();
			}
		}
		// save routine
		function savePos() 
		{
			var hideElems = [];
			$('fieldset legend').each(function(i, val) {
				if ( $(this).nextAll().is(":hidden") ) {
					hideElems.push(i);
				}
			});
			aux.cookies.setCookie('hiddenFieldsets', hideElems, 30);
		};
		// click behaviour
		$('fieldset legend').css({cursor:"pointer"}).click(function(){
			var elems = $(this).nextAll();
			elems.toggle(); 
			savePos();
		});
		// delete buttons must be set each time a new query is set
      setupDelBtns();
      // nice table
      $('table').stripy().tablesorter({
        headers: {
          4: { sorter: false },
          5: { sorter: false }
        },
        cssHeader: "headerNormal"
      });
      
      var page = <?=$page?>;
      var show = <?=$show?>;
      var more = $('a#more');
      more.click(function(e){
          $.get('includes/tablerows.php?page='+page+'&show='+show, function(data){
              $('table tbody').append(data);  
              $('table').stripy().trigger("update");
              // update external links and delete buttons
              $('a[rel=external]').attr("target", "_blank");
              setupDelBtns();
              // increment page counter
              ++page;
              // remove the 'more' link if there are no more records
              var r = new RegExp('<?=$noMoreText?>');
              var s = data.search(r);
              if (s != -1) { 
                more.parent().append('<?=$noMoreText?>');
                more.remove(); 
              }
          });
          // cancel default action
          e.preventDefault(); 
      });
      
      // use this function instead of parsing .conf class because deletions are asynchronous
      function setupDelBtns(){
        // delete records link
        $('a.del').click(function(e){
          var deleteLink = $(this).attr("href");
          var content = $(this).parent();
          var question = "Are you sure? This operation cannot be undone.";
          if (confirm(question)) {
            $.get(deleteLink, function(data){
                content.html('<em>'+data+'</em>');
                // delete row
                var row = content.parent();
                row.addClass("deleted");
                setTimeout(function(){
                  row.fadeOut("fast");
                }, 2000);
            });
          }
         e.preventDefault(); 
        });
      };
       
    });
    //]]>
    </script>

<?php include INC_PATH.'inc/footer.php'; ?>