/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.charts {

    import flash.display.Shape;
    import flash.display.Sprite;
    import flash.geom.Point;
import flash.external.ExternalInterface;
    import caurina.transitions.Tweener;
    
    import com.speedzinemedia.smt.display.Layers;
    import com.speedzinemedia.smt.draw.Arrow;
    import com.speedzinemedia.smt.text.DebugText;
    import com.speedzinemedia.smt.utils.Maths;
    
    public class TimeChart extends Sprite 
    {
        public static const TYPE_HORIZONTAL:String = "horizontal";
        public static const TYPE_VERTICAL:String = "vertical";
        
        private var $data:Array;                        // path(s) to draw
        private var $type:String;                       // chart type
        private var $label:String;                      // chart title
        private var $width:Number,$height:Number;       // chart size
        private var $canvas:Sprite;                     // container canvas
        private var $chart:Sprite;                      // chart canvas

        public function TimeChart(settings:Object) 
        {
            /*
            // settings example
            var mySettings:Object = {
                data:  {
                    activity: activity,
                    screen:   screen,
                    color:    m.color,
                    avg:      u[i].avg
                },
                type:  chartType,
                label: chartType.toUpperCase() + " mouse coordinates vs. Time",
                size: {
                    width: $info.screen.viewport.width,
                    height: $info.screen.viewport.height/2
                }
            };
            */
            
            // set values from constructor
            $data  = settings.data;
            $type  = settings.type;
            $label = settings.label;
            
            init();
        };
        
        private function init():void
        {   
            // set graph size
            $width  = $data[0].screen.viewport.width - 100;
            $height = $data[0].screen.viewport.height/2;
            
            const OFFSET:int = 10;
            // set background layer
            var bg:Shape = new Shape();
            bg.graphics.beginFill(0xFFFFFF);
            bg.graphics.drawRect(-OFFSET,-OFFSET, $width + OFFSET*2,$height + OFFSET*2);
            bg.graphics.endFill();
            // draw bounding box
            var lines:Shape = new Shape();
            lines.graphics.lineStyle(0,0xDDDDDD);
            lines.graphics.drawRect(0,0, $width,$height);
            // origin arrows
            const oIni:Point = new Point(0,0);
            const oEnd1:Point = new Point(0,50);
            const oEnd2:Point = new Point(150,0);
            const arrow1:Arrow = new Arrow(oIni, oEnd1, true);
            const arrow2:Arrow = new Arrow(oIni, oEnd2, true);
            lines.graphics.lineStyle(0,0x000000);
            lines.graphics.moveTo(oIni.x, oIni.y);
            lines.graphics.lineTo(oEnd1.x, oEnd1.y);
            lines.graphics.moveTo(oIni.x, oIni.y);
            lines.graphics.lineTo(oEnd2.x, oEnd2.y);
            // init canvas layers
            $canvas = new Sprite();
            $chart = new Sprite();
            // create display list
            $canvas.addChild(bg);
            $canvas.addChild(lines);
            $canvas.addChild($chart);
            $canvas.addChild(arrow1);
            $canvas.addChild(arrow2);
            addChild($canvas);
            // label the origin first
            var origin:DebugText = new DebugText($canvas, false, false);
            origin.msg(" X: Time \n Y: Coordinates");
            // draw graph later
            for (var i:int = 0; i < $data.length; ++i) {
                drawChart($data[i]);
            }
            // then add the title (we need to measure the full $canvas)
            var title:DebugText = new DebugText($canvas, false, false, 0xFFFFFF);
            title.msg($label);
            title.x = ($canvas.width * $canvas.scaleX)/2 - title.width/2;
            title.y = $canvas.height - title.height/2;
            
            //resetScale();
        };
            
        private function drawChart(info:Object):void
        {            
            var points:Array = ($type == TYPE_VERTICAL) ? info.activity.coords.y : info.activity.coords.x;
            
            var num:int = points.length - 1;
            var step:Number = $width / num;
            // fit points in bounding box to easily compare mouse trails
            var normalize:Number = $height / Maths.arrayMax(points);
            var count:int = 0;
            
            var lineThick:int = (info.avg) ? 3 : 0;
            var lineColor:int = (info.color) ? info.color : Layers.getColor(Layers.id.PATH);
            var ini:Point,end:Point;
            while (count < num) {
                ini = new Point(count,   points[count]);
                end = new Point(count+1, points[count+1]);
                // draw mouse path
                $chart.graphics.lineStyle(lineThick, lineColor);
                $chart.graphics.moveTo(ini.x * step, ini.y * normalize);
                $chart.graphics.lineTo(end.x * step, end.y * normalize);
                // go to next point
                ++count;
            }
        };
        
        private function resetScale():void 
        {
            var sx:Number = stage.stageWidth / $canvas.width - .1;
            var sy:Number = stage.stageHeight / $canvas.height - .1;
            $canvas.scaleX = $canvas.scaleY = Math.min(sx,sy); 
        };
        
    } // end class
}
