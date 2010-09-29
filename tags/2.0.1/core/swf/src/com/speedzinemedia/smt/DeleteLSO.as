package {

    import flash.net.SharedObject;
    import flash.display.Sprite;

    // set custom properties
    [SWF(backgroundColor="#FFFFFF")]

    /**
     *  This class deletes the (smt) SWF saved settings.
     *  @autor Luis Leiva
     *  @version    2.0.1
     *  @date 16 January 2010
     */
    public class DeleteLSO extends Sprite {
    
        public function DeleteLSO()
        {
            var lso:SharedObject = SharedObject.getLocal("smt-controlPanel", "/");
            lso.clear();
        };

    } // end class
}
