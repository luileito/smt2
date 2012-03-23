/**    
 *  @autor      Nutrox @ UltraShock 
 */
package com.speedzinemedia.smt.utils {

	import com.speedzinemedia.smt.events.ProgressTimerEvent;
	
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.external.ExternalInterface;
	import flash.utils.clearInterval;
	import flash.utils.getTimer;
	import flash.utils.setInterval;
	
	public class ProgressTimer extends EventDispatcher
	{
        /** Amount of time to reach. */
        public var maxTimeMs:int = 0;
        
        private var __startTime:int  = 0; // origin
		private var __targetTime:int = 0; // goal
		private var __pauseTime:int  = 0; // helper flag
		private var __interval:int   = 0; // clock
        
		public function ProgressTimer(){}
		
		public function start():void
		{
			stop();
			
			__startTime  = getTimer();
            __pauseTime  = __startTime;
			__targetTime = __startTime + maxTimeMs;
			__interval   = setInterval(update, 30);
		};
		
		public function pause():void
		{
            __pauseTime = getTimer();
        };
		
		public function stop():void
		{
            clearInterval(__interval);
		};
                		
		private function update():void
		{
		    var p:Number = (getTimer() - __pauseTime) / (__targetTime - __startTime);
            if (p >= 1) {
    			p = 1;
                stop();
    		}
			dispatchEvent(new ProgressTimerEvent(ProgressTimerEvent.PROGRESS, p));
		};
		
	} // end class
}