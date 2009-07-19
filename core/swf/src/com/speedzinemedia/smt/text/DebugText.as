/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.text {

    import flash.display.DisplayObjectContainer;
    import flash.events.Event;
    import flash.text.TextField;
    import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;
    
    public class DebugText extends TextField
    {
        private var $displayLabel:Boolean;
        
        public function DebugText(parent:DisplayObjectContainer, showBackground:Boolean = false, displayLabel:Boolean = true, select:Boolean = false) 
        {
            $displayLabel = displayLabel;
            
            this.autoSize = TextFieldAutoSize.LEFT;
            this.selectable = select;
            this.mouseEnabled = select;
            this.background = showBackground;
            this.backgroundColor = 0xEEEEEE;
            this.defaultTextFormat = new TextFormat("_sans", 12, 0x000000);
            this.visible = false; // to avoid displaying a empty square when the textField is empty
                        
            parent.addChild(this);
        };
        
        public function msg(str:*, label:String = "Info"):void
        {
            this.htmlText = ($displayLabel) ? "<b>"+label+"</b>: "+str : String(str);
            this.visible = true;
        };
        
    } // end class
}