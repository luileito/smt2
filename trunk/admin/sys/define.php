<?php
/** Installed (smt) version. */
define ('SMT_VERSION',   "2.0.0-beta1");
/** Path to (smt) admin system. This absolute URL is used to resolve links, redirections, an so on. */
define ('ADMIN_PATH',    ABS_PATH."admin/");
/** Path to core (smt) javascript functions. */
define ('JS_PATH',       ABS_PATH."core/js/");
/** Path to core (smt) flash functions. */
define ('SWF_PATH',      ABS_PATH."core/swf/");
/** (smt) record script. */
define ('SMT_RECORD',    JS_PATH."smt-record.min.js");
/** (smt) replay script. */
define ('SMT_REPLAY',    JS_PATH."smt-replay.min.js");
/** (smt) auxiliar functions. */
define ('SMT_AUX',       JS_PATH."smt-aux.min.js");
/** JavaScript graphics libary. */
define ('WZ_JSGRAPHICS', JS_PATH."wz_jsgraphics.min.js");
/** SWFObject libary. */
define ('SWFOBJECT',     ADMIN_PATH."js/swfobject.js");
/** HTML logs dir. Do not use absolute URLs because fopen wrappers could be disabled. */
define ('CACHE',         INC_PATH."cache/");
/** Table for storing (smt) records. */
define ('TBL_RECORDS',   "records");
/** Table for caching HTML logs. */
define ('TBL_CACHE',     "cache");
/** Table for storing browsers' name. */
define ('TBL_BROWSERS',  "browsers");
/** Table for storing operating system's name. */
define ('TBL_OS',        "os");
/** Table for registered users. */
define ('TBL_USERS',     "users");
/** Table to manage users' roles. */
define ('TBL_ROLES',     "roles");
/** Table for extension modules. */
define ('TBL_EXTS',      "exts");
/** Table for customize CMS options. */
define ('TBL_CMS',       "cms");
/** Table for customize JS replay options. */
define ('TBL_JSOPT',     "jsopt");
/** Form input type: User must enter some value (input text) */
define ('CMS_TYPE',      0);
/** Form input type: User must choose between 2 options (checkbox) */
define ('CMS_CHOICE',    1);
/** Form input type: User must choose between 3 or more options (radio button) */
define ('CMS_MULTIPLE',  2);
?>