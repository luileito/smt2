/** 
 * (smt)2 simple mouse tracking - record mode (smt-record-2.0.0.js)
 * Copyleft (cc) 2006-2009 Luis Leiva
 * Release date: September 12th 2009 
 * http://smt.speedzinemedia.com
 * @class smt2-record
 * @requires smt2-aux Auxiliary (smt)2 functions  
 * @version 2.0.0
 * @author Luis Leiva 
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and GPL (GPL-LICENSE.txt) licenses. 
 * @see smtAuxFn
 */

//var smtRecord = {}; // used for documenting code only, as this script is unobtrusively self-encapsulated
(function(){
  /** 
   * (smt)2 default recording options.
   * For developers: This Object can be overriden at runtime by calling the function {@link smtAuxFn#overrideTrackingOptions}.
   * For users: create the Object "smtRecordingOptions" and assign your custom values for the following properties.   
   * @see smtAuxFn#overrideTrackingOptions
   */
  var smtOpt = {
    /**
     * Tracking frequency, in frames per second.
     * @type number           
     */
    fps: 24,
    /**
     * Tracking timeout, in seconds. 
     * If timeout is reached, mouse activity is not recorded.
     * @type number     
     */
    recTime: 120,
    /**
     * Path to (smt)2 installation.
     * The record script will try to find automatically the installation path, 
     * but if you used other name (i.e: http://my.server/test) 
     * you must type it explicitly here. Please do NOT place a final slash (/).      
     * Valid path names that will be recognized automatically are: "http://domain.name/smt2", "/my/smt2dir", "/server/t/tracksmt2" ... and so on.
     * @type string     
     */
    smtPath: "/smt2",
    /**
     * You may choose to advice users (or not) that their mouse activity is going to be logged.
     * @type boolean      
     */
    warn: false,
    /**
     * Text to display when advising users (if warn: true).
     * @type string     
     */
    warnText: "We'd like to track your mouse activity\nin order to improve this website's usability.\nDo you agree?",
    /**
     * Cookies lifetime (in days) to reset both first time users and agreed-to-track visitors.
     * @type int     
     */
    cookieDays: 365,
    /**
     * Custom initialization routines. Here you can add more functionalities on init.
     * @type function     
     */
    initFn: null,
    /** 
     * Random user selection: if true, (smt)2 is not initialized.
     * Setting it to false (or 0) means that all the population will be tracked.
     * You should use random sampling for accurate statistical analysis.     
     * @type int           
     */
    disabled: 0 //Math.round(Math.random()) // <-- random sampling
  };
  
    
  /* do not edit below this line -------------------------------------------- */
  
  
  // get auxiliar functions
  var aux = window.smtAuxFn;
  if (typeof aux === "undefined") { throw("auxiliar (smt)2 functions not found"); }

  // get user options, if set
  var custom = window.smtRecordingOptions;
  if (typeof custom !== "undefined") { aux.overrideTrackingOptions(smtOpt, custom); }
  
  // try to auto-detect smt2 installation path                  
  var scripts = document.getElementsByTagName('script');
  for (var i = 0, s = scripts.length; i < s; ++i) {
    var filename = scripts[i].src;
    if ( /smt-record/i.test(filename) ) 
    {
      var paths = filename.split("/");
      var pos = aux.array.indexOf(paths, "smt2");
      if (pos && smtOpt.smtPath === null) {
        smtOpt.smtPath = paths.slice(0, pos + 1).join("/");
      }
    }
  }
  
  /* (smt)2 record system definition ---------------------------------------- */
  
  
  /** 
   * (smt)2 recording object.
   * This Object is private. Methods are cited but not documented.
   */
  var smtRec = {
    i: 0,                                         // counter var
    mouse:    { x:0, y:0 },                       // mouse position
    viewport: { width:0, height:0 },              // data normalization
    discrepance: { x:1, y:1 },                    // discrepance ratios
    coords:   { x:[], y:[] },                     // saved position coords
    clicks:   { x:[], y:[] },                     // saved click coords
    elem:     { hovered:[], clicked:[] },         // clicked and hovered elements
    url:      null,                               // document URL
    rec:      null,                               // recording identifier
    userId:   null,                               // user session identifier
    append:   null,                               // append data identifier
    paused:   false,                              // check active window
    clicked:  false,                              // no mouse click yet
    timeout:  smtOpt.fps * smtOpt.recTime,        // tracking timeout
    xmlhttp:  aux.createXMLHTTPObject(),          // create XHR object
    firstTimeUser:  1,                            // assume a first time user initially
    
    /** 
     * Pauses recording. 
     * The mouse activity is tracked only when the current window has focus. 
     */
    pauseRecording: function() 
    {
      smtRec.paused = true;
    },
    /** 
     * Resumes recording. The current window gain focus.
     */
    resumeRecording: function() 
    {
      smtRec.paused = false;
    },
    /** 
     * Normalizes data on window resizing.
     */
    normalizeData: function() 
    { 
      var vp = aux.getWindowSize();
      // compute new discrepace ratio
      smtRec.discrepance.x = aux.roundTo(vp.width / smtRec.viewport.width);
      smtRec.discrepance.y = aux.roundTo(vp.height / smtRec.viewport.height);
    },
    /** 
     * Cross-browser way to register the mouse position.
     * @autor Peter-Paul Koch (quirksmode.org)
     */
    getMousePos: function(e) 
    { 
      if (!e) { e = window.event; }
    	if (e.pageX || e.pageY) {
    		smtRec.mouse.x = e.pageX;
    		smtRec.mouse.y = e.pageY;
    	}	else if (e.clientX || e.clientY) {
    		smtRec.mouse.x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
    		smtRec.mouse.y = e.clientY + document.body.scrollTop  + document.documentElement.scrollTop;
    	}
    },
    /** 
     * Registers single clicks and drag and drop operations.
     */
    setClick: function() 
    {
      smtRec.clicked = true;
    },
    /** 
     * User releases the mouse.
     */
    releaseClick: function() 
    {
      smtRec.clicked = false; 
    },
    /** 
     * (smt)2 recording loop.
     * Tracks mouse coords when they're inside the client window, 
     * so zero and null values are not taken into account.     
     */
    recMouse: function() 
    {
      // track mouse only if window is active (has focus)
      if (smtRec.paused) { return; }
      // get mouse coords until timeout is reached 
      if (smtRec.i < smtRec.timeout) {
        // get normalized coords
        var x = Math.round(smtRec.discrepance.x * smtRec.mouse.x);
        var y = Math.round(smtRec.discrepance.y * smtRec.mouse.y);
        if (x && y) {
          smtRec.coords.x.push(x);
          smtRec.coords.y.push(y);
          // track also mouse clicks
          if (!smtRec.clicked) {
            smtRec.clicks.x.push(null); 
            smtRec.clicks.y.push(null);
          } else { 
            smtRec.clicks.x.push(x); 
            smtRec.clicks.y.push(y); 
          }
        }
    	} else {
    	  // timeout reached
    	  clearInterval(smtRec.rec);
    	  clearInterval(smtRec.append);
    	}
    	// next step
    	++smtRec.i;
    },
    /** 
     * Sends data in background via an XHR object (asynchronous request).
     * This function starts the tracking session.
     */   
    initMouseData: function() 
    { 
      smtRec.updateViewPort();
      // prepare data
      var data  = "url="        + smtRec.url;
          data += "&urltitle="  + document.title;
          data += "&cookies="   + document.cookie;
          data += "&bname="     + aux.browserDetect.browser;
          data += "&bversion="  + aux.browserDetect.version;
          data += "&bos="       + aux.browserDetect.OS;
          data += "&bua="       + navigator.userAgent;
          data += "&screenw="   + screen.width;
          data += "&screenh="   + screen.height;
          data += "&vpw="       + smtRec.viewport.width;
          data += "&vph="       + smtRec.viewport.height;
          data += "&time="      + aux.roundTo(smtRec.i/smtOpt.fps);
          data += "&fps="       + smtOpt.fps;
          data += "&ftu="       + smtRec.firstTimeUser;
          data += "&xcoords="   + smtRec.coords.x;
          data += "&ycoords="   + smtRec.coords.y;
          data += "&xclicks="   + smtRec.clicks.x;
          data += "&yclicks="   + smtRec.clicks.y;
          data += "&elhovered=" + smtRec.elem.hovered;
          data += "&elclicked=" + smtRec.elem.clicked;
      // send request
      aux.sendAjaxRequest({
        url:       smtOpt.smtPath + "/core/store.php", 
        callback:  smtRec.setUserId, 
        postdata:  data, 
        xmlhttp:   smtRec.xmlhttp
      });
      // clean
      smtRec.clearMouseData();
    },
    /**
     * Sets the user ID.
     * @return void
     * @param {string} response  XHR response text
     */
    setUserId: function(response) 
    {
      smtRec.userId = response;
      if (smtRec.userId > 0) {
        // once the session started, append mouse data
        smtRec.append = setInterval(smtRec.appendMouseData, 2000);
      }
    },
    /** 
     * Sends data (POST) in asynchronously mode via an XHR object.
     * This appends the mouse data to the current tracking session.
     * If user Id is not set, mouse data are queued.     
     */   
    appendMouseData: function() 
    {
      if (!smtRec.rec || smtRec.paused) { return false; }
      // prepare data
      var data  = "uid="        + smtRec.userId;
          data += "&time="      + aux.roundTo(smtRec.i/smtOpt.fps);
          data += "&xcoords="   + smtRec.coords.x;
          data += "&ycoords="   + smtRec.coords.y;
          data += "&xclicks="   + smtRec.clicks.x;
          data += "&yclicks="   + smtRec.clicks.y;
          data += "&elhovered=" + smtRec.elem.hovered;
          data += "&elclicked=" + smtRec.elem.clicked;
      // send request
      aux.sendAjaxRequest({
        url:       smtOpt.smtPath + "/core/append.php", 
        postdata:  data,
        xmlhttp:   smtRec.xmlhttp
      });
      // clean
      smtRec.clearMouseData();
    },
    /** 
     * Clears mouse data from queue.        
     */
    clearMouseData: function()
    {
      smtRec.coords.x = [];
      smtRec.coords.y = [];
      smtRec.clicks.x = [];
      smtRec.clicks.y = [];
      smtRec.elem.hovered = [];
      smtRec.elem.clicked = [];
    },
    /** 
     * Finds hovered or clicked DOM element.     
     */
    findElement: function(e)
    {
      if (!e) { e = window.event; }
      // bind function to widget tracking object
      aux.widget.findDOMElement(e, function(name){
        if (e.type == "mousedown") {
          smtRec.elem.clicked.push(name);
        } else if (e.type == "mousemove") {
          smtRec.elem.hovered.push(name);
        }
      });
    },
    /** 
     * Updates viewport size.     
     */
    updateViewPort: function() 
    {
      var vp = aux.getWindowSize();
      smtRec.viewport.width = vp.width;
      smtRec.viewport.height = vp.height;
    },
    /** 
     * System initialization.
     * Assigns events and performs other initialization routines.     
     */
    init: function() 
    {
      // compute the viewport here because of IE
      smtRec.updateViewPort();
      // get this location BEFORE making the AJAX request
      smtRec.url  = window.location.href;
      // set main function: the (smt)2 recording interval
      var interval = Math.round(1000/smtOpt.fps);
      smtRec.rec   = setInterval(smtRec.recMouse, interval);
      // allow mouse tracking over Flash animations
      aux.allowTrackingOnFlashObjects();
      
      // add unobtrusive events
      aux.addEvent(document, "mousemove", smtRec.getMousePos);        // get mouse coords
      aux.addEvent(document, "mousedown", smtRec.setClick);           // mouse is clicked        
      aux.addEvent(document, "mouseup",   smtRec.releaseClick);       // mouse is released
      aux.addEvent(window,   "resize",    smtRec.normalizeData);      // make easy data interpretation
      // only record mouse when window is active
      if (document.attachEvent) {
        // see http://odondo.wordpress.com/2007/08/28/javascript-and-cross-browser-window-focus/
        aux.addEvent(document.body, "focusout", smtRec.pauseRecording);
        aux.addEvent(document.body, "focusin",  smtRec.continueRecording);
      } else {
        aux.addEvent(window,  "blur",  smtRec.pauseRecording);
        aux.addEvent(window,  "focus", smtRec.continueRecording);
      }
      // track also at the widget level (fine-grained mouse tracking)
      aux.addEvent(document, "mousedown", smtRec.findElement);        // elements clicked
      aux.addEvent(document, "mousemove", smtRec.findElement);        // elements hovered
      // flush mouse data when tracking ends
      if (typeof window.onbeforeunload == 'function') {
        // user closes the browser window
        aux.addEvent(window, "beforeunload", smtRec.appendMouseData);
      } else {
        // page is unloaded (for old browsers)
        aux.addEvent(window, "unload", smtRec.appendMouseData);
      }
      // this is the fully-cross-browser method to store tracking data successfully
      setTimeout(smtRec.initMouseData, 2000);
      
      // call user-defined init function
      if (smtOpt.initFn) { smtOpt.initFn(); }
    }
  };

  /* (smt)2 record initialization ------------------------------------------- */
  
  // does user browse for the first time?
  var previousUser = aux.cookies.checkCookie('isFirstTimeUser');
  // do not skip first time users when current visit is not sampled (smt disabled)
  if (smtOpt.disabled && previousUser) { return; }
  // check if warning is enabled
  if (smtOpt.warn) {
    // did she agree for tracking before?
    var prevAgreed = aux.cookies.checkCookie('isAgreedToTrack');
    // if user is adviced, she must agree
    var agree = (prevAgreed) ? aux.cookies.getCookie('isAgreedToTrack') : window.confirm(smtOpt.warnText);
    if (!agree) {
      // will ask next day (instead of smtOpt.cookieDays value)
      aux.cookies.setCookie('isAgreedToTrack', 0, 1);
      return; 
    }
  }
  // store int numbers, not booleans
  smtRec.firstTimeUser = (!previousUser | 0); // yes, it's a bitwise operation
  aux.cookies.setCookie('isFirstTimeUser', smtRec.firstTimeUser, smtOpt.cookieDays);
  aux.cookies.setCookie('isAgreedToTrack', 1, smtOpt.cookieDays);
  // launch browser detection script (for statistical/debugging purposes)
  aux.browserDetect.init();
  
  // start recording on DOM loaded
  aux.onDOMload(smtRec.init);
  
})();