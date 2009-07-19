<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants

// use ajax settings
require './includes/settings.php';

// insert custom css
add_head('<link rel="stylesheet" type="text/css" href="styles/table.css" />');
include INC_PATH.'inc/header.php';

// display a warning message for javascript-disabled browsers
echo check_noscript();

// check defaults
$show = db_option(TBL_PREFIX.TBL_CMS, "recordsPerTable");
?>

    <div class="center">
      
      <div class="block heading">
        <p class="inline">Now displaying the last <?=$show?> entries from database. Starred rows are new users.</p>
        <noscript>
          <p class="inline">Order by date (descending) and client ID.</p>
        </noscript>
      </div><!-- end block -->
      
      <div id="records">
        <table border="0" cellpadding="10" cellspacing="1">
        <thead>
        <tr>
          <th>client ID</th>
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
          if ($displayMoreButton) {
            echo '<a href="./?page='.++$page.'&amp;'.$resetFlag.'" class="round morebtn" id="more">'.$showMoreText.'</a>';
          } else {
            echo $noMoreText;
          }
        ?>
      </div>
    
    </div><!-- end centered table -->
    
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.stripy.js"></script>
    <script type="text/javascript" src="<?=ADMIN_PATH?>js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript">
    //<![CDATA[
    $(function(){
      
      setupDelBtns();
      
      $('table').stripy().tablesorter({
        headers: {
          3: { sorter: false },
          4: { sorter: false }
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