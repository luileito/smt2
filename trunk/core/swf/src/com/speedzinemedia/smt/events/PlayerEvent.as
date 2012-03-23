/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.events {
	
	import com.speedzinemedia.smt.events.BaseEvent;
	
	public class PlayerEvent extends BaseEvent
    {
        public static const PLAY:String  = "play";
        public static const PAUSE:String = "pause";
        public static const STOP:String  = "stop";
        
        public function PlayerEvent(type:String, obj:Object = null)
        {
            super(type, obj);
        };
        
    } // end class
}