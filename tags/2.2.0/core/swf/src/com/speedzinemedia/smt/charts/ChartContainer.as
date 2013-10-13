/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.charts {
    
    import flash.display.DisplayObject;
    import flash.display.DisplayObjectContainer;
    import com.speedzinemedia.smt.charts.*;
    import com.speedzinemedia.smt.modal.ModalWindow;
    
    public class ChartContainer extends ModalWindow
    {
        public function ChartContainer(parent:DisplayObjectContainer, settings:Object, chartClass:Object)
        {
            var chart:DisplayObject = new chartClass(settings);
            super.addChild(chart);
            super.show(parent, this);  
        };
        
    } // end class
}
