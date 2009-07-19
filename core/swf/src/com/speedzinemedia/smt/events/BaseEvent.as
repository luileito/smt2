/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.events {
	
	import flash.events.Event;
	
	public class BaseEvent extends Event
    {
        // pass params to event additionally
        public var params:Object;
        
        public function BaseEvent(type:String, obj:Object = null, bubbles:Boolean = false, cancelable:Boolean = false)
        {
            super(type, bubbles, cancelable);
            this.params = obj;
        };
        
        override public function clone():Event
        {
            return new BaseEvent(type, this.params, bubbles, cancelable);
        };
        
    } // end class
}