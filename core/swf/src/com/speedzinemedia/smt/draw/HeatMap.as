/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.draw {

    import flash.display.Bitmap;
    import flash.display.BitmapData;
    import flash.display.Sprite;
    import flash.external.ExternalInterface;

    public class HeatMap extends Sprite
    {
        private var $bmp:Bitmap;
        private var $max:Number;
        
        public function HeatMap(layer:Sprite, scr:Object, maxColor:uint = 0xFFFFFF)
        {
            // copy layer pixels into bitmap
            var bmpd:BitmapData = new BitmapData(scr.currWindow.width, scr.currWindow.width, true, 0x00FFFFFF);
            bmpd.draw(layer);

            $bmp = new Bitmap(bmpd, "auto", true);
            layer.graphics.clear();
            layer.addChild($bmp);
            
            // use precomputed max color (less processor-intensive, avoid 1 pass)
            $max = maxColor as Number;

            create();
            //$bmp.bitmapData.dispose();
        };
        
        public function create():void
        {
            var i:int, j:int, pixel:int, min:Number;
            
            /*
            // find max value from gaussians
			for (i = 1; i <= $bmp.width; ++i)
            {
				for (j = 1; j <= $bmp.height; ++j)
                {
                    pixel = $bmp.bitmapData.getPixel32(i,j);
				    max = Math.max(max, (pixel >> 8) & 0xFFFFFF);
                }
            }
            */
            
            // colorize
			for (i = 1; i <= $bmp.width; ++i)
            {
                for (j = 1; j <= $bmp.height; ++j)
                {
				    pixel = $bmp.bitmapData.getPixel32(i,j);
					if (pixel == 0) continue;

                    $bmp.bitmapData.setPixel32(j, i, colorize(((pixel >> 8) & 0xFFFFFF) / $max));
				}
			}
        };
        
        /**
         * @author     Jordi Boggiano <j.boggiano@seld.be>
         * @copyright  2007 Jordi Boggiano
         * @license    http://creativecommons.org/licenses/by-nc-sa/3.0/  Creative Commons BY-NC-SA 3.0
         * @link       http://www.seld.be/
         * @version    1.0.0
         * @date       2007-12-16
         */
        protected function colorize(intensity:Number):uint
		{
			var alpha:int = Math.min(255 * intensity, 255);
			var temp:Number, r:int = 0, g:int = 0, b:int = 0;

			// blue
			if (intensity < 0.33) {
				b = 0;
			}
			else if (intensity >= 0.33 && intensity < 0.66) {
				temp = (intensity - 0.33) / 0.33;
				b = (1-temp) * 255;
			}
			else {
				b = 0;
			}

			// green
			if (intensity < 0.33) {
				temp = intensity / 0.33;
				g = temp * 255;
			}
			else if (intensity >= 0.33 && intensity < 0.66)	{
				g = 255;
			}
			else {
				temp = (intensity - 0.66) / 0.34;
				g = (1 - temp) * 255;
			}

			// red
			if (intensity < 0.33) {
				r = 0;
			}
			else if (intensity >= 0.33 && intensity < 0.66)	{
				temp = (intensity - 0.33) / 0.33;
				r = temp * 255;
			}
			else {
				r = 255;
			}

			return (alpha << 24) + (r << 16) + (g << 8) + b;
		};
		

    } // end class
}