/**
 *  Creates a visual help for tracking time.
 *  @version    0.1 - 13 Dec 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.display {

    import flash.events.MouseEvent;
    import flash.display.Sprite;
    import com.speedzinemedia.smt.events.PlayerEvent;
    import com.speedzinemedia.smt.interfaces.ITimelineControls;
    import com.speedzinemedia.smt.utils.ProgressTimer;
    import com.speedzinemedia.smt.events.ProgressTimerEvent;
    import flash.external.ExternalInterface;
    
    public class Scrubber extends Sprite implements ITimelineControls
    {        
        protected var __scrubBg:Sprite;    
        protected var __scrubFg:Sprite;
        protected var __info:Object;
        protected var __step:Number;
        protected var __loop:ProgressTimer;
        protected var __finished:Boolean;
        protected var __prevScaleX:Number = 0.0;
        protected var __paused:Boolean;
        
        public function get finished():Boolean { return __finished; };
        public function get step():Number { return __step; };
        public function get position():Number { return __scrubFg.width; };
        public function get paused():Boolean { return __paused; };        
        /** 
         * Constructor.
         * @param prop: { time, width, height, color }
         */
        public function Scrubber(prop:Object)
        {
            var vSize:int = (prop.height) ? prop.height : 2;
            var color:uint = (prop.color) ? prop.color  : 0xFFCC33;
            
            // draw bounding rectangle
            this.graphics.beginFill(0x000000);
            this.graphics.drawRect(0,0, prop.width, vSize);
            this.graphics.endFill();
            
            // draw scrubbers
            __scrubBg = createScrub(0xCCCCCC, prop.width, vSize);            
            __scrubFg = createScrub(color, prop.width, vSize);
            this.addChild(__scrubBg);
            this.addChild(__scrubFg);
                       
            this.addEventListener(PlayerEvent.SEEK, onSeek);
            
            // normalize time step
            __step = Math.floor(prop.width/prop.time);
            // save properties for later referencing
            __info = prop;

            __loop = new ProgressTimer();
            __loop.maxTimeMs = prop.time * 1000;
            __loop.frameRate = prop.fps;
            __loop.addEventListener(ProgressTimerEvent.PROGRESS, onProgress);
            __loop.start();
        };

        private function createScrub(color:uint, width:Number, height:Number):Sprite
        {
            var s:Sprite = new Sprite();
            s.graphics.beginFill(color);     
            s.graphics.drawRect(0,0, width, height);
            s.graphics.endFill();
            s.scaleX = 0.0;
            
            return s;
        };

        protected function updateScrubBg(pos:Number):void 
        {
            __scrubBg.scaleX = pos/__info.width;
            if (__scrubFg.width > pos) {
              __scrubFg.scaleX = __scrubBg.scaleX;
            }
        };
        
        protected function resetScrubBg():void 
        {
            __scrubBg.scaleX = __scrubFg.scaleX = __prevScaleX;
        };
        
        protected function onSeek(e:PlayerEvent):void
        {
            seek( Number(e.data) );
        };

        protected function onReload(e:PlayerEvent):void
        {
            restart();
        };
        
        private function onProgress(e:ProgressTimerEvent):void
        {
            if (__paused) {
                __prevScaleX = __scrubFg.scaleX;
            } else if (__scrubFg.width < __info.width) {
                __scrubFg.scaleX = __prevScaleX + e.progress;
            } else {
                __loop.stop();
            }
        };

        /** Seeks to time percentage. */
        public function seek(perc:Number):void
        {
            //__scrubBg.scaleX = __scrubFg.scaleX = perc;        
            __scrubBg.scaleX = __scrubFg.scaleX = __prevScaleX = perc;
        };
        
        /** Toggles scrubber animation. */
        public function pause():void
        {
            __paused = !__paused;
            __loop.pause();
        };

        /** Stops scrubber animation. */
        public function stop():void
        {
            __paused = true;
        };

        /** Resumes scrubber animation. */
        public function resume():void
        {
            __paused = false;
        };
                
        /** Finishes scrubber animation. */
        public function finish():void
        {
            __scrubFg.scaleX = __scrubBg.scaleX = 1.0;
            __prevScaleX = 0.0;
            
            __paused = true;
            __finished = true;
            __loop.stop();
        };

        /** Restarts scrubber animation. */
        public function restart():void
        {
            __scrubFg.scaleX = __scrubBg.scaleX = 0.0;
            __prevScaleX = 0.0;
                       
            __paused = false;
            __finished = false;
            __loop.start();
        };

    } // end class
}
