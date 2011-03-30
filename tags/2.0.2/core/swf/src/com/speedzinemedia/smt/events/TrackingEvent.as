/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.events {
	
	import com.speedzinemedia.smt.events.BaseEvent;
	
	public class TrackingEvent extends BaseEvent
    {
        /** First coordinate is reached */
        public static const MOUSE_INI:String    = "mouseIni";
        /** End coordinate is reached */
        public static const MOUSE_END:String    = "mouseEnd";
        /** Main loop */
        public static const MOUSE_LOOP:String   = "mouseLoop";
        /** Single click */
        public static const MOUSE_CLICK:String  = "mouseClick";
        /** Drag and Drop */
        public static const MOUSE_DRAG:String   = "mouseDrag";
        /** Movements */
        public static const MOUSE_MOVE:String   = "mouseMove";
        /** Hesitations */
        public static const MOUSE_STOP:String   = "mouseStop";
        /** Resume movement */
        public static const MOUSE_RESUME:String = "mouseResume";
		  
        public function TrackingEvent(type:String, obj:Object = null)
        {
            super(type, obj);
        };
        
    } // end class
}