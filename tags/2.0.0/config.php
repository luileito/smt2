<?php
/**
 * This is the directory where you put (smt)2.
 * This path will be used to look for the core files, 
 * and may be detected automatically unless you decide to use a strange dir path
 * (i.e. /aqwa/mmkia/jd65sa/ -- silly example).
 * You can use also full URLs like http://myserver.name/smt2/
 */
define ('ABS_PATH', "/smt2/"); // always place here an ending slash (/)

// ----------------------------------------------------- MySQL database info ---

/** 
 * Your MySQL database name. 
 * If you did not create one, (smt)2 will do it for you. 
 * If you cannot create *new* databases, write the name of your current database
 * and (smt)2 will store their tables there.
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
 * Prefix for creating (smt)2 tables. 
 * That's really useful if you have only one database.
 */
define ('TBL_PREFIX',  "smt2_");

// ----------------------------------------------------------------- Add-ons ---

/** 
 * Internal encoding to handle cache logs creation.
 * This constant is only available if the function mb_convert_encoding exists.
 */
define ('LOG_ENCODING',  "UTF-8");

/** 
 * Your Google maps key. This one is for localhost. 
 * If you put (smt)2 on your own production server, you should register (for free) at
 * http://code.google.com/apis/maps/signup.html 
 */
define ('GG_KEY', "ABQIAAAAElGM1_G8Y0SLRJtsUmEeART2yXp_ZAY8_ufC3CFXhHIE1NvwkxTjJAIz5IfhLGJPdYN9-8jws6kgmQ");


// ------------------------------------ (smt) admin functions - do not edit! ---
define ('INC_PATH', dirname(__FILE__).'/admin/');
require_once(INC_PATH.'sys/functions.php');
?>