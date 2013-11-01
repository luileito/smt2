<?php
if (empty($_POST)) exit;

// turn off error reporting ...
error_reporting(0);
// ... because db_connect() will trigger a Notice error only when installing for the first time

// define a helper function for query the DB
function try_sql_query($sql)
{
  if (db_query($sql)) {
    echo '<strong class="ok">Ok.</strong><br />';
  } else die ('<strong class="ko">Error.</strong>');
}

/* create database ---------------------------------------------------------- */
echo 'Creating database <em>'.DB_NAME.'</em>: ';
try_sql_query('CREATE DATABASE IF NOT EXISTS '.DB_NAME);

// now enable default error reporting
error_reporting(E_ALL ^ E_NOTICE);

/* create records table ----------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_RECORDS.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_RECORDS.'` (';
$sql .= '`id`           BIGINT        unsigned  NOT NULL auto_increment, ';     // log id
$sql .= '`client_id`    VARCHAR(20)             NOT NULL, ';                    // client id
$sql .= '`cache_id`     BIGINT        unsigned  NOT NULL, ';                    // cache id
$sql .= '`domain_id`    SMALLINT      unsigned  NOT NULL, ';                    // up to 65535 domains
$sql .= '`os_id`        TINYINT       unsigned  NOT NULL, ';                    // client OS
$sql .= '`browser_id`   TINYINT       unsigned  NOT NULL, ';                    // client browser (255 different browsers can be tracked)
$sql .= '`browser_ver`  FLOAT(3,1)    unsigned  NOT NULL, ';                    // client browser version
$sql .= '`user_agent`   VARCHAR(255)            NOT NULL, ';                    // full user agent string
$sql .= '`ftu`          TINYINT(1)              NOT NULL, ';                    // first time visitor
$sql .= '`ip`           VARCHAR(15)             NOT NULL, ';                    // user IP
$sql .= '`scr_width`    SMALLINT      unsigned  NOT NULL, ';                    // client screen resolution
$sql .= '`scr_height`   SMALLINT      unsigned  NOT NULL, ';     
$sql .= '`vp_width`     SMALLINT      unsigned  NOT NULL, ';                    // client viewport size
$sql .= '`vp_height`    SMALLINT      unsigned  NOT NULL, ';
$sql .= '`sess_date`    TIMESTAMP     default   CURRENT_TIMESTAMP, ';           // session timestamp
$sql .= '`sess_time`    FLOAT(7,2)    unsigned  NOT NULL, ';                    // tracking session time
$sql .= '`fps`          TINYINT       unsigned  NOT NULL, ';                    // registration accuracy
$sql .= '`coords_x`     MEDIUMTEXT              NOT NULL, ';                    // mouse coordinates (max 16777215 chars in one mouse trail)
$sql .= '`coords_y`     MEDIUMTEXT              NOT NULL, ';  
$sql .= '`clicks`       MEDIUMTEXT              NOT NULL, ';                    // mouse clicks
$sql .= '`hovered`      LONGTEXT                NOT NULL, ';                    // most hovered widgets (max 4294967298 chars)
$sql .= '`clicked`      LONGTEXT                NOT NULL, ';                    // ...and clicked
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);


/* create cache table ------------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_CACHE.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_CACHE.'` (';
$sql .= '`id`           BIGINT        unsigned  NOT NULL auto_increment, ';     // cache log id
$sql .= '`file`         VARCHAR(255)            NOT NULL, ';                    // cache log file name
$sql .= '`url`          TEXT                    NOT NULL, ';                    // tracked page url (http://www.boutell.com/newfaq/misc/urllength.html)
$sql .= '`layout`       ENUM("left", "center", "right", "liquid") NOT NULL DEFAULT "liquid", ';
$sql .= '`title`        VARCHAR(255)            NOT NULL, ';                    // tracked page title
$sql .= '`saved`        DATETIME                NOT NULL, ';                    // tracked page title
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);


/* create domains table ----------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_DOMAINS.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_DOMAINS.'` (';
$sql .= '`id`           SMALLINT      unsigned  NOT NULL auto_increment, ';     // domain id
$sql .= '`domain`       VARCHAR(255)            NOT NULL, ';                    // domain name
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);


/* create annotations table ------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_HYPERNOTES.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_HYPERNOTES.'` (';
$sql .= '`record_id`    BIGINT        unsigned  NOT NULL, ';                    // log id
$sql .= '`cuepoint`     CHAR(5)                 NOT NULL, ';                    // time position (SMPTE: ##:##)
$sql .= '`user_id`      TINYINT                 NOT NULL, ';                    // owner
$sql .= '`hypernote`    MEDIUMTEXT              NOT NULL, ';                    // html contents
$sql .= 'UNIQUE KEY `rcu` (`record_id`,`cuepoint`,`user_id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);


/* create OS table ---------------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_OS.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_OS.'` (';
$sql .= '`id`           TINYINT        unsigned  NOT NULL auto_increment, ';    // OS id
$sql .= '`name`         VARCHAR(20)              NOT NULL, ';                   // OS name
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);


/* create browsers table ---------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_BROWSERS.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_BROWSERS.'` (';
$sql .= '`id`           TINYINT        unsigned  NOT NULL auto_increment, ';    // browser id
$sql .= '`name`         VARCHAR(128)             NOT NULL, ';                   // browser name
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);


/* create users table ------------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_USERS.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_USERS.'` (';
$sql .= '`id`           TINYINT       unsigned  NOT NULL auto_increment, ';     // user id
$sql .= '`role_id`      TINYINT       unsigned  NOT NULL, ';                    // role id
$sql .= '`login`        VARCHAR(60)             NOT NULL, ';                    // user login
$sql .= '`pass`         VARCHAR(60)             NOT NULL, ';                    // user pass
$sql .= '`name`         VARCHAR(200)            NOT NULL, ';                    // user name
$sql .= '`email`        VARCHAR(100)            NOT NULL, ';                    // email
$sql .= '`website`      VARCHAR(100)            NULL,     ';                    // url
$sql .= '`registered`   DATETIME                NOT NULL, ';                    // registered date
$sql .= '`last_access`  TIMESTAMP     default   CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, '; // last login
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);

// create default admin password. Will be displayed at the end of installation
$ADMINPASS = generate_password();
// insert admin user
if (!db_select(TBL_PREFIX.TBL_USERS, "id", "login = 'root'")) {
  $sql  = "INSERT INTO ".TBL_PREFIX.TBL_USERS." (role_id, login, pass, name, email, registered)";
  $sql .= " VALUES (1, 'root', MD5('".$ADMINPASS."'), 'System Administrator', '".$_POST['email']."', NOW())";
  db_query($sql);
}


/* create roles table ------------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_ROLES.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_ROLES.'` (';
$sql .= '`id`           TINYINT       unsigned  NOT NULL auto_increment, ';     // role id (max. 255 roles)
$sql .= '`name`         VARCHAR(100)            NOT NULL, ';                    // role title
$sql .= '`description`  VARCHAR(255)            NOT NULL, ';                    // role description
$sql .= '`ext_allowed`  VARCHAR(255)            NOT NULL, ';                    // allowed extensions for this role
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);

// insert admin role
if (!db_select(TBL_PREFIX.TBL_ROLES, "id", "name = 'admin'")) {
  $sql  = "INSERT INTO ".TBL_PREFIX.TBL_ROLES." (name, description)";
  $sql .= " VALUES ('admin', 'sysadmin users group')";
  db_query($sql);
}

/* create extensions table -------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_EXTS.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_EXTS.'` (';
$sql .= '`id`         TINYINT       unsigned   NOT NULL auto_increment, ';      // ext (max. 255 modules)
$sql .= '`dir`        VARCHAR(20)              NOT NULL, ';                     // ext dir
$sql .= '`priority`   TINYINT       unsigned   NOT NULL, ';                     // stack order (0 means no order -> alphabetical sorting)
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);

// insert default extensions
$exts = ext_available();
foreach ($exts as $dir) 
{

  if (!db_select(TBL_PREFIX.TBL_EXTS, "id", "dir = '".$dir."'")) {
    $sql  = "INSERT INTO ".TBL_PREFIX.TBL_EXTS." (dir) VALUES ('".$dir."')";
    db_query($sql);
  }
}


/* create CMS options table ------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_CMS.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_CMS.'` (';
$sql .= '`id`           TINYINT       unsigned  NOT NULL auto_increment, ';     // option id
$sql .= '`type`         TINYINT                 NOT NULL, ';                    // option type (0:input,1:checkbox,etc.)
$sql .= '`name`         VARCHAR(100)            NOT NULL, ';                    // option name 
$sql .= '`value`        VARCHAR(255)            NOT NULL, ';                    // option value
$sql .= '`description`  TEXT                    NOT NULL, ';                    // option description
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);

/* insert default options --------------------------------------------------- */
$opts = array(
                // admin options
                array(CMS_TYPE,   "recordsPerTable",    20, "Number of records to show on each tracking table. This will be the default value, and it can be overriden on the <em>Admin logs</em> section."),
                array(CMS_TYPE,   "cacheDays",          60, "Cache (in days) for HTML logs. If the requested page was not modified in this amount of time, the system will use a cached copy. Leaving it blank or setting it to <code>0</code> means that no logs will be cached: each visit will generate one HTML log."),
                array(CMS_TYPE,   "maxSampleSize",      0,  "Number of logs to replay/analyze simultaneously (0 means no limit). If your database has a lot of records for the same URL, you can take into account only a small subset of logs."),
                // disabled by default
                array(CMS_CHOICE, "mergeCacheUrl",      0,  "Merge all logs that have the same URL. Useful if cache is disabled and one wants to group records by page ID."),
                array(CMS_CHOICE, "fetchOldUrl",        0,  "Tries to fetch a URL that could not be cached or that was deleted from cache."),
                array(CMS_CHOICE, "refreshOnResize",    0,  "Reload visualization page on resizing the browser window."),
                array(CMS_CHOICE, "displayWidgetInfo",  0,  "Display hover and click frequency for each interacted DOM element."),
                array(CMS_CHOICE, "displayGoogleMap",   0,  "If you typed a valid Google Maps key on your <em>config.php</em> file, the client location will be shown on a map when analyzing the logs."),
                array(CMS_CHOICE, "displayAvgTrack",    0,  "Display average mouse trail when visualizing simultaneous users."),
                array(CMS_CHOICE, "enableDebugging",    0,  "Turn on PHP strict mode and work with JS source files instead of minimized ones.")
             );
