<?php
/**
 * This is the directory where you put the smt2 CMS.
 * You can use relative as well as full URLs like /smt2/ or http://myserver.name/smt2/
 */
define ('ABS_PATH', "http://localhost/smt2/"); // always put an ending slash (/)

// ----------------------------------------------------- MySQL database info ---

/** 
 * Your MySQL database name. 
 * If you did not create one, smt2 will do it for you.
 * If you cannot create *new* databases, write the name of your current database
 * and smt2 will store their tables there.
 */
define ('DB_NAME',     "smt");
/** 
 * Your MySQL username.
 * This user must have grants to SELECT, INSERT, UPDATE, and DELETE tables.
 */
define ('DB_USER',     "root");
/** 
 * Your MySQL password. 
 */
define ('DB_PASSWORD', "admin");
/** 
 * Your MySQL server. 
 * If port number ### were needed, use 'servername:###'. 
 */
define ('DB_HOST',     "localhost");
/** 
 * Prefix for creating smt2 tables.
 * That's really useful if you have only one database.
 */
define ('TBL_PREFIX',  "smt2_");

// ----------------------------------------------------------------- Add-ons ---

/** 
 * Your Google maps key for client localization. This one is for localhost.
 * If you put smt2 on your own production server, you should register (for free)
 * at http://code.google.com/apis/maps/signup.html
 */
define ('GG_KEY', "ABQIAAAAElGM1_G8Y0SLRJtsUmEeART2yXp_ZAY8_ufC3CFXhHIE1NvwkxTjJAIz5IfhLGJPdYN9-8jws6kgmQ");

// ------------------------------------------ (smt) functions - do not edit! ---
define ('BASE_PATH', dirname(__FILE__));
define ('INC_PATH', BASE_PATH.'/admin/');
require_once INC_PATH.'sys/functions.php';
?>
