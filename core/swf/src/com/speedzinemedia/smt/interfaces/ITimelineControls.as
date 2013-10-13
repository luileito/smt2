package com.speedzinemedia.smt.interfaces {
  /**
   * Defines the basic interface for replay methods.
   * @author   Luis Leiva
   * @date     01 May 2010
   */
	public interface ITimelineControls
	{
	
	   function pause():void;
	   function stop():void;
	   function resume():void;
	   function restart():void;
	   function finish():void;
     function seek(perc:Number):void;	   
	   
  } // end interface
}
