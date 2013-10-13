/**
 *  Draws the mouse path and adds the user label
 *  @version    1.0 - 26 Sep 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.mouse {
	 
    import flash.display.DisplayObjectContainer;
    import com.speedzinemedia.smt.interfaces.ITimelineControls;
    import com.speedzinemedia.smt.mouse.MouseView;
    
    public class MouseManager implements ITimelineControls
    {              
        private var $instances:Vector.<MouseView>;
		    private var $container:DisplayObjectContainer;
		    private var _percent:Number;
        private var _paused:Boolean;		    
		
		    public var config:Object = {
		      realtime: true,  // realtime replay
		      heatmaps: false, // use heatmaps (shadowmaps, actually)
		      nopauses: false  // skip dwell times while replaying
		    };
		    
		  
        public function MouseManager(parent:DisplayObjectContainer) 
        {
            $container = parent;
            // register MouseView instances
            $instances = getInstances();
        };
		  
		    public function init(conf:Object):void 
        {
            for (var prop:String in conf) {
              if (config.hasOwnProperty(prop) && typeof conf[prop] !== null) {
                config[prop] = conf[prop];
              }
            }
            
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

		    public function stop():void 
        {   
            $instances.forEach(stopAll);
            
            _paused = true;
        };

		    public function resume():void 
        {   
            $instances.forEach(resumeAll);
            
            _paused = false;
        };
               
		    public function restart():void 
        {   
            $instances.forEach(finishAll);
            $instances.forEach(initAll);
        };
                        
        public function seek(perc:Number):void 
        {
            _percent = perc;
            $instances.forEach(seekAll);
        };
        
		    private function initAll(element:*, index:int, arr:Vector.<MouseView>):void 
        {   
            element.init(config);
        };
		  
        private function finishAll(element:*, index:int, arr:Vector.<MouseView>):void 
        {   
			      element.finish();
        };
		  
		    private function pauseAll(element:*, index:int, arr:Vector.<MouseView>):void 
        {   
            element.pause();
        };

		    private function stopAll(element:*, index:int, arr:Vector.<MouseView>):void 
        {   
            element.stop();
        };

		    private function resumeAll(element:*, index:int, arr:Vector.<MouseView>):void 
        {   
            element.resume();
        };
                        
		    private function seekAll(element:*, index:int, arr:Vector.<MouseView>):void 
        {   
            element.seekTo(_percent);
        };
        		  
		    public function getInstances():Vector.<MouseView>
        {   
            var elems:Vector.<MouseView> = new Vector.<MouseView>();
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
