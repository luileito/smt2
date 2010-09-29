/**
 *  Tweaked from http://www.veryinteractivepeople.com/?p=145
 *  @version    2.0 - 20 Apr 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.draw {

    import flash.display.BitmapData;
    import flash.display.DisplayObjectContainer;
    import flash.display.Sprite;
    import flash.geom.Matrix;
    import flash.geom.Rectangle;
    import flash.events.Event;
    //import flash.events.TimerEvent;
    //import flash.utils.Timer;

    public class AntsRectangle extends Sprite
    {
        private var $horizontalBitmap:BitmapData;
        private var $verticalBitmap:BitmapData;
        private var $bitmapScroll:Number = 0;
        private var $lineThickness:int;
        private var $rect:Rectangle;
       
        public function AntsRectangle(parent:DisplayObjectContainer, thick:int = 1)
        {
            $lineThickness = thick;
            $rect = new Rectangle(0,0, parent.width,parent.height);
            $horizontalBitmap = new BitmapData(4, 2, false, 0xFFFFFF);
            $verticalBitmap = new BitmapData(2, 4, false, 0xFFFFFF);
            
            initBitmaps();
        };
        
        private function initBitmaps():void
        {
            for (var x:Number = 0; x < 2; ++x) {
                for (var y:Number = 0; y < 2; ++y) {
                    $horizontalBitmap.setPixel(x, y, 0x000000);
                    $verticalBitmap.setPixel(x, y, 0x000000);
                }
            }
            // loop
            addEventListener(Event.ENTER_FRAME, update);
            /*
            var t:Timer = new Timer(100);
            t.addEventListener(TimerEvent.TIMER, update);
            t.start();
            */
        };
        
        private function update(e:Event):void
        {
            scrollBitmaps();
            
            graphics.clear();
            // horizontal
            graphics.beginBitmapFill($horizontalBitmap, new Matrix(1, 0, 0, 1, $bitmapScroll, 0), true, false);
            graphics.drawRect($rect.x, $rect.y - $lineThickness, $rect.width, $lineThickness);
            graphics.drawRect($rect.x, $rect.y + $rect.height, $rect.width, $lineThickness);
            // vertical
            graphics.beginBitmapFill($verticalBitmap,new Matrix(1, 0, 0, 1, 0, $bitmapScroll),true,false);
            graphics.drawRect($rect.x - $lineThickness, $rect.y - $lineThickness, $lineThickness, $rect.height + $lineThickness * 2);
            graphics.drawRect($rect.x + $rect.width, $rect.y - $lineThickness, $lineThickness, $rect.height + $lineThickness * 2);
            
            //stage.invalidate(); // if Event.ENTER_FRAME?
            //e.updateAfterEvent(); // if TimerEvent?
        };
        
        private function scrollBitmaps():void
        {
            ++$bitmapScroll;
            
            if ($bitmapScroll > 3) { $bitmapScroll = 0; }
        };

    } // end class
}