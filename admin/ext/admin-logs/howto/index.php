<?php
// server settings are required - relative path to smt2 root dir
require '../../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';
// now you have access to all CMS API

add_head(array('<link rel="stylesheet" type="text/css" href="howto.css" />'));

include INC_DIR.'header.php';
?>

<p>&larr; <a href="../">Back to admin logs</a></p>

<div id="admin-logs-help" class="mt">

<h2>HowTo Guides</h2>

<p>
This page provides you with some tips to take advantage of the admin logs section.
Please be sure to read the <a href="https://code.google.com/p/smt2/w/list">Google code wiki pages</a> for basic info about smt2.
</p>

<div id="tips">

  <h3>Admin logs table</h3>
  <ol>
    <li>Put your mouse over row entries underlined <abbr title="tooltip example">like this</abbr> to display a tooltip with more details about such entries.
    </li>
    <li>
      On the 'actions' column: 
        the play icon loads the cursor replay visualizations;
        the note icon allows you to analyze each log in detail;
        and the disk icon allows you to download the raw log.
    </li>
  </ol>
  
  <h3>Mine results form</h3>
  <ol>  
    <li>
      The 'Filter by' fieldset allows you to display only those logs that match your desired criteria.
      For instance, show only Firefox visits on Windows for a certain page.
    </li>
    <li>
      The 'Grouping' fieldset allows you to merge logs. Group results by page to replay various visits simultaneously!
    </li>
    <li>    
      The 'first-time users' option shows only visits of new users.
    </li>    
  </ol>
  
  <h3>Visualization panel</h3>
  <p>To reveal this panel, just CTRL + Click while watching a cursor replay movie.</p>
  
  <img src="controlpanel.jpg" alt="THE control panel ;)" />
  
  <p>Now, a brief explanation of the important parts:</p>
  <ol>  
    <li>
      <span class="subpanel">[Layers] </span>
        <ol>
          <li>      
            Please <a href="https://code.google.com/p/smt2/wiki/VisualizationShortcuts">read this page first</a>.
          </li>
        </ol>
    </li>
    <li>
      <span class="subpanel">[Custom selections]</span>
        <ol>
          <li>
            This subpanel allows you to quickly turn on/off visualization layers. Feel free to experiment.
          </li>
        </ol>
    </li>
    <li>
      <span class="subpanel">[Time charts]</span>
        <ol>
          <li>
            These charts plot cursor trails against time, which can give you an overview of some tendencies 
            like reading patterns (X coords), scrolling behavior (Y coords), or page persistence (3D chart).
          </li>
        </ol>        
    </li>
    <li>
      <span class="subpanel">[Colors]</span>
        <ol>
          <li>This subpanel is self-explanatory ;)</li>
        </ol>          
    </li>
    <li>
      <span class="subpanel">[Visualization]</span>
        <ol>
          <li>If replay in realtime is disabled, cursor trackas are displayed as a single image.</li>
          <li>Enable 'skip dwell times' to replay only cursor movements.</li>
          <li>Enable 'use shadowmaps' to display heapmaps-like visualization of coordinates and clicks.</li>
          <li>Enable 'autoplay trails' to load all logs for a particular user sequentially, as if you were watching TV.</li>
        </ol>          
    </li>
    <li>
      <span class="subpanel">[Hypernotes]</span>
        <ol>
          <li>
            When watching a movie, click on the main (big, white) cursor to add HTML annotations.
            This can be useful to let co-workers know that you have reviewed a replay,
            or to mark a particularly interesting behavior.
          </li>
        </ol>
    </li>      
  </ol>

  <h3>Need more help?</h3>
  <p>If you feel that more details should be given on this page, please do not hesitate to contact me!</p>
      
</div><!--#tips-->

</div><!--#admin-logs-help-->

<?php include INC_DIR.'footer.php'; ?>
