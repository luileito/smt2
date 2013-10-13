/**
 * Populates timeline information.
 * @author  Luis Leiva
 * @date    26 Sep 2009
 */
package com.speedzinemedia.smt.display {

    import flash.display.Sprite;
    import flash.utils.Timer;
    import flash.events.TimerEvent;
    
    import com.speedzinemedia.smt.interfaces.ITimelineControls;

    public class TimelineEventDispatcher extends Sprite implements ITimelineControls
    {
        private var $paused:Boolean;
        private var $loop:Timer;
        private var $step:Number;
        private var $info:Object;
        private const REFRESH_INTERVAL:int = 50;   // ms
        
        public function get paused():Boolean { return $paused; };
        public function get step():Number { return $step; };
        public function get info():Object { return $info; };
        
        public function TimelineEventDispatcher(prop:Object)
        {
            // normalize time step
            $step = prop.width / Math.ceil(prop.time*1000 / REFRESH_INTERVAL);
            // save properties reference
            $info = prop;
            
            // launch timer
            $loop = new Timer(REFRESH_INTERVAL);
            $loop.addEventListener(TimerEvent.TIMER, onProgress);
            $loop.start();
        };
            
        protected function onProgress(e:TimerEvent):void
        {
            if ($paused) return;

            if ($step < $info.width) {
                ++$step;
            } else {
                $loop.stop();
                //$step = $info.width;
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
        };

        /** Restarts scrubber animation. */
        public function restart():void
        {
            $loop.reset();
            $loop.start();
        };
    
    } // end class
}