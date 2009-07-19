/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.events {
	
	import com.speedzinemedia.smt.events.BaseEvent;
	
	public class ControlPanelEvent extends BaseEvent
    {
        // notify from ControlPanel to Tracking application
        public static const TOGGLE_REPLAY_MODE:String = "toggleReplayMode";
        // notify from Tracking to ControlPanel
        public static const REPLAY_COMPLETE:String = "replayComplete";
        
        public function ControlPanelEvent(type:String, obj:Object = null)
        {
            super(type, obj);
        };
        
    } // end class
}