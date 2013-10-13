/**
 *  @version    2.0 - 29 Jun 2012
 *  @autor      Luis Leiva
 */
package com.speedzinemedia.smt.text {

  import flash.external.ExternalInterface;
  import com.speedzinemedia.smt.utils.Utils;
    
  public class Hypernote {

    public static const WINDOW_ID:String = "hypereditor";

    public static function create(id:int, login:String, smpte:String):void 
    {
        var qs:String = Utils.buildQueryString({
          id:    id,
          login: login,
          time:  smpte
        });
        var basePath:String = ExternalInterface.call("window.smt2fn.getBaseURL");
        Utils.popup(basePath + "hypernotes/edit.php?" + qs, WINDOW_ID, {
          width:  560, 
          height: 260, 
          location: "no"
        });    
    };

    public static function read(id:int, login:String, smpte:String):void 
    {
        var qs:String = Utils.buildQueryString({
          id:    id,
          login: login,
          time:  smpte
        });
        var basePath:String = ExternalInterface.call("window.smt2fn.getBaseURL");
        Utils.popup(basePath + "hypernotes/read.php?" + qs, WINDOW_ID, {
          width:  560, 
          height: 260, 
          location: "no"
        });    
    };
        
    public static function manage(id:int, login:String):void 
    {
        var qs:String = Utils.buildQueryString({
          id:    id,
          login: login
        });        
        var basePath:String = ExternalInterface.call("window.smt2fn.getBaseURL");
        Utils.popup(basePath + "hypernotes/list.php?" + qs, WINDOW_ID, {
          width:  700, 
          height: 400, 
          location: "no"
        });
    };    
    
  } // end class
}
