/**
 *  Creates a visual help for tracking time.
 *  @version    0.1 - 13 Dec 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.display {

    import flash.display.Sprite;
    import com.speedzinemedia.smt.interfaces.ITimelineControls;
    import com.speedzinemedia.smt.utils.ProgressTimer;
    import com.speedzinemedia.smt.events.ProgressTimerEvent;
    import flash.external.ExternalInterface;
    public class Scrubber extends Sprite implements ITimelineControls
    {
        private var __scrub:Sprite;
        private var __info:Object;
        private var __step:Number;
        private var __loop:ProgressTimer;
        private var __paused:Boolean;
        private var __finished:Boolean;
        private var __prevScaleX:Number = 0.0;
        
        private const VERTICAL_SIZE:int = 2; // default scrubber height

        public function get finished():Boolean { return __finished; };
        public function get paused():Boolean { return __paused; };
        public function get step():Number { return __step; };
        /** 
         * Constructor.
         * @param prop: { time, width, height, color }
         */
        public function Scrubber(prop:Object)
        {
            var vSize:int = (prop.height) ? prop.height : VERTICAL_SIZE;
            var color:uint = (prop.color) ? prop.color : 0xFFCC33;
            
            // draw bounding rectangle
            this.graphics.beginFill(0x000000);
            this.graphics.drawRect(0,0, prop.width, vSize);
            this.graphics.endFill();
            
            // draw scrubber itself
            __scrub = new Sprite();
            __scrub.graphics.beginFill(color);     
            __scrub.graphics.drawRect(0,0, prop.width, vSize);
            __scrub.graphics.endFill();
            this.addChild(__scrub);
            
            // normalize time step
            __step = Math.round(prop.width/prop.time);
            // save properties for later referencing
            __info = prop;

            __loop = new ProgressTimer();
            __loop.maxTimeMs = prop.time * 1000;
            __loop.addEventListener(ProgressTimerEvent.PROGRESS, progress);
            __loop.start();
        };

        private function progress(e:ProgressTimerEvent):void
        {       
            if (__paused) {
                __prevScaleX = __scrub.scaleX;
                return;
            }
            if (__scrub.width < __info.width) {
                __scrub.scaleX = __prevScaleX + e.progress;
            } else {
                __loop.stop();
            }
        };

        /** Toggles scrubber animation. */
        public function pause():void
        {
            __paused = !__paused;
            __loop.pause();
        };

        /** Finishes scrubber animation. */
        public function finish():void
        {
            __scrub.scaleX = 1;
            __prevScaleX = 0;
            
            __paused = true;
            __finished = true;
            __loop.stop();
        };

        /** Restarts scrubber animation. */
        public function restart():void
        {
            __scrub.scaleX = 0;
            __prevScaleX = 0;
                       
            __paused = false;
            __finished = false;
            __loop.start();
        };

    } // end class
}
