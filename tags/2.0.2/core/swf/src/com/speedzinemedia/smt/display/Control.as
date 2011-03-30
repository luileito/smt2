package com.speedzinemedia.smt.display {

	import flash.display.Sprite;
	import flash.events.EventDispatcher;

	/**
	 * Base class for interactive controls.
	 * @autor Luis Leiva
	 * @date 14-Ene-2010
	 */
	public class Control extends EventDispatcher
	{
		private var $target:Sprite;
		
		/**
		 * Creates a new Control
		 */
		public function Control(obj:Sprite) 
		{
			$target = obj;
		}
		
		public function get target():Sprite
		{
			return $target;
		};
		
		// -- MXML ------------------------------------------------------------
		
		/** @private */
		public function initialized(document:Object, id:String):void
		{
			// do nothing
		};
		
	} // end class
}