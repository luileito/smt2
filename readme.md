## Please don't post comments below. If you find a bug use the issue tracker. If you need assistance drop me an email. ##


# Requirements #

**MySQL 5** database and **PHP 5** installed with the libraries **cURL** and **json**. These settings are very common on most web servers. If your server doesn't meet these requirements, you will be advised during installation.


# Download #

You can download all the project files from  [GoogleCode SVN](http://code.google.com/p/smt2/source/checkout).

Until 2011, downloads were also available as ZIP files. However it was cumbersome to keep them updated while fixing bugs and/or modifying the source code, so now you need an Subversion client to grab the code. Here is [a list of some common Subversion clients](http://en.wikipedia.org/wiki/Comparison_of_Subversion_clients).

This project contains the following items:
  * The mouse tracking files (production version), both for recording and visualization, ready for using on your webserver.
  * The sources ~~and API documentation~~. They are located in:
    1. `core/js/src`
    1. `core/swf/src`
    1. ~~`admin/ext/documentation`~~
  * The admin area (CMS), to manage logs and so on.

**Update**: the technical documentation files are around 4 MB, and they are not needed in production. Therefore, they were removed from trunk, to save thus disk space in a production server. These files will be available soon in another SVN branch.


# Installing #

The installation process is very similar to the well-known WordPress Content Management System (CMS), so the learning curve is supposed to be very smooth.

It is recommended to put the system in a directory named `/smt2` on your localhost or production server, although any other directory name is ok as long as you configure properly both `config.php` and the argument of `smt.record()` function. NOTE: If you put `config.php` in a directory name that contains the string "smt2", then it will be recognized automatically by the recording script.


## Steps ##

First of all, you must configure the basic stuff by editing the file `config.php`. Then, just upload the `smt2` folder to your Web server.

Assuming that you've uploaded the files to a server called `mysite.com`:

  1. Open your Web browser and navigate to the URL `http://mysite.com/smt2/admin/sys/install.php`.
  1. Enter your email and click the `Install` button. That email will be used to send you the root password.
  1. Once the installation process finishes, copy the generated password and log in (your username is **root**) to the CMS. You can change that password on the _users_ section.

### Upgrading ###

If you already have a previous 2.x version installed, you can overwrite such installation with the new files and then update your database info by navigating to the URL `http://mysite.com/smt2/admin/sys/upgrade.php`.


# Uninstalling #

Just go to the URL `http://mysite.com/smt2/admin/sys/uninstall.php` and follow the instructions.


# Including the recording script on your page(s) #

You must add the following line either to the HEAD or the BODY:
```
<script type="text/javascript" src="/smt2/core/js/smt2e.min.js"></script>
```

It actually abstracts dependencies and performs lazy loading to ensure that tracking will start as soon as everything is ready.

For version 2.1.0 and below, you must insert 2 lines instead:
```
<script type="text/javascript" src="/smt2/core/js/smt-aux.min.js"></script>  
<script type="text/javascript" src="/smt2/core/js/smt-record.min.js"></script>
```

Note that order is mandatory: aux functions first.


# Customizing the recording options #

## Current version (2.2.0, codename smt2e) ##

These are the **default** (smt)<sup>2</sup> recording options that you can customize:
```
  /**
   * Tracking frequency, in frames per second.
   * @type number           
   */
  fps: 24,
  /**
   * Maximum recording time (aka tracking timeout), in seconds. 
   * When the timeout is reached, mouse activity is not recorded.
   * If this value is set to 0, there is no timeout.
   * @type number     
   */
  recTime: 3600,
  /**
   * Interval to send data, in seconds
   * If timeout is reached, mouse activity is not recorded.
   * @type number     
   */
  postInterval: 30,
  /**
   * URL to local (smt)2 website, i.e., the site URL to track (with the smt*.js files).
   * If this property is empty, the system will detect it automatically.
   * @type string
   */
  trackingServer: "",
  /**
   * URL to remote (smt)2 server, i.e., the site URL where the logs will be stored, 
   * and (of course) the CMS is installed.
   * If this value is empty, data will be posted to trackingServer URL (recommended).
   * @deprecated in favor of the new 'Access-Control-Allow-Origin' HTTP header.
   * @type string
   */
  storageServer: "",
  /**
   * You may choose to advice users (or not) that their mouse activity is going to be logged.
   * Not doing so may be illegal in some countries.
   * @type boolean      
   */
  warn: false,
  /**
   * Text to display when advising users (if warn: true).
   * You can split lines in the confirm dialog by typing the char \n.
   * @type string
   */
  warnText: "We'd like to study your mouse activity." +"\n"+ "Do you agree?",
  /**
   * Cookies lifetime (in days) to reset both first time users and agreed-to-track visitors.
   * @type int     
   */
  cookieDays: 365,
  /** 
   * Main layout content diagramation; a.k.a 'how page content flows'. 
   * Values: 
   *  "left" (content is fixed and ragged left; e.g. http://smt.speedzinemedia.com), 
   *  "center" (content is fixed and centered; e.g. http://personales.upv.es/luileito/), 
   *  "right" (content is fixed and ragged right; e.g. ???), 
   *  "liquid" (adaptable, optionally centered (or not); default behavior of web pages).
   * @type string
   */
  layoutType: "liquid",
  /**
   * Recording can stop/resume on blur/focus to save space in your DB. 
   * Depending on your goals/experiment/etc., you may want to tweak this behavior.
   * @type boolean
   */
  contRecording: true,
  /**
   * Compress tracking data to lower bandwidth usage.
   * @type boolean
   */
  compress: true,
  /** 
   * Random user selection: if true, (smt)2 is not initialized.
   * Setting it to false (or 0) means that all the population will be tracked.
   * You should use random sampling for better statistical analysis:
   * disabled: Math.round(Math.random())
   * You can set your own sampling strategy; e.g. this one would track users only on Mondays:
   * disabled: (function(){ return (new Date().getDay() == 1); })()
   * @type int
   */
  disabled: 0
```

To override these defaults you do not need to edit manually the record script, just keep reading these instructions.

### Important new feature: cross-domain POSTs ###

In version 2.0.2 two variables were introduced to customize the recording script: `trackingServer` and `storageServer`. This way, one can record mouse activity in domains A, B and C, and post data to a domain Z. Thus a single Z server will store and manage logs/users/etc. for multiple domains.

To enable this for version 2.2.0 and above, just leave `storageServer` empty. Also, `trackingServer` can be any valid URL, not only relative to the domain of your tracking server.

```
  trackingServer: "http://myserver.com/smt2/",
  storageServer:  "",
```

For older versions (2.1.0 and below), type in `storageServer` the **absolute URL** of the server where both the CMS and the database are installed (following the previous naming convention, it would be server Z), and put the `core` dir in the tracking server (e.g., domains A, B or C). For the tracking server, just create an `smt2` dir an put inside the `core` folder only. For the remote server, upload the full `smt2` dir. In both cases, you'll need to edit `config.php` accordingly.

```
  trackingServer: "/smt2/",
  storageServer:  "http://www.othersite.com/smt2/",
```

Please notice that in previous 2.x versions neither `trackingServer` nor `storageServer` options are available. Even more, in versions prior to 2.0.2 you only could set the recording option `dirPath`, in order to specify the path where you put smt2 (although the system will try to detect it automatically).


## Invoking recording script in old versions (2.0.0 branch) ##

Create an object named `smtRecordingOptions` with the properties that you want to customize BEFORE the above mentioned [2 JavaScript lines](http://code.google.com/p/smt2/wiki/readme#Including_the_record_script_on_your_page(s)). Take this example page:
```
<html>
<head>
  <script type="text/javascript">
  var smtRecordingOptions = { 
   recTime: 300,
   disabled: Math.round(Math.random()),
   warn: true, 
   warnText: "We are going to record your mouse movements for a remote usability study."
  };
  </script>
  <script type="text/javascript" src="/smt2/core/js/smt-aux.min.js"></script>  
  <script type="text/javascript" src="/smt2/core/js/smt-record.min.js"></script>
</head>
<body>
Your page content goes here...
</body>
</html>
```

Here we are telling that the record script will live for 300 seconds, and users will be selected randomly -- It seems that people want to track all their visits by default, so random sampling was removed on version 2.0.0. Additionally, the system will advice users with a custom message (they must agree to get recorded their mouse activity).

Note that the last property have no ending comma.

## Invoking recording script in newer versions (2.0.1 and above) ##

You must call the `smt2.record()` method explicitly.
This is how to invoke it, based on the previous example:
```
<html>
<head>
  <script type="text/javascript" src="/smt2/core/js/smt-aux.min.js"></script>  
  <script type="text/javascript" src="/smt2/core/js/smt-record.min.js"></script>
  <script type="text/javascript">
  try {
    smt2.record({
      recTime: 300,
      disabled: Math.round(Math.random()),
      warn: true, 
      warnText: "We are going to record your mouse movements for a remote usability study."
    });
  } catch(err) {}
  </script>
</head>
<body>
Your page content goes here...
</body>
</html>
```

In this way, now there is more flexibility in customizing your own recording initialization.


## Invoking recording script in current version (2.2.0, codename smt2e) ##

Currently the process is much simpler, though you can follow the previous example as well.
```
<html>
<head>
  <script type="text/javascript" src="/smt2/core/js/smt2e.min.js"></script>  
  <script type="text/javascript">
  try {
    smt2.record({
      recTime: 300,
      disabled: Math.round(Math.random()),
      warn: true, 
      warnText: "We are going to record your mouse movements for a remote usability study."
    });
  } catch(err) {}
  </script>
</head>
<body>
Your page content goes here...
</body>
</html>
```


# A note about privacy #

It is up to the webmaster enabling the `warn` property on `smt-record.js` script; however in some countries anyone who employs this type of user tracking should always inform the user. Furthermore, tracking of user actions should only happen for a limited amount of time.

If you decide to warn users, a confirm dialog will prompt **before** the webpage is rendered to the browser. You can write your own code to display a fancy modal window (or DIV) instead, as [this webmaster](http://smt.speedzinemedia.com/comments.php#comment4976) did on his site. This is a good visual improvement, but unfortunately displaying a customized layered dialog instead of smt2's "ugly" javascript prompt will result in non-accurate users' metrics; e.g.: browsing time will be higher, mouse activity will be lower... and so on. Read below how to achieve this.

**If you are using old 2.0.0 versions**, a better approach would be start recording the mouse activity and notify in some DIV on the page that she is being recorded -- i.e.: by not involving any awaiting prompt/dialog. In this way, if the user don't want to be considered in the tracking study, she could disable the recording script by simply clicking on a link that removes this user from database.

**If you are using newer versions (2.0.1 and above)**, you can tweak the initialization, as now the tracking script is called explicitly via JavaScript. This allows you to implement the previously aforementioned "fancy modal window" approach.


# Recording issues? #

Please take into account this checklist before reporting a new bug about _empty log cache_ or something similar:

  * Server permissions. This may be the source of most of your problems. Concerning the _cache folder empty_ bug, take into account that the PHP process must have full read/write access in the `/admin/cache` dir. Setting dir permissions to 755 is enough (provided that PHP is the owner of that dir), although you can try wider access, such as 775 or even 777.
  * Registered smt users are never tracked (i.e., root and all the user accounts you create on the CMS). Try to access your site from another browser or computer.
  * No firewall or proxy is blocking connections. Just loose your firewall/proxy rules for smt2.
  * If you want to make cross-domain POSTs, you must have access to the filesystem of both domains, in order to upload the required files -- see [note](http://code.google.com/p/smt2/wiki/readme#Important_new_feature:_cross-domain_POSTs).


# Working with the gathered data #

All recorded user visits can be managed on the _admin logs_ section of the CMS. The system will use the SWF visualization tool, if Flash plugin is available. Otherwise the JavaScript visualization API will be used (which is actually deprecated).

Read more about the CMS on the [wiki page](http://code.google.com/p/smt2/wiki/CMS).