<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';
// now you have access to all CMS API

// use ajax settings
require './includes/settings.php';

// insert custom CSS and JS files
$headOpts = array(
  '<link rel="stylesheet" type="text/css" href="styles/table.css" />',
  '<link rel="stylesheet" type="text/css" href="styles/ui-lightness/custom.css" />'
);
add_head($headOpts);


include INC_DIR.'header.php';

// display a warning message for javascript-disabled browsers
echo check_noscript();

// check defaults from DB or current sesion
$show = (isset($_SESSION['limit']) && $_SESSION['limit'] > 0) ? $_SESSION['limit'] : db_option(TBL_PREFIX.TBL_CMS, "recordsPerTable");
// sanitize
if (!$show) { $show = $defaultNumRecords; }
?>

    <div class="center">
      <!-- Order by date (descending) and client ID -->
      <h1>User logs</h1>
      
      <div id="records">
        <?php check_notified_request("records") ?>
        <table class="cms" cellpadding="10" cellspacing="1">
        <thead>
        <tr>
          <th>client ID</th>
          <th>location</th>
          <th>page ID</th>
          <th>date</th>
          <th>aprox. time</th>
          <th># clicks</th>
          <!--<th>visualize</th>-->
          <th>action</th>
        </tr>
        </thead>
        <tbody>
          <?php include './includes/tablerows.php'; ?>
        </tbody>
        </table>
        
        <?php
        // the 'more' button
        if (!empty($displayMoreButton)) {
          echo '<a href="./?page='.++$page.'&amp;'.$resetFlag.'" class="round button morebtn" id="more">'.$showMoreText.'</a>';
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
            $select = (isset($_SESSION[$id]) && $entry['id'] == $_SESSION[$id]) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$entry['id'].'">'.$entry['name'].'</option>'; 
          }
          $s .= '</select>';
          return $s;
        }
        function select_date($id)
        { 
          $d  = '<label for="'.$id.'" class="ml">'.ucfirst($id).'</label> ';
          $val = (!empty($_SESSION['filterquery']) && isset($_SESSION[$id])) ? $_SESSION[$id] : null;
          $d .= '<input type="text" id="'.$id.'" name="'.$id.'" class="text datetime" value="'.$val.'" />';
          return $d;
        }
        function select_cache() 
        {
          $s  = '<label for="cache">Page ID</label> ';
          $s .= '<select id="cache" name="cache_id" class="mr">';
          $s .= '<option value="">---</option>';
          $row = db_select_all(TBL_PREFIX.TBL_CACHE, "*", "1 ORDER BY id DESC");
          // pad with zeros the page id
          $num = strlen( count($row) );
          foreach ($row as $entry) {
            $select = (isset($_SESSION['cache_id']) && $entry['id'] == $_SESSION['cache_id']) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$entry['id'].'">'.pad_number($entry['id'],$num).': '.trim_text($entry['title']).'</option>';
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
            $select = (isset($_SESSION['client_id']) && $entry['client_id'] == $_SESSION['client_id']) ? 'selected="selected"' : null;
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
                        "cache_id"   => "Page ID",
                        "ip"         => "Location"
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
            $select = (!empty($_SESSION['limit']) && $n == $_SESSION['limit']) ? 'selected="selected"' : null;
            $s .= '<option '.$select.' value="'.$n.'">'.$n.'</option>'; 
          }
          $s .= '</select>';
          return $s;
        }
        function input_time($id) 
        {
          $value = (!empty($_SESSION[$id]) && strlen($_SESSION[$id]) < 5) ? $_SESSION[$id] : "0";
          $c  = '<label for="'.$id.'" class="ml">'.$id.'</label> ';
          $c .= '<input type="text" class="text" size="2" id="'.$id.'" name="'.$id.'" value="'.$value.'" />';
          return $c;
        }
        ?>
      </div>
      
		<?php if (db_records()) { ?>
		
      <hr />
		
      <h1 class="mt" id="mine">Mine results</h1>
      <?php check_notified_request("mine"); ?>
      
      <em>Leave fields blank for default values</em>
      <form id="filter" class="center" action="filter.php" method="post">
        <fieldset class="smallround">
          <legend>Filter by</legend>
          <?php
            echo select_cache();
            echo select_client();
            echo select_tbl(TBL_PREFIX.TBL_OS, "os_id", "Operating System");
            echo select_tbl(TBL_PREFIX.TBL_BROWSERS, "browser_id", "Browser");
            echo checkbox("ftu", "Only first-time users");
          ?>
        </fieldset>
        <fieldset class="clear smallround">
          <legend>Grouping</legend>
          <?=select_records()?>
          <?=select_group()?>
        </fieldset>
        <fieldset class="clear smallround">
          <legend>Date range</legend>
          <?=select_date("from")?>
          <?=select_date("to")?>
        </fieldset>
        <fieldset class="clear smallround">
          <legend>Time range (seconds)</legend>
          <div id="slider-wrap">
            <div id="slider-range">
              <?=input_time("mintime")?>
              <?=input_time("maxtime")?>
            </div>
            <p class="center" id="slider-amount"></p>
          </div><!-- end slider-wrap -->
        </fieldset>
        <fieldset class="clear smallround">
          <legend>Action</legend>
          <input type="submit" class="button round" value="Apply filter" />
          <input type="submit" name="reset" class="button round" value="Reset filter" />
			 <?php
			 /*
			 // massive bulk function (not implemented)
			 if (is_root() && isset($_SESSION['filterquery'])) {
				echo '<input type="submit" name="delete" class="button round delete conf" value="Delete filtered logs" />';
			 }
			 */
			 ?>
        </fieldset>
        <fieldset class="clear smallround">
          <legend>Export</legend>
            <!--<?=checkbox("export-all", "Whole database")?>-->
            <label for="csv">Format:</label>

            <input id="csv" type="radio" name="format" class="radio" value="csv" checked="checked" />
            <label for="csv"><abbr title="Comma Separated Values">CSV</abbr></label>
            
            <input id="tsv" type="radio" name="format" class="radio" value="tsv" />
            <label for="tsv"><abbr title="Tab Separated Values">TSV</abbr></label>
<!--
            <input id="txt" type="radio" name="format" class="radio" value="txt" /> 
            <label for="txt"><abbr title="plain TeXT (each field is preceded by a newline char)">TXT</abbr></label>

            <input id="xml" type="radio" name="format" class="radio" value="xml" />
            <label for="xml"><abbr title="eXtensible Markup Language">XML</abbr></label>
-->
            <input type="submit" class="button round" name="download" value="Download logs" />
            <?php
					    /*if (!isset($_SESSION['filterquery'])) {
						    echo checkbox("dumpdb", "Dump whole database");
					    }*/
				    ?>
				  <!--
          <p class="left">
            <small>
              <sup>1</sup> each log is stored in a single file, then all are compressed in a ZIP file.
            <br />
              <sup>2</sup> all logs are dumped in a single file (logs are separated by a newline).
            </small>
          </p>
          -->
        </fieldset>

      </form>
      
	 <?php } ?>
	 
    </div><!-- end centered table -->
    
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.stripy.js"></script>
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.ui.core.js"></script>
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.ui.datepicker.js"></script>
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.ui.slider.js"></script>
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.ui.timepicker.js"></script>
    <script type="text/javascript">
    //<![CDATA[
    $(function(){

      // shorcut for jQuery selectors
      var legends = "fieldset legend";
      var records = "#records table";
      
  		// shorcut to (smt)2 aux functions
  		var aux = window.smt2fn;
  		// check saved cookie
  		var cookieId = "smt-hiddenFieldsets";
  		if (aux.cookies.checkCookie(cookieId)) {
  			var hide = aux.cookies.getCookie(cookieId).split(",");
  			for (var i = 0; i < hide.length; ++i) {
  				$(legends).eq(hide[i]).nextAll().toggle();
  			}
  		}
  		// save routine
  		function savePos()
  		{
  			var hideElems = [];
  			$(legends).attr('title', "Toggle fieldset").each(function(i, val) {
  				if ( $(this).nextAll().is(':hidden') ) {
  					hideElems.push(i);
  				}
  			});
  			aux.cookies.setCookie(cookieId, hideElems, 30);
  		};
  		
  		// click behaviour
  		$(legends).attr('title', "toggle fieldset").css('cursor', "pointer").click(function(){
    			var elems = $(this).nextAll();
    			elems.toggle();
    			savePos();
  		});

      // display nice table
      $(records).stripy().tablesorter({
          headers: {
            6: { sorter: false }
          },
          cssHeader: "headerNormal"
      });
      
      // date picker UI widget
      $('.datetime').datepicker({
        	duration: '',
          showTime: true,
          constrainInput: false,
          beforeShow: function(i,e) {
            e.dpDiv.css( 'z-index', aux.getNextHighestDepth() );
          }
      });
      
      // slider UI widget
      var sliderElem = $('#slider-range');
      var minInput = $('input#mintime');
      var maxInput = $('input#maxtime');
      
      function formatSlider(arrValues)
      {
        $("#slider-amount").html('min. ' + arrValues[0] + ' &mdash; max. ' + arrValues[1]);
      };

      <?php
      $time = db_select(TBL_PREFIX.TBL_RECORDS, "MAX(sess_time) as max", 1);
      $maxTime = ceil( $time['max'] );
      if (!isset($_SESSION['filterquery'])) {
      ?>
      // set time range (a log-normal mapping function should be used here...)
      minInput.val( Math.ceil(<?=$maxTime/2 - $maxTime/4?>) );
      maxInput.val( Math.floor(<?=$maxTime/2 + $maxTime/4?>) );
      <?php
      }
      ?>
      // hide regular input fields
      sliderElem.find("input,label").hide();
      
      sliderElem.slider({
    			range: true,
    			min: 0,
    			max: <?=$maxTime?>,
    			//step: 5,
    			values: [minInput.val(), maxInput.val()],
    			slide: function(event, ui) {
    				formatSlider(ui.values);
    			},
    			stop: function(event, ui) {
            minInput.val(ui.values[0]);
            maxInput.val(ui.values[1]);
          }
  		});
  		formatSlider( sliderElem.slider("values") );
      
      // append more records to main table (see include/settings.php)
      var page = <?=$page?>;
      var show = <?=$show?>;
      var more = $('a#more');
      more.click(function(e){
          // remove focus
          $(this).blur();
          // async request
          $.get('includes/tablerows.php?page='+page+'&show='+show, function(data){
              $(records+' tbody').append(data);
              $(records).stripy().trigger("update");
              // update CMS links, delete buttons, etc.
              SetupCMS.all();
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
      
    });
    //]]>
    </script>

<?php include INC_DIR.'footer.php'; ?>
