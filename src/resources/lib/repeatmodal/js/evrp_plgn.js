// JSLint settings:
/*global
    clearTimeout,
    console,
    jQuery,
    setTimeout
*/

/*
 * Author: Adam Randlett
 * adam@tippingmedia.com
 *
 *
*/




var EVRP = (function($, evrp, window, document, undefined){

        evrp.Helpers = {};
        evrp.Ajax    = {};
        evrp.Cms     = {};


        /**
         * EVRP VARIABLES.
         */

        var _html = $("html"),
            _body = $('body');


        /* --------------------------------------------- *\
                HELPERS.
        \*  -------------------------------------------- */

        evrp.Helpers.is_int = function(value){
            if((parseFloat(value) == parseInt(value)) && !isNaN(value)){
                return true;
            } else {
                return false;
            }
        }


        // If you use console when IE doesn't have the "F12"
        // tools open, throws a "console not defined" error.
        evrp.log = function() {
            // Safely log things, if need be.
            if (console && typeof console.log === 'function') {
                for (var i = 0, ii = arguments.length; i < ii; i++) {
                    console.log(arguments[i]);
                }
            }
        };


        //evrp.log("EVRP --");
        return evrp;

// jQuery, evrp, window, document, undefined
}(jQuery, EVRP || {}, this, this.document));
