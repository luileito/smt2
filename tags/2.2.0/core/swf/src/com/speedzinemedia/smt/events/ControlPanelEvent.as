/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.events {
	
	import com.speedzinemedia.smt.events.BaseEvent;
	
	public class ControlPanelEvent extends BaseEvent
  {
    // notify from ControlPanel to Tracking application
    public static const CREATE_HYPERNOTE:String   = "createHyperNote";
    public static const TOGGLE_HYPERNOTE:String   = "toggleHyperNote";
    public static const REQUEST_CUEPOINT:String   = "requestCuepoint";
    public static const UPDATED_CUEPOINT:String   = "updatedCuepoint";
    // notify from ControlPanel to Tracking application
    public static const TOGGLE_REPLAY_MODE:String = "toggleReplayMode";
    // notify from Tracking to ControlPanel
    public static const REPLAY_COMPLETE:String    = "replayComplete";
        
    public function ControlPanelEvent(type:String, obj:Object = null)
    {
      super(type, obj);
    };
        
  } // end class
}
