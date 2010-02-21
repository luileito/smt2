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
        public function DebugText(parent:DisplayObjectContainer, showBackground:Boolean = false, select:Boolean = false, color:uint = 0x000000)
        {
            this.autoSize = TextFieldAutoSize.LEFT;
            this.selectable = select;
            this.mouseEnabled = select;
            this.background = showBackground;
            this.backgroundColor = 0xEEEEEE;
            this.defaultTextFormat = new TextFormat("_sans", 14, color);
            this.visible = false; // avoid displaying a empty square when the textField is empty
                        
            parent.addChild(this);
        };
        
        public function msg(str:*, label:String = ""):void
        {
            this.htmlText = (label) ? "<b>"+label+"</b>: "+str : String(str);
            this.visible = true;
        };
        
    } // end class
}