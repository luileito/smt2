/*! 
 * (smt)2 simple mouse tracking v2.1.0
 * Copyleft (cc) 2006-2012 Luis Leiva
 * http://smt2.googlecode.com & http://smt.speedzinemedia.com
 */
/**
 * (smt)2 simple mouse tracking - auxiliary functions (smt-aux.js)
 * Copyleft (cc) 2006-2012 Luis Leiva
 * Release date: March 23 2012
 * http://smt2.googlecode.com & http://smt.speedzinemedia.com
 * @class smt2-aux
 * @version 2.1.0
 * @author Luis Leiva
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and GPL (GPL-LICENSE.txt) licenses.
 */

var smt2fn = {
  /**
   * Overrides (smt) tracking options object with custom-provided options object
   * @return void
   * @param {object} smtOptionsObj
   * @param {object} customOptionsObj
   * @see <code>smtOpt</code> object either in <code>smtRecord</code> or <code>smtReplay</code> classes
   */
  overrideTrackingOptions: function(smtOptionsObj, customOptionsObj)
  {
    for (var prop in smtOptionsObj)
    {
      if (customOptionsObj.hasOwnProperty(prop) && customOptionsObj[prop] !== null) {
        smtOptionsObj[prop] = customOptionsObj[prop];
      }
    }
  },
  
  /**
   * Allows recording/replaying the mouse path over Flash objects.
   * A Flash movie may display above all the layers on the HTML apge,
   * regardless of the stacking order ("z-index") of those layers.
   * Using a WMODE value of "opaque" or "transparent" will prevent a Flash movie from playing in the topmost layer
   * and allow you to adjust the layering of the movie within other layers of the HTML document.
   * However, to avoid possible performance issues, it's best use the "opaque" mode.
   * Note: The WMODE parameter is supported only on some browser/Flash Player version combinations.
   * If the WMODE parameter is not supported, the Flash movie will always display on top.
   * @param {Object}  d   document object   
   * @return void
   */
  allowTrackingOnFlashObjects: function(d)
  {
    var obj = d.getElementsByTagName("object");
    for (var i = 0, t = obj.length; i < t; ++i) {
      var param = d.createElement("param");
      param.setAttribute("name", "wmode");
      param.setAttribute("value","opaque");
      obj[i].appendChild(param);
    }

    var embed = d.getElementsByTagName("embed");
    for (var j = 0, u = embed.length; j < u; ++j) {
      embed[j].setAttribute("wmode", "opaque");
      // recording on some browsers is tricky (replaying is ok, though)
      if (!/MSIE/i.test(navigator.userAgent)) {
        /* Some browsers sets the wmode correctly,
         * but once the SWF object is instantiated you can't update its properties.
         * So, replace the old Flash object with the new one ;)
         */
        var cloned = embed[j].cloneNode(true);
        embed[j].parentNode.replaceChild(cloned, embed[j]);
      }
    }
  },
     
  /**
   * Traces any kind of objects in the debug console (if available).
   * @return void
   */
  log: function()
  {
    // check if console is available
    if (!window.console || window.console.log) { return false; }
    // display messages in the console
    console.log.apply(console, arguments);
  },
  
  /**
   * Checks the DOM-ready initialization in modern browsers.
   * This method was introduced by Dean Edwards/Matthias Miller/John Resig (dean.edwards.name/outofhanwell.com/jquery.com)
   * and it is discussed in http://dean.edwards.name/weblog/2006/06/again/
   * It's 2009, and fortunately nowadays there are only 2 types of modern browsers: W3C standards and Internet Explorer.
   * @return void
   * @param {function} callback   the function to be called on DOM load
   */
  onDOMload: function(callback)
  {
    if (arguments.callee.done) { return; }
    arguments.callee.done = true;
    
    // Firefox, Opera, Webkit-based browsers (Chrome, Safari)...
    if (document.addEventListener) {
      document.addEventListener('DOMContentLoaded', callback, false);
    }
    // Internet Explorer ¬¬
    else if (document.attachEvent) {
      try {
        document.write("<scr"+"ipt id=__ie_onload defer=true src=//:><\/scr"+"ipt>");
        var script = document.getElementById("__ie_onload");
        script.onreadystatechange = function() {
          if (this.readyState === 'complete') { callback(); }
        };
      } catch(err) {}
    }
    else {
      // fallback: old browsers use the window.onload event
      this.addEvent(window, 'load', callback);
    }
  },
  
  /**
   * Reloads the current page.
   * This method is needed for the drawing APIs,
   * where all window and screen data should be re-computed (and stage size should be reset).
   * @deprecated because IE's infinite loop behaviour
   * @return void
   */
  reloadPage: function()
  {
    // do not not alter the the browser's history
    window.location.replace(window.location.href);
  },
  
  /**
   * Loads more mouse trails for the current user, if available.
   * @return void
   * @param {object}   smtData    The user's data object
   */
  loadNextMouseTrail: function(smtData)
  {
    if (typeof smtData.api === 'undefined') { smtData.api = "js"; }
    var currTrailPos = this.array.indexOf(smtData.trails, smtData.currtrail);
    // check
    if (currTrailPos < smtData.trails.length - 1) {
      var navigateTo = smtData.trailurl+'?id='+smtData.trails[currTrailPos + 1]+'&api='+smtData.api;
      if (smtData.autoload) {
        window.location.href = navigateTo;
      } else if (confirm("This user also browsed more pages.\nDo you want to replay the next log?")) {
        window.location.href = navigateTo;
      }
    } else {
      alert("There are no more browsed pages for this user.");
    }
  },
  
  /**
   * Gets the position of element on page.
   * @autor Peter-Paul Koch (quirksMode.org)
   * @return {object} offset position - object with 2 properties: left {integer}, and top {integer}
   */
  findPos: function(obj)
  {
    var curleft = curtop = 0;
    if (obj && obj.offsetParent) {
      do {
  			curleft += obj.offsetLeft;
  			curtop  += obj.offsetTop;
      } while (obj = obj.offsetParent);
    }
    
    return { x:curleft, y:curtop };
  },
  
  /**
   * Gets the CSS styles of a DOM element.
   * @return {string} window dimmensions - object with 2 properties: width {integer}, and height {integer}
   */
  getStyle: function (oElm, strCssRule)
  {
  	var strValue = "";
  	//strCssRule = strCssRule.toLowerCase();
  	if(document.defaultView && document.defaultView.getComputedStyle){
  		strValue = document.defaultView.getComputedStyle(oElm, "").getPropertyValue(strCssRule);
  	}
  	else if(oElm.currentStyle) {
  		strCssRule = strCssRule.replace(/\-(\w)/g, function (strMatch, p1){
  			return p1.toUpperCase();
  		});
  		strValue = oElm.currentStyle[strCssRule];
  	}
  	
  	return strValue;
  },

  /**
   * Gets the browser's window size (aka 'the viewport').
   * @return {object} window dimmensions - object with 2 properties: width {integer}, and height {integer}
   */
  getWindowSize: function()
  {
    var d = document;
    var w = (window.innerWidth) ? window.innerWidth
            : (d.documentElement && d.documentElement.clientWidth) ? d.documentElement.clientWidth
            : (d.body && d.body.clientWidth) ? d.body.clientWidth
            : 0;
    var h = (window.innerHeight) ? window.innerHeight
            : (d.documentElement && d.documentElement.clientHeight) ? d.documentElement.clientHeight
            : (d.body && d.body.clientHeight) ? d.body.clientHeight
            : 0;
    return { width: w, height: h };
  },
  
  /**
   * Gets the browser window's offsets.
   * @return {object} window offsets - object with 2 properties: x {integer}, and y {integer}
   */
  getWindowOffset: function()
  {
    var d = document;
    var xpos = (window.pageXOffset) ? window.pageXOffset
                : (d.documentElement && d.documentElement.scrollLeft) ? d.documentElement.scrollLeft
                : (d.body && d.body.scrollLeft) ? d.body.scrollLeft
                : 0;
    var ypos = (window.pageYOffset) ? window.pageYOffset
                : (d.documentElement && d.documentElement.scrollTop) ? d.documentElement.scrollTop
                : (d.body && d.body.scrollTop) ? d.body.scrollTop
                : 0;

    return { x: xpos, y: ypos };
  },
  
  /**
   * Gets the document's size.
   * @return {object} document dimensions - object with 2 properties: width {integer}, and height {integer}
   */
  getDocumentSize: function()
  {
    var d = document;
    var w = (window.innerWidth && window.scrollMaxX) ? window.innerWidth + window.scrollMaxX
            : (d.body && d.body.scrollWidth > d.body.offsetWidth) ? d.body.scrollWidth
            : (d.body && d.body.offsetWidth) ? d.body.offsetWidth
            : 0;
    var h = (window.innerHeight && window.scrollMaxY) ? window.innerHeight + window.scrollMaxY
            : (d.body && d.body.scrollHeight > d.body.offsetHeight) ? d.body.scrollHeight
            : (d.body && d.body.offsetHeight) ? d.body.offsetHeight
            : 0;

    return { width: w, height: h };
  },
  
  /**
   * Gets the max value from both window (viewport's size) and document's size.
   * @return {object} viewport dimensions - object with 2 properties: width {integer}, and height {integer}
   */
  getPageSize: function()
  {
    var win = this.getWindowSize(),
        doc = this.getDocumentSize();

    // find max values from this group
    var w = (doc.width < win.width) ? win.width : doc.width;
    var h = (doc.height < win.height) ? win.height : doc.height;

    return { width: w, height: h };
  },
  
  /**
   * Gets the max z-index level available on the page.
   * @return {integer}    z-index level
   * @param {object} e    DOM element (default: document)
   * @autor Jason J. Jaeger (greengeckodesign.com)
   */
  getNextHighestDepth: function(e)
  {
    var highestIndex = 0;
    var currentIndex = 0;
    var elementArray = [];
    // check all elements in page ...
    if (document.getElementsByTagName) {
      elementArray = document.getElementsByTagName('*');
    } else if (e.getElementsByTagName) {
      elementArray = e.getElementsByTagName('*');
    }
    // ... and iterate
    for (var i = 0, l = elementArray.length; i < l; ++i) {
      if (elementArray[i].currentStyle) {
        currentIndex = parseFloat(elementArray[i].currentStyle.zIndex);
      } else if (window.getComputedStyle) {
        currentIndex = parseFloat(document.defaultView.getComputedStyle(elementArray[i],null).getPropertyValue('z-index'));
      }
      if (currentIndex > highestIndex) { highestIndex = currentIndex; }
    }

    return highestIndex + 1;
  },
  
  /**
   * Gets the base path of the current window location.
   * @return {string}    path
   */
  getBaseURL: function()
  {
    var basepath = window.location.href;
    var dirs = basepath.split("/");
    delete dirs[ dirs.length - 1 ];

    return dirs.join("/");
  },
  
  /**
   * Gets the canonical URL of the current window location.
   * @return {string}    url
   */
  getCanonicalURL: function()
  {
    var basepath = window.location.href;
    var parts = basepath.split('?');
    
    return parts[0];
  },

  /**
   * Tests whether a set of URLs come from the same domain.
   * @return {boolean}
   */
  sameDomain: function() {
    var prevDomain, sameDomain = true;
    for (var i = 0, l = arguments.length; i < l; ++i) {
      if (i > 0) {
        sameDomain = (this.getDomain(prevDomain) == this.getDomain(arguments[i]));
      }
      prevDomain = arguments[i];
    }
    
    return sameDomain;
  },
  /**
   * Gets the domain of a given URL.
   * @return {string}
   */
  getDomain: function(url) {
    var l = document.createElement("a");
    l.href = url;
    
    return l.hostname;
  },
    
  /**
   * Checks that a URL ends with a slash; otherwise it will be appended at the end of the URL.
   * @return {string}    url
   */  
  ensureLastURLSlash: function(url) 
  {
    if (url.lastIndexOf("/") != url.length - 1) {
      url += "/";
    }

    return url;
  },
  
  /**
   * Adds event listeners unobtrusively.
   * @return void
   * @param {object}    obj   Object to add listener(s) to
   * @param {string}    type  Event type
   * @param {function}  fn    Function to execute
   * @autor John Resig (jquery.com)
   */
  addEvent: function(obj, type, fn)
  {
    if (obj.addEventListener) {
      obj.addEventListener(type, fn, false);
    } else if (obj.attachEvent)	{
      obj["e"+type+fn] = fn;
      obj[type+fn] = function(){ obj["e"+type+fn](window.event); };
      obj.attachEvent("on"+type, obj[type+fn]);
    }
  },
  
  /**
   * Rounds a number to a given digits accuracy.
   * @return {float}
   * @param {float}   number  input number
   * @param {integer} digits  precision digits
   */
  roundTo: function(number,digits)
  {
    if (!digits) { digits = 2; }
    var exp = 100; // faster, because smt2 precision is the same for all computations!
    /* in 'taliban mode' that would be ok:
     * <code>var exp = Math.pow(10,digits);</code>
     * or even this, avoiding the pow function:
     * <code>for (var i = 0, exp = 1; i < digits.length; ++i, exp *= 10) {}</code>
     */
    return Math.round(exp*number)/exp;
  },
  
  /**
   * Scrolls the browser window.
   * This function is quite useful for replaying the user trails comfortably ;)
   * @return void
   * @param {object}   obj    Config object
   * @config {integer} xpos   X coordinate
   * @config {integer} ypos   Y coordinate
   * @config {integer} width  Viewport width
   * @config {integer} height Viewport height
   */
  doScroll: function(obj)
  {
    var off = this.getWindowOffset();
    // center current mouse coords on the viewport
    var xto = Math.round(obj.xpos - obj.width) + obj.width/2;
    var yto = Math.round(obj.ypos - obj.height) + obj.height/2;
    window.scrollBy(xto - off.x, yto - off.y);
  },
  
  /**
   * Creates an XML/HTTP request to provide async communication with the server.
   * @return {object} XHR object
   * @autor Peter-Paul Koch (quirksMode.org)
   */
  createXMLHTTPObject: function()
  {
    var xmlhttp = false;
    // current AJAX flavours
    var XMLHttpFactories = [
      function(){ return new XMLHttpRequest(); },
      function(){ return new ActiveXObject("Msxml2.XMLHTTP"); },
      function(){ return new ActiveXObject("Msxml3.XMLHTTP"); },
      function(){ return new ActiveXObject("Microsoft.XMLHTTP"); }
    ];
    // check AJAX flavour
    for (var i = 0; i < XMLHttpFactories.length; ++i) {
      try {
        xmlhttp = XMLHttpFactories[i]();
      } catch(err) { continue; }
      break;
    }

    return xmlhttp;
  },
  
  /**
   * Makes an asynchronous XMLHTTP request (XHR) via GET or POST.
   * Inspired on Peter-Paul Koch's XMLHttpRequest function.
   * @return void
   * @param  {object}    setup      Request properties
   * @config {string}    url        Request URL
   * @config {boolean}  [async]     Send asynchronous request or not (default: true)
   * @config {string}   [postdata]  POST vars in the form "var1=name&var2=name..."
   * @config {function} [callback]  Response function   
   * @config {object}   [xmlhttp]   A previous XMLHTTP object can be reused
   */
  sendAjaxRequest: function(setup)
  {
    // create XHR object or reuse it
    var request = setup.xmlhttp ? setup.xmlhttp : this.createXMLHTTPObject();
    var cors = !this.sameDomain(window.location.href, setup.url);
    // CORS does work with XMLHttpRequest on modern browsers, except IE
    if (cors && window.XDomainRequest) {
      request = new XDomainRequest();
    }
    if (!request) { return; }

    var method = (setup.postdata) ? "POST" : "GET";
    var asynchronous = setup.hasOwnProperty('async') ? setup.async : true;    
    // start request
    request.open(method, setup.url, asynchronous);

    var iecors = window.XDomainRequest && (request instanceof XDomainRequest);
    // post requests must set the correct content type (not allowed under CORS + IE, though)
    if (setup.postdata && !iecors) {
      request.setRequestHeader('Content-Type', "application/x-www-form-urlencoded");
    }
    // add load listener
    if (iecors) {
      request.onload = function(){
        if (typeof setup.callback === 'function') {
          setup.callback(request.responseText);
        }
      };
    } else {
      // check for the 'complete' request state
      request.onreadystatechange = function(){
        if (request.readyState == 4 && typeof setup.callback === 'function') {
          setup.callback(request.responseText);
        }
      };
    }
    request.send(setup.postdata);
  },
  
  /**
   * Cookies management object.
   * This cookies object allows you to store and retrieve cookies easily.
   * Cookies can be picked up by any other web pages in the correct domain.
   * Cookies are set to expire after a certain length of time.
   */
  cookies: {
    /**
     * Stores a cookie variable.
     * @return void
     * @param {string} name
     * @param {mixed}  value
     * @param {string} expiredays (optional) default: no expire
     * @param {string} domainpath (optional) default: root domain
     */
    setCookie: function(name,value,expiredays,domainpath)
    {
      var path = domainpath || "/";
      var expires = "";
      if (expiredays) {
        var date = new Date();
        date.setTime(date.getTime() + (expiredays*24*60*60*1000)); // ms
        expires = "; expires=" + date.toGMTString();
      }
      document.cookie = name +"="+ escape(value) + expires +"; path=" + path;
    },
    /**
     * Retrieves a cookie variable.
     * @return {string}       cookie value, or false on failure
     * @param {string} name   cookie name
     */
    getCookie: function(name)
    {
      var cStart,cEnd;
      if (document.cookie.length > 0) {
        cStart = document.cookie.indexOf(name+"=");
        if (cStart != -1) {
          cStart = cStart + name.length + 1;
          cEnd   = document.cookie.indexOf(";", cStart);
          if (cEnd == -1) {
            cEnd = document.cookie.length;
          }

          return unescape(document.cookie.substring(cStart, cEnd));
        }
      }
      return false;
    },
    /**
     * Checks if a cookie exists.
     * @return {boolean}       true on success, or false on failure
     * @param {string}  name   cookie name
     */
    checkCookie: function(name)
    {
      var c = this.getCookie(name);
      return Boolean(c);
    },
    /**
     * Deletes a cookie.
     * @param {string}  name   cookie name
     */
    deleteCookie: function(name)
    {
      if (this.checkCookie(name)) {
        this.setCookie(name, null, -1);
      }
    }
  },
  
  /**
   * Core for tracking widgets.
   * The word "widget" stands for *any* DOM element on the page.
   * This snippet was developed years ago as 'DOM4CSS', and now lives in harmony with smt2.
   */
  widget: {
    /**
     * Concatenation token.
     */  
    chainer: ">",
    /**
     * Finds the first available element with an ID.
     * Traversing count starts from current element to node parents.
     * This function should be registered on mouse move/down events.
     * @return {object}            DOM node element
     * @param {object}   e         DOM event
     * @param {function} callback  response function
     */
    findDOMElement: function(e,callback)
    {
      if (!e) { e = window.event; }
      // find the element
      var t = e.target || e.srcElement;
      // defeat Safari bug
      if (t.nodeType == 3) { t = t.parentNode; }
      // if the element has no ID, travese the DOM in reverse (find its parents)
      var check = (t.id) ? this.getID(t) : this.getParents(t);
      if (check) {
        callback(check);
      }
    },
    /**
     * Gets the element's id.
     * @return {string}     DOM node ID
     * @param {object}  o   DOM node element
     */
    getID: function(o)
    {
      // save HTML and BODY nodes?
      //if (o.nodeName == 'HTML' || o.nodeName == 'BODY') { return false; }
      
      return o.nodeName + "#" + o.id;
    },
    /**
     * Gets the element's class.
     * If that element has more than one CSS class, return only the first class name found.
     * @return {string}     DOM node class
     * @param {object}  o   DOM node element
     */
    getClass: function(o)
    {
      // save HTML and BODY nodes?
      //if (o.nodeName == 'HTML' || o.nodeName == 'BODY') { return false; }
      
      // if the element has no class, return its node name only
      return (o.className) ? o.nodeName + "." + o.className.split(" ")[0] : o.nodeName;
    },
    /**
     * Gets all node parents until a node with ID is found.
     * @return {string}     CSS selector string (intended to use in other scripts).
     * @param {object}  o   DOM node element
     */
    getParents: function(o)
    {
      // store current node first
      var elem = (o.id) ? this.getID(o) : this.getClass(o);
      var list = (elem) ? [elem] : [];
      // get all parents until find an element with ID
      while (o.parentNode)
      {
        o = o.parentNode;
        // store only element nodes
        if (o.nodeType == 1)
        {
          if (o.id) {
            elem = this.getID(o);
            list.unshift(elem);
            // if parent has an ID, end finding
            return list.join(this.chainer);
          } else {
            elem = this.getClass(o);
            list.unshift(elem);
          }
          if (o == parent) {
            // #document reached
            return list.join(this.chainer);
          }
        }
      }
      return list.join(this.chainer);
    }
  },
  
  /**
   * Array methods -- without extending the Array prototype (best practice).
   * Note: These Array methods would only work for a completely 'dense' array.
   * If you're working with sparse arrays, then you should pre-process them:
   * <code>
   *   var narray = [];
   *   for (var i = 0; i < array.length; ++i) {
   *     if (array[i] != null) { narray.push(array[i]); }
   *   }
   *   // now narray is converted to a 'dense' array
   * </code>
   */
  array: {
    /**
     * Gets the max element in a numeric array.
     * @return {float}        array max value
     * @param {array} array
     * @author John Resig
     */
    max: function(arr)
    {
      return Math.max.apply(Math, arr);
    },
    /**
     * Gets the min element in a numeric array.
     * @return {float}        array min value
     * @param {array} array
     * @author John Resig
     */
    min: function(arr)
    {
      return Math.max.apply(Math, arr);
    },
    /**
     * Sums all elements in a numeric array.
     * @return {float}        array sum
     * @param {array} arr
     */
    sum: function(arr)
    {
      var s = 0;
      for (var i = 0, l = arr.length; i < l; ++i) {
        s += arr[i];
      }
      return s;
    },
    /**
     * Appends any supplied arguments to the end of the array, in the order given.
     * @return {integer}        new length of the array
     * @param {array} arr
     * @param {mixed} content   anything to push to array
     * @author Ash Searle (tweaked by Luis Leiva)
     * @link http://hexmen.com/blog/
     * @license http://creativecommons.org/licenses/by/2.5/
     */
    push: function(arr, content)
    {
      var args = Array.prototype.slice.call(content);
      var n = arr.length >>> 0;
      for (var i = 0; i < args.length; ++i) {
        arr[n] = args[i];
        n = n + 1 >>> 0;
      }
      arr.length = n;
      return n;
    },
    /**
     * Removes the last element of the array and returns it.
     * @return {mixed}        element removed (if any). Otherwise return <code>undefined</code>
     * @param {array} arr
     * @author Ash Searle
     * @link http://hexmen.com/blog/
     * @license http://creativecommons.org/licenses/by/2.5/
     */
    pop: function(arr)
    {
      var n = arr.length >>> 0, value;
      if (n) {
        value = arr[--n];
        delete arr[n];
      }
      arr.length = n;
      return value;
    },
    /**
     * Removes element(s) in desired position(s) from input array and returns the modified array.
     * @return {array}          modified array
     * @param {array}   arr
     * @param {integer} from    start index
     * @param {integer} to      end index
     * @author John Resig
     */
    remove: function(arr, from, to)
    {
      var rest = arr.slice((to || from) + 1 || arr.length);
      arr.length = (from < 0) ? arr.length + from : from;
      return array.push.apply(arr, rest);
		},
    /**
     * Gets the index of the first element that matches search value.
     * @return {integer}          index if found, or -1 otherwise
     * @param {array}   arr
     * @param {mixed}   search    element to search for
     * @param {integer} from      start index
     * @link http://snipplr.com/view/3355/arrayindexof/
     */
    indexOf: function(arr, search, from)
    {
      for (var i = (from || 0), total = arr.length; i < total; ++i) {
        // strict types should use === comparison
        if (arr[i] == search) {
          return i;
        }
      }
      return -1;
    }
  },
  
  LZW: {
  
    compress: function(uncompressed) 
    {
      var i, dictionary = {}, c, wc, w = "", result = [], dictSize = 256;
      for (i = 0; i < dictSize; i += 1) {
        dictionary[String.fromCharCode(i)] = i;
      }
      for (i = 0; i < uncompressed.length; ++i) {
        c = uncompressed.charAt(i);
        wc = w + c;
        if (dictionary[wc]) {
          w = wc;
        } else {
          result.push(dictionary[w]);
          dictionary[wc] = dictSize++;
          w = String(c);
        }
      }
      if (w !== "") {
        result.push(dictionary[w]);
      }
      for (i = 0; i < result.length; ++i) {
        result[i] = String.fromCharCode(result[i]);
      }
      
      return result.join("");
    },
 
    decompress: function(compressed) 
    {        
      compressed = compressed.split("");
      var i, dictionary = [], w, result, k, entry = "", dictSize = 256;
      for (i = 0; i < dictSize; i += 1) {
        dictionary[i] = String.fromCharCode(i);
      }
      w = compressed[0];
      result = w;
      for (i = 1; i < compressed.length; ++i) {
        k = compressed[i].charCodeAt(0);
        if (dictionary[k]) {
          entry = dictionary[k];
        } else {
          if (k === dictSize) {
            entry = w + w.charAt(0);
          } else {
            return null;
          }
        }
          result += entry;
          dictionary[dictSize++] = w + entry.charAt(0);
          w = entry;
      }
      
      return result;
    }
  }

};

