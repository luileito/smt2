/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.draw {
    
    import flash.display.Shape;
    import flash.geom.Matrix;
    import flash.geom.Point;
    
    import com.speedzinemedia.smt.utils.Utils;

    public class Arrow extends Shape 
    {

        public function Arrow(ini:Point, end:Point, filled:Boolean = false, lineSytle:int = 0, head:int = 5, color:uint = 0x000000) 
        {
            // draw arrow first
            if (filled) { 
                graphics.beginFill(color); 
            }
            graphics.lineStyle(lineSytle, color);
            graphics.moveTo(end.x, end.y);
            graphics.lineTo(end.x - head, end.y - head);
            graphics.moveTo(end.x, end.y);
            graphics.lineTo(end.x - head, end.y + head);
            if (filled) { 
                graphics.lineTo(end.x - head, end.y - head); 
                graphics.endFill(); 
            }
            
            // then rotate around center
            const theta:Number = Utils.angle(ini, end);
            var m:Matrix = transform.matrix;
            m.tx -= end.x;  // displace first
            m.ty -= end.y;
            m.scale(2,1);   // scale arrow head
            m.rotate(theta);
            m.tx += end.x;  // place later
            m.ty += end.y;
            transform.matrix = m;
        };

    } // end class
}
