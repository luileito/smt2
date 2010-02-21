/**
 *  Creates a visual help for tracking time.
 *  @version    0.1 - 13 Dec 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.display {
    
    import flash.display.Shape;
    import flash.display.Sprite;
    import flash.utils.Timer;
    import flash.events.TimerEvent;
    
    public class Scrubber extends Sprite
    {
        private var $scrub:Shape;
        private var $step:Number;
        private var $info:Object;
        private var $loop:Timer;
        private var $paused:Boolean;
        
        private const VSIZE:int = 2;    // scrubber height
        private const DELAY:int = 50;   // interval (ms)
        
        /** 
         * Constructor.
         * @param prop: { time, color }
         */
        public function Scrubber(prop:Object)
        {
            // draw bounding rectangle
            this.graphics.beginFill(0x000000);
            this.graphics.drawRect(0,0, prop.width, VSIZE);
            this.graphics.endFill();
            
            // draw scrubber itself
            var color:uint = (prop.color) ? prop.color : 0xFFCC33;
            $scrub = new Shape();
            $scrub.graphics.beginFill(color);
            $scrub.graphics.drawRect(0,0, 1, VSIZE);
            $scrub.graphics.endFill();
            this.addChild($scrub);
            
            // normalize time step
            $step = prop.width / Math.ceil(prop.time*1000 / DELAY);
            // save properties reference
            $info = prop;
            
            // launch timer
            $loop = new Timer(DELAY);
            $loop.addEventListener(TimerEvent.TIMER, progress);
            $loop.start();
        };
                    
        private function progress(e:TimerEvent):void 
        { 
            if ($paused) return;
            
            if ($scrub.width < $info.width) {
                $scrub.scaleX += $step;
            } else {
                $loop.stop(); 
            }
        };
        
        /** Toggles scrubber animation. */    
        public function pause():void 
        {
            $paused = !$paused;
        };
        
        /** Finishes scrubber animation. */
        public function finish():void 
        {
            $scrub.width = $info.width;
        };
        
        /** Restarts scrubber animation. */    
        public function restart():void 
        {
            $loop.stop();
            // reset
            $scrub.width = 1;
            $loop.start();
        };
          
    } // end class
}