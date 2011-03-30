package com.speedzinemedia.smt.utils {
    
   import flash.system.Capabilities;
	
	/**
	 * Player info class.
	 * @autor 	Luis Leiva
	 * @date 	2009-May-27
	 */
    public class PlayerInfo
    {
        public static const OS_LINUX:String 	= "Linux";
		public static const OS_MAC:String 		= "Mac";
		public static const OS_WINDOWS:String 	= "Windows";
		
        /**
    	 * Parses the name of the Operating System.
    	 * @return the operating system's name: Windows, Mac, or Linux
    	 */
        public static function getPlatformName():String
    	{
			var osname:String;
            var osstring:String = Capabilities.os;
            var test:Array = [OS_LINUX, OS_MAC, OS_WINDOWS];
            for (var i:int = 0; i < test.length; ++i) {
                osname = test[i];
                if (osstring.indexOf(osname) != -1) { break; }
			}
			
            return osname;
    	};
		
		/** 
    	 * Parses the version of Flash Player.
    	 * @return the major and minor version numbers
    	 */
        public static function getPlayerVersion():Number
    	{
            // Capabilities.version returns something like "LNX 10,0,42,34"
            var fpInfo:Array = Capabilities.version.split(" ");
			var osId:String = fpInfo[0]; 		// not used, just for shake of clarity
			var verStr:String = fpInfo[1];	// our info
			
			var fpver:Array = verStr.split(",");
			var majorVersion:int = parseInt(fpver[0]);
			var minorVersion:int = parseInt(fpver[1]);
			//var buildNumber:int  = parseInt(fpver[2]);
			//var internalBuildNumber:int  = parseInt(fpver[3]);
			
            return (majorVersion + minorVersion / 10);
    	};
		
		/** 
    	 * Determines if the OS is Linux.
    	 * @return true on success or false on failure
    	 */
        public static function isLinux():Boolean
    	{
            return osTest(OS_LINUX);
    	};
		
		/** 
    	 * Determines if the OS is Mac.
    	 * @return true on success or false on failure
    	 */
        public static function isMac():Boolean
    	{
            return osTest(OS_MAC);
    	};
		
		/** 
    	 * Determines if the OS is Windows.
    	 * @return true on success or false on failure
    	 */
        public static function isWindows():Boolean
    	{
            return osTest(OS_WINDOWS);
    	};
		
		/** 
    	 * Test if the given OS name matches the current Player OS.
		 * @param name Operating System name.
    	 * @return true on success or false on failure
    	 */
		protected static function osTest(name:String):Boolean
    	{
            var osname:String = getPlatformName();
            
            return (Capabilities.os.indexOf(name) !== -1);
    	};
			
	} // end class
}