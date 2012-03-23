package com.speedzinemedia.smt.charts {

  import flash.display.Shape;
	import flash.display.Sprite;
	import flash.display.Stage;
	import flash.events.Event;
	import flash.events.MouseEvent;
	
  import com.bit101.components.HUISlider;
  import com.bit101.components.VUISlider;
  import com.bit101.components.PushButton;
  import com.speedzinemedia.smt.display.Layers;
  import com.speedzinemedia.smt.utils.Maths;
  import net.badimon.five3D.display.DynamicText3D;
	import net.badimon.five3D.display.Scene3D;
	import net.badimon.five3D.display.Shape3D;
	import net.badimon.five3D.display.Sprite3D;
	import net.badimon.five3D.utils.DrawingUtils;

	public class Plot3D extends Sprite {

    private var $data:Array;                  // path(s) to draw
    private var $width:Number,$height:Number;
		private var $scene:Scene3D;
		private var $sign:Sprite3D;
		private var $plot:Shape3D;
		private var $planeXY:PushButton;
    private var $planeYZ:PushButton;
    private var $planeXZ:PushButton;
    private var $slideY:VUISlider;
    private var $slideX:HUISlider;
    private var $slideZ:VUISlider;

		public function Plot3D(settings:Object) 
		{
      $data = settings.data;
            
      const offset:int = 5;
      var window:Object = $data[0].screen.viewport;
      // set graph size
      $width = window.width - 50;
      $height = window.height - 100;
      // set background layer
      var bg:Sprite = new Sprite();
      bg.graphics.beginFill(0xEEEEEE);
      bg.graphics.drawRect(offset,offset, $width + offset*2,$height + offset*2);
      bg.graphics.endFill();
      addChild(bg);      
            
			$scene = new Scene3D();
			$scene.x = 0;
			$scene.y = 0;
			addChild($scene);

			$sign = new Sprite3D();
			$scene.addChild($sign);
			
			$plot = new Shape3D();
      // TODO: move to  drawAxes method
      const size:int = 200;
      $plot.graphics3D.lineStyle(3, 0xFF0000);
      $plot.graphics3D.moveToSpace(0,0,0);
      $plot.graphics3D.lineToSpace(size,0,0);
      $plot.graphics3D.lineStyle(3, 0x00FF00);
      $plot.graphics3D.moveToSpace(0,0,0);
      $plot.graphics3D.lineToSpace(0,size,0);
      $plot.graphics3D.lineStyle(3, 0x0000FF);
      $plot.graphics3D.moveToSpace(0,0,0);
      $plot.graphics3D.lineToSpace(0,0,size);

      $slideY = new VUISlider(this, 0,0, "Y", rotateAxisY);
      $slideY.setSliderParams(-180,180,0); // min, max, start value
      $slideY.labelPrecision = 0;
      $slideY.height = $height/2;
      $slideY.x = offset;
      $slideY.y = ($height - $slideY.height)/2;
                  
      $slideX = new HUISlider(this, 0,0, "X", rotateAxisX);
      $slideX.setSliderParams(-180,180,0);
      $slideX.labelPrecision = 0;
      $slideX.width = $width/2;
      $slideX.x = ($width - $slideX.width)/2;
      $slideX.y = $height - offset; //- $slideX.height;
                  
      $slideZ = new VUISlider(this, 0,0, "Z", rotateAxisZ);
      $slideZ.setSliderParams(-180,180,0);
      $slideZ.labelPrecision = 0;
      $slideZ.height = $height/2;
      $slideZ.x = $width - offset;//- $slideZ.width;
      $slideZ.y = ($height - $slideY.height)/2;

      var commands:Sprite = new Sprite();
      addChild(commands);
      $planeYZ = new PushButton(commands, 0,10, "YZ", setYZPlane);
      $planeXZ = new PushButton(commands, 110,10, "XZ", setXZPlane);
      $planeXY = new PushButton(commands, 220,10, "XY", setXYPlane);
      var zoom:HUISlider = new HUISlider(commands, 350,10, "zoom", onZoom);
      zoom.setSliderParams(0,10000,0);
      zoom.labelPrecision = 0;
      zoom.tick = 10;
                  
      commands.x = Math.round( ($width - commands.width - 100)/2 );
                  
      bg.addEventListener(MouseEvent.MOUSE_UP, onMouseUp);
      bg.addEventListener(MouseEvent.MOUSE_DOWN, onMouseDown);
                  
      $sign.x = $width/2;
      $sign.y = $height/2;
      $sign.addChild($plot);
                  
      for (var i:int = 0; i < $data.length; ++i) {
        drawPlot($data[i]);
      }
		};

    private function drawPlot(info:Object):void
    {
      var coordsX:Array = info.activity.coords.x;
      var coordsY:Array = info.activity.coords.y;

      var num:Number = coordsX.length - 1;
      var count:Number = 0;
      var lineThick:int = (info.avg) ? 3 : 0;
      var lineColor:int = (info.color) ? info.color : Layers.getColor(Layers.id.PATH);

      $plot.graphics3D.lineStyle(lineThick, lineColor);
      $plot.graphics3D.moveToSpace(coordsX[0], coordsY[0], 0);
      while (count < num) {
        // draw mouse path
        $plot.graphics3D.lineToSpace(coordsX[count], coordsY[count], count*10);
        // go to next coord
        ++count;
      }
    };
        
    private function setYZPlane(e:MouseEvent):void
    {
      $sign.rotationX = $slideX.value = 90;
      $sign.rotationY = $slideY.value = 90;
      $sign.rotationZ = $slideZ.value = 90;
    };
    
    private function setXZPlane(e:MouseEvent):void
    {
      $sign.rotationX = $slideX.value = 90;
      $sign.rotationY = $slideY.value = 0;
      $sign.rotationZ = $slideZ.value = 90;
    };
    
    private function setXYPlane(e:MouseEvent):void
    {
      $sign.rotationX = $slideX.value = 0;
      $sign.rotationY = $slideY.value = 0;
      $sign.rotationZ = $slideZ.value = 0;
    };
        
    private function onZoom(e:Event):void
    {
      $sign.z = e.target.value;
      $sign.x = $width/2;
      $sign.y = $height/2;
    };
        
    private function onMouseUp(e:MouseEvent):void
    {
      $scene.stopDrag();
    };
        
    private function onMouseDown(e:MouseEvent):void
    {
      if (e.target is Sprite) $scene.startDrag();
    };

    private function rotateAxisX(e:Event):void
    {
      $sign.rotationX = e.target.value;
    };
        
    private function rotateAxisY(e:Event):void
    {
      $sign.rotationY = e.target.value;
    };
        
    private function rotateAxisZ(e:Event):void
    {
      $sign.rotationZ = e.target.value;
    };
        
    private function drawAxes():void
    {
    };
    
	}// end class
}
