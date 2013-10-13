/**
 *  Miscelaneous functions.
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.utils {

    import flash.display.Sprite;
    import flash.display.Stage;
    import flash.display.StageAlign;
    import flash.display.StageQuality;
    import flash.display.StageScaleMode;
    import flash.external.ExternalInterface;    
    import flash.geom.Point;
    import flash.net.LocalConnection;
    import flash.net.navigateToURL;
    import flash.net.URLRequest;
    import flash.ui.ContextMenu;
    import flash.ui.ContextMenuItem;
    import flash.events.ContextMenuEvent;
    import flash.display.DisplayObjectContainer;
    import com.adobe.serialization.json.*;    
    
    public class Utils
    {
        /** Computes the angle (in radians) between 2 points. */
        public static function angle(ini:Point, end:Point):Number
        {
        	  return Math.atan2(end.y - ini.y, end.x - ini.x);
        };
        
        /** Gets a flash var and assigns it the proper casting. */
        public static function getFlashVar(fvar:String, casting:String = "string"):*
        {
            if (fvar === null) throw new Error("Invalid Flash var!", 1009);
            
            switch (casting.toLowerCase()) {
                case 'array':
                    return fvar.split(",");
                    break;                    
                case 'int':
                    return parseInt(fvar);
                    break;
                case 'number':
                case 'float':
                    return parseFloat(fvar);
                    break;                 
                case 'boolean':
                    return Boolean( parseInt(fvar) );
                    break;
                case 'object':
                case 'json':                
                    return JSON.decode(fvar);                    
                    break;
                case 'string':
                default:
                    return fvar;
                    break;
            }
        };

        /** Checks if a given URI is secure. */
        public static function allowDomainURI(url:String):Boolean 
        {
            var lc:LocalConnection = new LocalConnection();
            var domainName:String = lc.domain;
            var pattern:RegExp = new RegExp("^http[s]?\:\\/\\/([^\\/]+)\\/");
            var result:Object = pattern.exec(url);
            
            return (result !== null || result[1] == domainName || url.length < 4096);
        };

        /** Builds a query string from object. */
        public static function buildQueryString(o:Object, glue:String = "&"):String
        {
            //var qs:Vector.<String> = new Vector.<String>();
            var qs:Array = [];
            for (var prop:String in o) {
              qs.push(prop + '=' + o[prop]);
            }
            
            return qs.join(glue);
        };
        
        /** Opens a pop-up window. */
        public static function popup(url:String, target:String = "popup", windowFeatures:Object = null):void
        {
            var args:String = url +"', '"+ target;
            if (windowFeatures) {
              args += "', '" + buildQueryString(windowFeatures, ",");
            }
            var request:URLRequest = new URLRequest("javascript:window.open('"+args+"');void(0)");
            // use _top to avoid "Ignoring cross-frame javascript URL load requested by plugin" message
            navigateToURL(request, "_top");
        };
        
        public static function toArray(iterable:*):Array 
        {
           var ret:Array = [];
           for each (var elem:* in iterable) ret.push(elem);
           return ret;
        };
        
        /** Initializes the Stage (quality, alignment, scaling...). */
        public static function initStage(application:DisplayObjectContainer):void 
        {   
            // configure stage
            application.stage.align = StageAlign.TOP_LEFT;
            application.stage.scaleMode = StageScaleMode.NO_SCALE;
            application.stage.quality = StageQuality.LOW;
            // customize right click menu (do not remove!)
            var cm:ContextMenu = new ContextMenu();
            var cmi:ContextMenuItem = new ContextMenuItem("about smt2");
            cmi.addEventListener(ContextMenuEvent.MENU_ITEM_SELECT, aboutSMT);
            cm.hideBuiltInItems();
            cm.customItems.push(cmi);
            application.contextMenu = cm;
        };
        
        private static function aboutSMT(e:ContextMenuEvent):void 
        {
            var request:URLRequest = new URLRequest("http://smt.speedzinemedia.com/");
            navigateToURL(request);
        };
        
    } // end class
}
