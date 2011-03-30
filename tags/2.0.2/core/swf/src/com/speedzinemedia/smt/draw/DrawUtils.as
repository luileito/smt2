/**
 *  @version    1.0 - 26 Sep 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.draw {

    import flash.display.Shape;
    import flash.display.Sprite;
    import flash.filters.BitmapFilter;
    import flash.filters.BlurFilter;
    import flash.geom.ColorTransform;
    import flash.geom.Point;
    
    public class DrawUtils
    {
        public static var $minStarRadius:int = 3;
        public static var $maxStarRadius:int = 20;
        
        public static function parseColor(str:String):uint 
        {   
            return parseInt("0x" + str, 16);
        };

        public static function changeInstanceColor(displayObject:*, newColor:uint):void
        {
            var c:ColorTransform = new ColorTransform();
            c.color = newColor;
            displayObject.transform.colorTransform = c;
        };
               
        public static function drawStar(layer:Sprite, p:Point, size:int, offset:int = 0):void
        {   
            layer.graphics.moveTo(p.x - offset, p.y - offset);
            layer.graphics.lineTo(p.x - size,   p.y - size);
            layer.graphics.moveTo(p.x - offset, p.y + offset);
            layer.graphics.lineTo(p.x - size,   p.y + size);
            layer.graphics.moveTo(p.x + offset, p.y - offset);
            layer.graphics.lineTo(p.x + size,   p.y - size);
            layer.graphics.moveTo(p.x + offset, p.y + offset);
            layer.graphics.lineTo(p.x + size,   p.y + size);
        };
        
        /** @param layer Sprite or Shape instance */
        public static function drawCircle(layer:*, p:Point, size:int, color:uint = 0xFFFFFF, alpha:Number = 0.5):void
        {
            layer.graphics.beginFill(color, alpha);
            layer.graphics.drawCircle(p.x, p.y, size);
            layer.graphics.endFill();
        };
        
        public static function drawCircleCenter(layer:Sprite, p:Point, size:int, color:uint = 0x000000):void
        {
            // draw center as a star
            var centerSize:Number = size/10;
            if (centerSize > $minStarRadius) {
                // limit max size
                if (centerSize > $maxStarRadius) { centerSize = $maxStarRadius; }
                // draw
                layer.graphics.lineStyle(0, color);
                DrawUtils.drawStar(layer, p, centerSize);
            }
        };

        public static function createCircle(p:Point, size:int, color:uint = 0xFFFFFF, alpha:Number = 0.5):Shape
        {
            var c:Shape = new Shape();
            
            drawCircle(c, p, size, color, alpha);

            return c;
        };
        
        public static function createGlow(p:Point, size:int = 20, color:uint = 0xFFFFFF, alpha:Number = 0.5):Shape
        {
            var gp:Shape = createCircle(p, size, color, alpha);
            gp.filters = applyBlurFilter(size);
            
            return gp;
        };

        public static function applyBlurFilter(size:Number = 20, quality:int = 1):Array
        {
            var blur:BitmapFilter = new BlurFilter(size, size);
            var filter:Array = new Array();
            filter.push(blur);

            return filter;
        };
        
    } // end class
}