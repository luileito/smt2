/**
 *  Draws the mouse path and adds the user label
 *  @version    1.0 - 26 Sep 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.mouse {
	 
    import flash.display.DisplayObjectContainer;
    import com.speedzinemedia.smt.events.TrackingEvent;
    import com.speedzinemedia.smt.mouse.MouseView;
    
    public class MouseManager
    {      
        private var $instances:Array = [];
		private var $container:DisplayObjectContainer;
		private var _dynamic:Boolean = true;
		private var _heatmap:Boolean;
		private var _paused:Boolean;
		  
		public function get paused():Boolean { return _paused; }
		
		public function set dynamic(value:Boolean):void { 
            _dynamic = value;
		}
		public function set heatmap(value:Boolean):void {
            _heatmap = value;
		}
		  
        public function MouseManager(parent:DisplayObjectContainer) 
        {
            $container = parent;
            // register MouseView instances
            $instances = getInstances();
        };
		  
		public function init():void 
        {
            $instances.forEach(initAll);
        };
		  
        public function finish():void 
        {   
            $instances.forEach(finishAll);
        };
		  
		public function pause():void 
        {   
            $instances.forEach(pauseAll);
            
            _paused = !_paused;
        };
		  
		private function initAll(element:*, index:int, arr:Array):void 
        {   
            element.init(_dynamic, _heatmap);
        };
		  
        private function finishAll(element:*, index:int, arr:Array):void 
        {   
			element.finish();
        };
		  
		private function pauseAll(element:*, index:int, arr:Array):void 
        {   
            element.pause();
        };
		  
		public function getInstances():Array
        {   
            var elems:Array = [];
			for (var i:int = 0; i < $container.numChildren; ++i) {
                var instance:* = $container.getChildAt(i);
				if (instance is MouseView) {
				    elems.push(instance);
				}
			}
			
			return elems;
        };
            
    } // end class
}