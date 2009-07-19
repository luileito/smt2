/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt {
    
    public class Maths 
    {   
        public static function roundTo(num:Number, digits:int):Number
        {
            var precision:int = Math.pow(10, digits);
            
            return Math.round(num * precision)/precision;
        };
        
        public static function arrayMax(values:Array):Number
        {
        	var maxVal:Number = values[0];
        	for (var i:int = 0, vLength:int = values.length; i < vLength; ++i) {
        	   maxVal = (values[i] > maxVal) ? values[i] : maxVal;
        	}
        	
        	return maxVal;
        };
        
        public static function arrayMin(values:Array):Number
        {
        	var minVal:Number = values[0];
        	for (var i:int = 0, vLength:int = values.length; i < vLength; ++i) {
        	   minVal = (values[i] < minVal) ? values[i] : minVal;
        	}
        	
        	return minVal;
        };
        
        public static function arrayAvg(values:Array):Number
        {
        	var sum:Number = 0;
        	const N:int = values.length;
        	// do not call arrayCastNumbers to boost performance
        	for (var i:int = 0; i < N; ++i) { 
                sum += Number(values[i]); 
            }
        	
        	return sum/N;
        };
        
        public static function arrayCastNumbers(values:Array):Array
        {
            var tmp:Array = new Array();
        	// Number casting is needed because each array member parsed from Flash vars is a String instance
        	for (var i:int = 0, t:int = values.length; i < t; ++i) {
                tmp.push(Number(values[i])); 
            }
        	
        	return tmp;
        };
        
    } // end class
}