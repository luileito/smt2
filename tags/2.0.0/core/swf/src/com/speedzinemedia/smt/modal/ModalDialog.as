/**
 *  (smt) Modal Confirm  
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.modal {

    import flash.display.DisplayObjectContainer;
    import flash.text.TextField;
    import flash.text.TextFieldAutoSize;
    import flash.text.TextFormat;
    
    import com.speedzinemedia.smt.modal.ModalWindow;
    
    public class ModalDialog extends ModalWindow
    {
        
        public function ModalDialog(parent:DisplayObjectContainer, displayText:String):void 
        {   
            var fmt:TextFormat = new TextFormat("_sans", 30, 0xFFFFFF);
            var msg:TextField = new TextField();
            msg.defaultTextFormat = fmt;
            msg.background = true;
            msg.backgroundColor = 0x555555;
            msg.autoSize = TextFieldAutoSize.LEFT;
            msg.selectable = false;
            msg.text = displayText;
            super.addChild(msg);
            
            super.show(parent, this);
        };
        
    }
}
