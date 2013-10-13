/**
 *  (smt) Modal Confirm  
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.modal {

    import flash.display.DisplayObjectContainer;
    import flash.events.MouseEvent;
    
    import com.bit101.components.PushButton;
    import com.speedzinemedia.smt.events.ModalEvent;
    import com.speedzinemedia.smt.modal.ModalDialog;

    public class ModalAlert extends ModalDialog
    {
        
        public function ModalAlert(parent:DisplayObjectContainer, displayText:String):void 
        {   
            super(parent, displayText);

            var yes:PushButton = new PushButton(this, 0, 10 + parent.y + this.height, ModalEvent.MODAL_ACCEPT, closeMe);
        };
        
        private function closeMe(e:MouseEvent):void 
        {
            super.close();
        };
    }
}
