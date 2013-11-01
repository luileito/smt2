/*! 
 * smt2e -- simple mouse tracking
 * Copyleft (cc) 2013 Luis Leiva
 * http://smt2.googlecode.com & http://smt.speedzinemedia.com
 */
(function(){

  // Delay recording function until smt2 libs are fully loaded
  var smt2cache;
  window.smt2 = {
    record: function(opts) {
      smt2cache = function() { window.smt2.record(opts); }
    }
  }
  
  function createScript(filepath) {
    var scriptElem = document.createElement('script');
    scriptElem.type = "text/javascript";
    scriptElem.src = filepath;
    return scriptElem;
  }
  
  // Grab path of currently executing script
  var scripts = document.getElementsByTagName('script');
  var currentScript = scripts[scripts.length - 1];
  // Remove filename
  var pathParts = currentScript.src.split("/");
  pathParts.splice(pathParts.length - 1, 1);
  // Now we have the full script path
  var path = pathParts.join("/");
  // Load smt2 libs accordingly: first aux functions, then record
  var ext = pathParts[pathParts.length - 1] == "src" ? ".js" : ".min.js";
  var aux = createScript(path + "/" + "smt-aux" + ext);
  currentScript.parentNode.insertBefore(aux, currentScript.nextSibling);
  aux.onload = function() {
    var record = createScript(path + "/" + "smt-record" + ext);  
    currentScript.parentNode.insertBefore(record, aux.nextSibling);
    record.onload = function() {
      smt2cache();
      // DOM is already loaded, so make this explicit fn call
      smt2.methods.init();
    }
    // Finally remove loader script
    currentScript.parentNode.removeChild(currentScript);
  }

})();
