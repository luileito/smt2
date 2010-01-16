/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.events {
	
	import com.speedzinemedia.smt.events.BaseEvent;
	
	public class ModalEvent extends BaseEvent
    {
        // notify from Modal Alert / Confirm
        public static const MODAL_ACCEPT:String = "ACCEPT";
        // notify from Modal Confirm
        public static const MODAL_CANCEL:String = "CANCEL";
        
        public function ModalEvent(type:String, obj:Object = null)
        {
            super(type, obj);
        };
        
    } // end class
}