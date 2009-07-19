/**
 *  (smt) Modal Confirm  
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.modal {

    import flash.display.DisplayObjectContainer;
    import flash.events.MouseEvent;

    import com.bit101.components.PushButton;
    import com.speedzinemedia.smt.modal.ModalDialog;
    import com.speedzinemedia.smt.events.ModalEvent;
    
    public class ModalConfirm extends ModalDialog
    {
           
        public function ModalConfirm(parent:DisplayObjectContainer, displayText:String):void 
        {   
            super(parent, displayText);
            
            var yes:PushButton = new PushButton(this, 0, 10 + parent.y + this.height, ModalEvent.MODAL_ACCEPT, acceptMe);
            var no:PushButton = new PushButton(this, yes.x + yes.width + 10, yes.y, ModalEvent.MODAL_CANCEL, cancelMe);
        };
        
        private function acceptMe(e:MouseEvent):void 
        {
            dispatchEvent(new ModalEvent(ModalEvent.MODAL_ACCEPT));
            super.close();
        };
        
        private function cancelMe(e:MouseEvent):void 
        {
            super.close();
        };
    }
}
