# Introduction #

You may log in `http://<yourserver>/<smt2dir>/` as the superuser (username **root**) in order to have full access to all CMS features. That user is created when installing smt2 for the first time.

# Extensions overview #

The (smt)<sup>2.0</sup> CMS is composed by _extensions_ (aka _functional modules_ or simply _sections_). When new extensions are available, just upload them to the `/smt2/admin/ext/` dir and the system will install them automatically for your convenience -- You will be warned on the _Dashboard_ section.

## Dashboard ##

This is the admin base dir. Here you will check your database server status and new (smt) <sup>2</sup> releases.

## Customize ##

Here you can set the basic CMS options, change the extensions' order priority, and customize the visualization options.

## Admin logs ##

This is the most featured extension. Here you will manage all tracked visits (visualize, analyze and delete). You can sort the records table by client ID, date and session time. Additionally, you can filter and refine your saved logs.

## Roles ##

This extension is designed to give you the ability to control and assign what users can and cannot do in the CMS. The default role is named _admin_, and it is the role that the **root** user belongs to.

You can assign users to the _admin_ role, with almost wide access. However, the **root** user is the only account that can enjoy all CMS features (i.e: create, assign and delete roles).

Read more about roles and capabilities on the [roles wiki](http://code.google.com/p/smt2/wiki/roles).

## Users ##

On that section you can add new users, and assign them certain roles. Users with the _admin_ role can create new users, but they won't assign roles to them. Again, the **root** user is the only one that can do these kind of things (i.e: also update and delete all registered accounts).


# Creating custom extensions #

If you know how the CMS works, you can create your own extensions. Please read the PHP API documentation for better understanding. Since version 2.0.0 a extension called _documentation_ is provided (you know what you'll find there).

**Example**: This is a base script to begin writing an extension:
```
<?php
// config settings are required - set the relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone (optional but encouraged)
require SYS_DIR.'logincheck.php';
// include CMS base header
include INC_DIR.'header.php';


// ... your code goes here ...


// the script closes the HTML markup by including the common footer
include INC_DIR.'footer.php'; 
?>
```
You can save it as `index.php` under the dir `admin/ext/mynewscript/` and you're done!