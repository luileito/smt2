package com.speedzinemedia.smt.utils {

   import flash.display.Sprite;
   import flash.events.Event;
   import flash.text.TextField;
   import flash.text.TextFieldAutoSize;
   import flash.text.TextFormat;
   import flash.utils.getTimer;

   /** 
    * Generic FPS-meter.
    * @author   kaioa, Luis Leiva
    * @date     2009 Jan 20
    */
   public class FPSMeter extends TextField
   {
      private var last:uint = getTimer();
      private var ticks:uint = 0;

      /** Constructor. */		
      public function FPSMeter() 
      {    
         var fmt:TextFormat = new TextFormat();
         fmt.color   = 0x000000;
         fmt.size	   = 11;
         fmt.font    = "_sans";

         this.selectable         = false;
         this.background         = true;
         this.backgroundColor    = 0xEEEEEE;
         this.autoSize           = TextFieldAutoSize.LEFT;
         this.defaultTextFormat  = fmt;
         this.text               = "24.0 fps";

         addEventListener(Event.ENTER_FRAME, tick);
      };

      /**
       * Tick function.
       * @param e  onEnterFrame event listener         
       */
      public function tick(e:Event):void 
      {
         ticks++;
         var now:uint   = getTimer();
         var delta:uint = now - last;
         // update values each 100 ms
         if (delta >= 100) {
            var fps:Number = ticks / delta * 1000;
            this.text = fps.toFixed(1) + " fps";

            ticks = 0;
            last = now;
         }
      };

   } // end class
}