foreach ($opts as $arrValue) 
{
  if (!db_select(TBL_PREFIX.TBL_CMS, "id", "name = '".$arrValue[1]."'")) {
    $sql  = "INSERT INTO ".TBL_PREFIX.TBL_CMS." (type,name,value,description)";
    $sql .= " VALUES ('".$arrValue[0]."','".$arrValue[1]."','".$arrValue[2]."','".$arrValue[3]."')";
    db_query($sql);
  }
}


/* create JS options table ------------------------------------------------- */
echo 'Creating table <em>'.TBL_PREFIX.TBL_JSOPT.'</em>: ';

$sql  = 'CREATE TABLE IF NOT EXISTS `'.TBL_PREFIX.TBL_JSOPT.'` (';
$sql .= '`id`           TINYINT       unsigned  NOT NULL auto_increment, ';     // option id
$sql .= '`type`         TINYINT                 NOT NULL, ';                    // option type (0:input,1:checkbox,etc.)
$sql .= '`name`         VARCHAR(100)            NOT NULL, ';                    // option name 
$sql .= '`value`        VARCHAR(255)            NOT NULL, ';                    // option value
$sql .= '`description`  TEXT                    NOT NULL, ';                    // option description
$sql .= 'PRIMARY KEY (`id`) ';
$sql .= ') DEFAULT CHARSET utf8';

