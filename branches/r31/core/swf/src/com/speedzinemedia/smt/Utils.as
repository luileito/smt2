/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt {

    import flash.display.Sprite;
    import flash.display.Stage;
    import flash.display.StageAlign;
    import flash.display.StageQuality;
    import flash.display.StageScaleMode;
    import flash.geom.Point;
    import flash.net.LocalConnection;
    import flash.net.navigateToURL;
    import flash.net.URLRequest;
    import flash.ui.ContextMenu;
    import flash.ui.ContextMenuItem;
    import flash.events.ContextMenuEvent;
    import flash.display.DisplayObjectContainer;
    
    public class Utils
    {
    
        public static function checkAngle(ini:Point, end:Point):Number
        {
        	return Math.atan2(end.y - ini.y, end.x - ini.x);
        };
        
        public static function parseColor(str:String):uint 
        {   
            return parseInt("0x" + str, 16);
        };
        
        public static function getFlashVar(fvar:String, desiredType:String):* 
        {
            switch (desiredType.toLowerCase()) {
                case 'array':
                    return fvar.split(",");
                    break;
                case 'int':
                    return parseInt(fvar);
                    break;
                case 'boolean':
                    return Boolean( parseInt(fvar) );
                    break;
                case 'string':
                default:
                    return fvar;
                    break;
            }
        };
        
        public static function drawStar(d:Sprite, x:Number, y:Number, size:int, offset:int = 0):void 
        {   
            d.graphics.moveTo(x - offset, y - offset);
            d.graphics.lineTo(x - size,   y - size);
            d.graphics.moveTo(x - offset, y + offset);
            d.graphics.lineTo(x - size,   y + size);
            d.graphics.moveTo(x + offset, y - offset);
            d.graphics.lineTo(x + size,   y - size);
            d.graphics.moveTo(x + offset, y + offset);
            d.graphics.lineTo(x + size,   y + size);
        };
        
        public static function allowDomainURI(url:String):Boolean 
        {
            var lc:LocalConnection = new LocalConnection();
            var domainName:String = lc.domain;
            var pattern:RegExp = new RegExp("^http[s]?\:\\/\\/([^\\/]+)\\/");
            var result:Object = pattern.exec(url);
            return (result !== null || result[1] == domainName || url.length < 4096);
        };
        
        public static function initStage(s:Stage, aplication:DisplayObjectContainer):void 
        {   
            // configure stage
            s.align = StageAlign.TOP_LEFT;
            s.scaleMode = StageScaleMode.NO_SCALE;
            s.quality = StageQuality.LOW;
            // customize right click menu (do not remove!)
            var cm:ContextMenu = new ContextMenu();
            var cmi:ContextMenuItem = new ContextMenuItem("about (smt) 2.0");
            cmi.addEventListener(ContextMenuEvent.MENU_ITEM_SELECT, aboutSMT);
            cm.hideBuiltInItems();
            cm.customItems.push(cmi);
            aplication.contextMenu = cm;
        };
        
        private static function aboutSMT(e:ContextMenuEvent):void 
        {
            var request:URLRequest = new URLRequest("http://smt.speedzinemedia.com/");
            navigateToURL(request);
        };
        
    } // end class
}