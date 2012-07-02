/**
 * Time methods.
 * @author  Luis Leiva
 * @date    2 May 2010
 */
package com.speedzinemedia.smt.utils {

    public class TimeUtils
    {

        /** Pads a number with zeros. */
        public static function zeroPad(number:Number, zeros:int = 2):String
        {
            var format:String = String(number);
            while (format.length < zeros) {
              format = "0" + format;
            }

            return format;
        };

        /** Converts milliseconds to SMPTE timestamps (##:##). */
        public static function timeToSMPTE(ms:int):String
        {
            if (ms < 0) ms = 0;
            
            var hours:int   = Math.floor(  ms / (1000*60*60) );
            var minutes:int = Math.floor( (ms % (1000*60*60)) / (1000*60) );
            var seconds:int = Math.floor( ((ms % (1000*60*60)) % (1000*60)) / 1000 );

            return TimeUtils.zeroPad(minutes) + ':' + TimeUtils.zeroPad(seconds);
        };

        /** Converts SMPTE timestamps (##:##) to milliseconds. */
        public static function SMPTEToTime(SMPTE:String):int
        {
            var time:Array = SMPTE.split(':');
            var minutes:int = int(time[0]);
            var seconds:int = int(time[1]);

            return 1000 * (seconds + minutes * 60);
        };

    } // end class
}