try_sql_query($sql);

/* default options ---------------------------------------------------------- */
$opts = array(
                // JS tracking colors
                array(CMS_TYPE, "entryPt", "99FF66", "Color for the mouse entry coordinate."),
                array(CMS_TYPE, "exitPt",  "FF6666", "Color for the mouse exit coordinate."),
                array(CMS_TYPE, "regPt",   "FF00FF", "Registration points color. Each registration point can give you a visual idea of the tracking accuracy."),   
                array(CMS_TYPE, "regLn",   "00CCCC", "Registration lines color. Used to draw the mouse path."),
                array(CMS_TYPE, "click",   "FF0000", "Mouse clicks color. One of the most relevant features to measure the implicit user interest in a page."),
                array(CMS_TYPE, "dDrop",   "AABBCC", "Drag and drop color. Mouse clicks should be distinguished from drag and drop operations (such as selecting some text, for example)."),
                array(CMS_TYPE, "varCir",  "FF9999", "Time-depending circles color. Each circle represents the amount of time that there is no mouse movement (the user is not using the mouse)."),
                array(CMS_TYPE, "cenPt",   "DDDDDD", "Centroid color. The centroid is the geometric center of the mouse path."),
                array(CMS_TYPE, "clust",   "0000FF", "Clusters color. The k-means algorithm assigns each registration point to the cluster whose center is nearest."),
                array(CMS_TYPE, "bgColor", "000000", "Background layer color. Self explanatory ;)"),
                // more JS options
                array(CMS_CHOICE, "bgLayer",        1, "Draw a semi-transparent background layer on bottom."),
                array(CMS_CHOICE, "realTime",       1, "You can replay the mouse path in real time or as a static overlayed image."),
                array(CMS_CHOICE, "dirVect",        0, "When replaying in <em>static</em> mode, it could be useful to display the path direction vector."),
                array(CMS_CHOICE, "loadNextTrail",  0, "Load more trails automatically (if available) for the current tracked user.")
             );

