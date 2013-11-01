/*! 
 * smt2e -- simple mouse tracking
 * Copyleft (cc) 2013 Luis Leiva
 * http://smt2.googlecode.com & http://smt.speedzinemedia.com
 */
(function(){var g;window.smt2={record:function(i){g=function(){window.smt2.record(i)}}};function c(j){var i=document.createElement("script");i.type="text/javascript";i.src=j;return i}var b=document.getElementsByTagName("script");var d=b[b.length-1];var f=d.src.split("/");f.splice(f.length-1,1);var h=f.join("/");var e=f[f.length-1]=="src"?".js":".min.js";var a=c(h+"/smt-aux"+e);d.parentNode.insertBefore(a,d.nextSibling);a.onload=function(){var i=c(h+"/smt-record"+e);d.parentNode.insertBefore(i,a.nextSibling);i.onload=function(){g();smt2.methods.init()};d.parentNode.removeChild(d)}})();