/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.events {
	
	import flash.events.Event;
	
	public class BaseEvent extends Event
  {
      // pass params to event additionally
      public var data:Object;
        
      public function BaseEvent(type:String, obj:Object = null, bubbles:Boolean = false, cancelable:Boolean = false)
      {
          super(type, bubbles, cancelable);
          this.data = obj;
      };
        
      override public function clone():Event
      {
          return new BaseEvent(type, this.data, bubbles, cancelable);
      };
        
  } // end class
}
