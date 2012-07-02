/**
 *  @autor      Nutrox @ UltraShock  
 */
package com.speedzinemedia.smt.events {

	import flash.events.Event;
	
	public class ProgressTimerEvent extends Event
	{
		public static const PROGRESS:String = "progress";
		
		private var _progress:Number = 0;
		public function get progress():Number {
			return _progress;
		};
		
		public function ProgressTimerEvent(type:String, progress:Number)
		{
			super(type, false, false);
			_progress = progress;
		};
		
    } // end class
}