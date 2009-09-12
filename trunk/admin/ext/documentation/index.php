<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants
include INC_PATH.'inc/header.php';

// shorcut to CSS styles
define ('CSS_PATH', ADMIN_PATH."css/");
?>


<p>
  Developers may read the <a href="./php/">PHP</a> and <a href="./js/">JS</a> APIs for writing their own scripts.
  Designers may have a look at the CSS files on <?=CSS_PATH?>: 
  <a href="<?=CSS_PATH?>base.css">base</a>, <a href="<?=CSS_PATH?>admin.css">admin</a>, <a href="<?=CSS_PATH?>theme.css">theme</a>.
</p>

<p>More information about (smt)<sup>2.0</sup> can be found on <a rel="external" href="http://code.google.com/p/smt2/w/list">(smt) Google code wiki</a> pages:</p>
<ul>
  <li><a rel="external" href="http://code.google.com/p/smt2/wiki/readme">the basics</a></li>
  <li><a rel="external" href="http://code.google.com/p/smt2/wiki/CMS">about this CMS</a></li>
  <li><a rel="external" href="http://code.google.com/p/smt2/wiki/roles">understanding roles</a></li>
</ul>


<?php include INC_PATH.'inc/footer.php'; ?>