// insert default options
foreach ($opts as $arrValue) 
{
  if (!db_select(TBL_PREFIX.TBL_CMS, "id", "name = '".$arrValue[1]."'")) {
    $sql  = "INSERT INTO ".TBL_PREFIX.TBL_JSOPT." (type,name,value,description)";
    $sql .= " VALUES ('".$arrValue[0]."','".$arrValue[1]."','".$arrValue[2]."','".$arrValue[3]."')";
    db_query($sql);
  }
}

/* set permissions ---------------------------------------------------------- */
if (!is_writeable(CACHE_DIR)) {
  echo 'Settings permissions to <em>'.CACHE_DIR.'</em> dir: ';
  $perms = substr(decoct( fileperms(CACHE_DIR) ), 2);
  if ($perms != "775" && !chmod(CACHE_DIR, 0775)) 
  {
    echo '<strong class="ko">Failed.</strong> The directory '.CACHE_DIR.' must be writeable. Please verify it!
          <strong>You might have to set cache dir permissions manually.</strong><br />';
  } else {
    echo '<strong class="ok">Ok.</strong><br />'; 
  }
}

/* end ---------------------------------------------------------------------- */
?>

<h2>That's all! Your server is ready to gather data.</h2>

<p>
  Your admin login is <strong>root</strong>, with password <strong><?=$ADMINPASS?></strong>
  <br />
  Once logged in, you can change it on the <em>users</em> section.
</p>

<p><a href="../">Go to admin page</a>.</p>
