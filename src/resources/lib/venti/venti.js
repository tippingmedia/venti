(function() {
    // allow for custom events.
    function CustomEvent ( event, params ) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent( 'CustomEvent' );
        evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
        return evt;
    }

    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;

    Object.defineProperty(Object.prototype, "indexOfKey", {
      value: function(value) {
          var i = 1;
          for (var key in this){
            if (key == value){
              return i;
            }
            i++;
          }
          return undefined;
      }
    });

    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }

    $.fn.removeStyle = (function(){
        var rootStyle = document.documentElement.style;
        var remover =
            rootStyle.removeProperty    // modern browser
            || rootStyle.removeAttribute   // old browser (ie 6-8)
        return function removeInlineCss(property){
            if(property == null)
                return this.removeAttr('style');
            var proporties = property.split(/\s+/);
            return this.each(function(){
                for(var i = 0 ; i < proporties.length ; i++)
                    remover.call(this.style, proporties[i]);
            });
        };
    })();

    // Set Venti as window object.
    window.Venti = {};
    $.extend(Venti,{});

})();