// JS MD5 implementation by Joseph Myers
// http://www.myersdaily.org/joseph/javascript/md5-text.html
(function(){

  function md5cycle(x, k) {
    var a = x[0], b = x[1], c = x[2], d = x[3];

    a = ff(a, b, c, d, k[0], 7, -680876936);
    d = ff(d, a, b, c, k[1], 12, -389564586);
    c = ff(c, d, a, b, k[2], 17,  606105819);
    b = ff(b, c, d, a, k[3], 22, -1044525330);
    a = ff(a, b, c, d, k[4], 7, -176418897);
    d = ff(d, a, b, c, k[5], 12,  1200080426);
    c = ff(c, d, a, b, k[6], 17, -1473231341);
    b = ff(b, c, d, a, k[7], 22, -45705983);
    a = ff(a, b, c, d, k[8], 7,  1770035416);
    d = ff(d, a, b, c, k[9], 12, -1958414417);
    c = ff(c, d, a, b, k[10], 17, -42063);
    b = ff(b, c, d, a, k[11], 22, -1990404162);
    a = ff(a, b, c, d, k[12], 7,  1804603682);
    d = ff(d, a, b, c, k[13], 12, -40341101);
    c = ff(c, d, a, b, k[14], 17, -1502002290);
    b = ff(b, c, d, a, k[15], 22,  1236535329);

    a = gg(a, b, c, d, k[1], 5, -165796510);
    d = gg(d, a, b, c, k[6], 9, -1069501632);
    c = gg(c, d, a, b, k[11], 14,  643717713);
    b = gg(b, c, d, a, k[0], 20, -373897302);
    a = gg(a, b, c, d, k[5], 5, -701558691);
    d = gg(d, a, b, c, k[10], 9,  38016083);
    c = gg(c, d, a, b, k[15], 14, -660478335);
    b = gg(b, c, d, a, k[4], 20, -405537848);
    a = gg(a, b, c, d, k[9], 5,  568446438);
    d = gg(d, a, b, c, k[14], 9, -1019803690);
    c = gg(c, d, a, b, k[3], 14, -187363961);
    b = gg(b, c, d, a, k[8], 20,  1163531501);
    a = gg(a, b, c, d, k[13], 5, -1444681467);
    d = gg(d, a, b, c, k[2], 9, -51403784);
    c = gg(c, d, a, b, k[7], 14,  1735328473);
    b = gg(b, c, d, a, k[12], 20, -1926607734);

    a = hh(a, b, c, d, k[5], 4, -378558);
    d = hh(d, a, b, c, k[8], 11, -2022574463);
    c = hh(c, d, a, b, k[11], 16,  1839030562);
    b = hh(b, c, d, a, k[14], 23, -35309556);
    a = hh(a, b, c, d, k[1], 4, -1530992060);
    d = hh(d, a, b, c, k[4], 11,  1272893353);
    c = hh(c, d, a, b, k[7], 16, -155497632);
    b = hh(b, c, d, a, k[10], 23, -1094730640);
    a = hh(a, b, c, d, k[13], 4,  681279174);
    d = hh(d, a, b, c, k[0], 11, -358537222);
    c = hh(c, d, a, b, k[3], 16, -722521979);
    b = hh(b, c, d, a, k[6], 23,  76029189);
    a = hh(a, b, c, d, k[9], 4, -640364487);
    d = hh(d, a, b, c, k[12], 11, -421815835);
    c = hh(c, d, a, b, k[15], 16,  530742520);
    b = hh(b, c, d, a, k[2], 23, -995338651);

    a = ii(a, b, c, d, k[0], 6, -198630844);
    d = ii(d, a, b, c, k[7], 10,  1126891415);
    c = ii(c, d, a, b, k[14], 15, -1416354905);
    b = ii(b, c, d, a, k[5], 21, -57434055);
    a = ii(a, b, c, d, k[12], 6,  1700485571);
    d = ii(d, a, b, c, k[3], 10, -1894986606);
    c = ii(c, d, a, b, k[10], 15, -1051523);
    b = ii(b, c, d, a, k[1], 21, -2054922799);
    a = ii(a, b, c, d, k[8], 6,  1873313359);
    d = ii(d, a, b, c, k[15], 10, -30611744);
    c = ii(c, d, a, b, k[6], 15, -1560198380);
    b = ii(b, c, d, a, k[13], 21,  1309151649);
    a = ii(a, b, c, d, k[4], 6, -145523070);
    d = ii(d, a, b, c, k[11], 10, -1120210379);
    c = ii(c, d, a, b, k[2], 15,  718787259);
    b = ii(b, c, d, a, k[9], 21, -343485551);

    x[0] = add32(a, x[0]);
    x[1] = add32(b, x[1]);
    x[2] = add32(c, x[2]);
    x[3] = add32(d, x[3]);
  }

  function cmn(q, a, b, x, s, t) {
    a = add32(add32(a, q), add32(x, t));
    return add32((a << s) | (a >>> (32 - s)), b);
  }

  function ff(a, b, c, d, x, s, t) {
    return cmn((b & c) | ((~b) & d), a, b, x, s, t);
  }

  function gg(a, b, c, d, x, s, t) {
    return cmn((b & d) | (c & (~d)), a, b, x, s, t);
  }

  function hh(a, b, c, d, x, s, t) {
    return cmn(b ^ c ^ d, a, b, x, s, t);
  }

  function ii(a, b, c, d, x, s, t) {
    return cmn(c ^ (b | (~d)), a, b, x, s, t);
  }

  function md51(s) {
    txt = '';
    var n = s.length,
    state = [1732584193, -271733879, -1732584194, 271733878], i;
    for (i=64; i<=s.length; i+=64) {
      md5cycle(state, md5blk(s.substring(i-64, i)));
    }
    s = s.substring(i-64);
    var tail = [0,0,0,0, 0,0,0,0, 0,0,0,0, 0,0,0,0];
    for (i=0; i<s.length; i++)
      tail[i>>2] |= s.charCodeAt(i) << ((i%4) << 3);
    tail[i>>2] |= 0x80 << ((i%4) << 3);
    if (i > 55) {
      md5cycle(state, tail);
      for (i=0; i<16; i++) tail[i] = 0;
    }
    tail[14] = n*8;
    md5cycle(state, tail);
    return state;
  }

  /* there needs to be support for Unicode here,
   * unless we pretend that we can redefine the MD-5
   * algorithm for multi-byte characters (perhaps
   * by adding every four 16-bit characters and
   * shortening the sum to 32 bits). Otherwise
   * I suggest performing MD-5 as if every character
   * was two bytes--e.g., 0040 0025 = @%--but then
   * how will an ordinary MD-5 sum be matched?
   * There is no way to standardize text to something
   * like UTF-8 before transformation; speed cost is
   * utterly prohibitive. The JavaScript standard
   * itself needs to look at this: it should start
   * providing access to strings as preformed UTF-8
   * 8-bit unsigned value arrays.
   */
  function md5blk(s) {
    var md5blks = [], i;
    for (i=0; i<64; i+=4) {
      md5blks[i>>2] = s.charCodeAt(i)
      + (s.charCodeAt(i+1) << 8)
      + (s.charCodeAt(i+2) << 16)
      + (s.charCodeAt(i+3) << 24);
    }
    return md5blks;
  }

  var hex_chr = '0123456789abcdef'.split('');
  function rhex(n) {
    var s='', j=0;
    for(; j<4; j++)
      s += hex_chr[(n >> (j * 8 + 4)) & 0x0F]
      + hex_chr[(n >> (j * 8)) & 0x0F];
    return s;
  }

  function hex(x) {
    for (var i=0; i<x.length; i++)
    x[i] = rhex(x[i]);
    return x.join('');
  }

  function md5(s) {
    return hex(md51(s));
  }

  /* this function is much faster,
  so if possible we use it. Some IEs
  are the only ones I know of that
  need the idiotic second function,
  generated by an if clause.  */

  function add32(a, b) {
    return (a + b) & 0xFFFFFFFF;
  }

  if (md5('hello') != '5d41402abc4b2a76b9719d911017c592') {
    function add32(x, y) {
      var lsw = (x & 0xFFFF) + (y & 0xFFFF),
      msw = (x >> 16) + (y >> 16) + (lsw >> 16);
      return (msw << 16) | (lsw & 0xFFFF);
    }
  }

  // expose
  if (typeof window.md5 !== 'function') window.md5 = md5;
  
})();
