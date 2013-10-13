/**
 *  Creates a visual help for tracking time.
 *  @version    0.1 - 13 Dec 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.display {

    import flash.events.MouseEvent;
    import flash.display.Sprite;
    import com.speedzinemedia.smt.events.PlayerEvent;
    import com.speedzinemedia.smt.utils.ProgressTimer;
    import com.speedzinemedia.smt.events.ProgressTimerEvent;
    import flash.external.ExternalInterface;
    
    public class ScrubberInteractive extends Scrubber
    {
        private var __scaleBefore:Number;
        
        /** 
         * Constructor.
         * @param prop: { time, width, height, color }
         */
        public function ScrubberInteractive(prop:Object)
        {
            super(prop);

            this.addEventListener(MouseEvent.MOUSE_OVER, onMouseOver);
            this.addEventListener(MouseEvent.MOUSE_MOVE, onMouseMove);
            this.addEventListener(MouseEvent.MOUSE_OUT, onMouseOut);
            this.addEventListener(MouseEvent.CLICK, onClick);
        };

        private function onMouseOver(e:MouseEvent):void 
        {
            if (__finished) return;
            //scrubZoomIn();
                        
            __scaleBefore = __prevScaleX; // save status when user doesn't click to seek
            updateScrubBg(e.currentTarget.mouseX);
        };

        private function onMouseMove(e:MouseEvent):void 
        {
            if (__finished) return;
            
            updateScrubBg(e.currentTarget.mouseX);
        };
        
        private function onMouseOut(e:MouseEvent):void 
        {
            if (__finished) return;
            //scrubZoomOut();
            
            seek(__scaleBefore);
            //resetScrubBg();
        };
        
        private function onClick(e:MouseEvent):void 
        {
            if (__finished) return;
            
            var perc:Number = e.currentTarget.mouseX/__info.width;
            seek(perc);
            parent.dispatchEvent(new PlayerEvent(PlayerEvent.SEEK, perc));            
            __scaleBefore = perc; // update new status
        };

        private function scrubZoomIn():void 
        {
            this.scaleY *= 3.0;
        };
        
        private function scrubZoomOut():void 
        {
            this.scaleY = 1.0;
        };
        
    } // end class
}
