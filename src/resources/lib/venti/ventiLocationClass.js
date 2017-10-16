/**
 * Locations Class
 */

 class VentiLocation {

    constructor (options) {
        const $this = this;
        this._options = options ? options : {};
        this._fields = {
            findAddr:  document.querySelectorAll(".find-address-input")[0],
            address:   document.querySelectorAll(".loc-address-input")[0],
            city:      document.querySelectorAll(".loc-city-input")[0],
            state:     document.querySelectorAll(".loc-state-input")[0],
            zip:       document.querySelectorAll(".loc-zip-input")[0],
            lat:       document.querySelectorAll(".loc-lat-input")[0],
            lng:       document.querySelectorAll(".loc-lng-input")[0],
            country:   document.querySelectorAll(".loc-countries-input select")[0]
        };
        this._mapContainer = document.querySelectorAll('.map_container')[0];

        google.maps.event.addDomListener(window, 'load', function(){
            $this.initalize();
        });

        this.initMap();

    }

    get fields () {
        return this._fields;
    }

    set input (fields) {
        this._fields = fields;
    }

    initalize () {
        const $this = this;
        const address = this._fields.findAddr;
        const autocomplete = new google.maps.places.Autocomplete(address);
        autocomplete.setTypes(['geocode']);
        google.maps.event.addListener(autocomplete, "place_changed", function() {
            let place = autocomplete.getPlace();
            if (!place.geometry) {
                return;
            }

            let address = " ";
            if (place.address_components) {
                address = [
                    (place.address_components[0] && place.address_components[0].short_name || ""),
                    (place.address_components[1] && place.address_components[1].short_name || ""),
                    (place.address_components[2] && place.address_components[2].short_name || "")
                ].join(" ");
            }
        });

        // Prevent address autocomplete input from submitting form on return.
        address.addEventListener('keypress',function(e){
            if (e.keyCode == 13) {
               let src = e.srcElement || e.target;
               if (src.tagName.toLowerCase() != "textarea") {
                   e.stopPropagation();
                   if (e.preventDefault) {
                       e.preventDefault();
                   } else {
                       e.returnValue = false;
                   }
               }
           }
        });


        autocomplete.addListener('place_changed', function (evt) {
            $this.codeAddress(evt, $this);
            $this.fillAddress(evt, $this, autocomplete);
        });
    }

    codeAddress(evt, clas) {
        const $this = clas;
        let geocoder = new google.maps.Geocoder();
        let findAddrValue = ($this._fields.findAddr).value;
        geocoder.geocode({ 'address': findAddrValue }, function(results, status) {
            if (status === google.maps.GeocoderStatus.OK) {

                $this._fields.lat.value = results[0].geometry.location.lat();
                $this._fields.lng.value = results[0].geometry.location.lng();
                $this.loadMap(results[0].geometry.location.lat(), results[0].geometry.location.lng());
            }
            else {
                //alert("Geocode was not successful for the following reason: " + status);
            }
        });
    }

    fillAddress(evt,clas, autocomplete) {
         let $this = clas;
         let place = autocomplete.getPlace();
         let address = "";

        for (let i = 0; i < place.address_components.length; i++) {
            let addressType = place.address_components[i].types[0];
            let fld = $this._fields;

            switch (addressType) {
                case 'street_number':
                    address += place.address_components[i]['short_name'];
                    break;
                case 'route':
                    address += " " + place.address_components[i]['long_name'];
                    fld.address.value = address;
                    break;
                case 'locality':
                    fld.city.value = place.address_components[i]['long_name'];
                    break;
                case 'administrative_area_level_1':
                    fld.state.value = place.address_components[i]['short_name'];
                    break;
                case 'postal_code':
                    fld.zip.value = place.address_components[i]['short_name'];
                    break;
                case 'country':
                    let select = fld.country;
                    for(let option in select.options) {
                        if(select.options[option].value === place.address_components[i]['short_name']) {
                            select.options[option].selected = true;
                        }
                    }
                    break;
            }
        }
    }

    getFullAddress()
    {
        let addressDict = [
            this._fields.address.value,
            this._fields.city.value,
            this._fields.state.value,
            this._fields.zip.value];

        return addressDict.join(" ");
    }


    initMap()
    {
        let $this = this;

        if(this._fields.lat.value !== "" && this._fields.lng.value !== ""){
            this.loadMap(parseFloat(this._fields.lat.value),parseFloat(this._fields.lng.value));
        }
    }

    loadMap(lat,lng)
    {
        let locLatLng = {lat: lat, lng: lng};
        let map = new google.maps.Map(this._mapContainer, {
          center: locLatLng,
          disableDefaultUI: true,
          zoom: 15
        });
        let marker = new google.maps.Marker({
          position: locLatLng,
          map: map,
        });
    }

 }
