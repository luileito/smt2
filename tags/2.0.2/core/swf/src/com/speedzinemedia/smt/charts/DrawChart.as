/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.charts {
    
    import flash.display.DisplayObjectContainer;
	
    import com.speedzinemedia.smt.charts.TimeChart;
    import com.speedzinemedia.smt.modal.ModalWindow;
    import com.speedzinemedia.smt.text.DebugText;
    
    public class DrawChart extends ModalWindow 
    {           
        public function DrawChart(parent:DisplayObjectContainer, settings:Object) 
        {                        
            var chart:TimeChart = new TimeChart(settings);
            
            super.addChild(chart);

            super.show(parent, this);  
        };
        
    } // end class
}