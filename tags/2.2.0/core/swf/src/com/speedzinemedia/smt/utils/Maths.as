/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.utils {
    
    public class Maths 
    {   
        public static function roundTo(num:Number, digits:int):Number
        {
            const precision:int = Math.pow(10, digits);
            
            return Math.round(num * precision)/precision;
        };
        
        // values can be Array or Vector
        public static function arrayMax(values:*):Number
        {
            const N:int = values.length;        
          	var maxVal:Number = values[0];
          	for (var i:int = 0; i < N; ++i) {
          	   maxVal = (values[i] > maxVal) ? values[i] : maxVal;
          	}
          	
          	return maxVal;
        };
        
        // values can be Array or Vector
        public static function arrayMin(values:*):Number
        {
            const N:int = values.length;
          	var minVal:Number = values[0];
          	for (var i:int = 0, vLength:int = values.length; i < vLength; ++i) {
          	   minVal = (values[i] < minVal) ? values[i] : minVal;
          	}
          	
          	return minVal;
        };
        
        // values can be Array or Vector
        public static function arrayAvg(values:*):Number
        {
          	const N:int = values.length;
          	var sum:Number = 0;
          	// do not call arrayCastNumbers to boost performance
          	for (var i:int = 0; i < N; ++i) { 
              sum += Number(values[i]); 
            }
          	
          	return sum/N;
        };
        
        public static function arrayCastNumbers(values:Array):Array
        {
            const N:int = values.length;
            var casted:Array = [];
          	// Number casting is needed because each array member parsed from Flash vars is a String instance
          	for (var i:int = 0; i < N; ++i) {
              casted.push( int(values[i]) );
            }
          	
          	return casted;
        };
        
        
    } // end class
}
