
/*
 * Author: Adam Randlett
 * adam@randlett.net
 * Tipping Media LLC
 *
*/

EVRP.erw = (function ($, erw, window, document, undefined){

    var _body = $('body');
    erw.Widget = {};

    erw.Widget.labels = {
        "everytext":[
            "days",
            "weeks",
            "months",
            "years"
        ]
    }



    erw.Widget.init = function(){
        var ruleInp = $("[data-rrule]");
        erw.Widget.events();
    };



    /**
     * START DATE METHODS.
     */

    // Sets the Start On day from the event Start Date
    erw.Widget.setStartsOn = function () {
        $("#er_starts").attr("value",$("#fields-eventStartDate-date").val());
    };


    erw.Widget.getStartDate = function () {
        var sdate = document.querySelector("#fields-eventStartDate-date").value;
        var time = erw.Widget.convert12to24(document.querySelector("#fields-eventStartDate-time").value),
            timeArry = time.split(':'),
            dateArry = sdate.split('/'),
            start = new Date(dateArry[2],dateArry[0]-1,dateArry[1],timeArry[0],timeArry[1],"00"); // used Date.UTC

        // If no start date use today formatted M/D/YYYY
        if(start === ""){
            var date = new Date();
            start = erw.formatDate(date);
        }

        return start;
    };


    erw.Widget.convert12to24 = function (timeStr) {
        var meridian = timeStr.substr(timeStr.length-2).toLowerCase();;
        var hours =  timeStr.substr(0, timeStr.indexOf(':'));
        var minutes = timeStr.substring(timeStr.indexOf(':')+1, timeStr.indexOf(' '));
        if (meridian=='pm')
        {
            if (hours!=12)
            {
                hours=hours*1+12;
            }
            else
            {
                hours = (minutes!='00') ? '0' : '24' ;
            }
        }

        return hours+':'+minutes;
    }

    //Format Date 08/14/2014
    erw.Widget.formatDate = function (date) {
        var day = date.getDate(),
                month = date.getMonth() + 1,
                year = date.getFullYear();
        return start = month + "/" + day + "/" + year;
    };


    /**
     * EVENTS.
     */

    erw.Widget.events = function () {

        var datesSaved = false,
            savedValues = {
                startTime: '',
                endTime: ''
            };

        // Done button click
        _body.on("click",".venti_modal-done", function (evt) {
            evt.preventDefault();
            var parentModal = $(this).parents('.venti_modal');
            $("[data-events-edit]").show();
            erw.Widget.done(parentModal);
        });


        // Ends On Radio Set click
        _body.on("click",".evrp_modal [type=radio], .evrp_modal [type=checkbox]", function () {
            var $this = $(this);
            if($this.is('[name*=endsOn]')){
                erw.Widget.repeatEnds($this);
            }
        });

        $('.evrp_field').on('click','input[name*=allDay]', function (evt) {
            var $this = $(this),
                parent = $(evt.delegateTarget),
                startDateTime = parent.find('#fields-eventStartDate-time'),
                endDateTime = parent.find('#fields-eventEndDate-time');

            if ($this.is(':checked')) {
                parent.addClass('allDay');
                if (!datesSaved) {
                    savedValues.startTime = startDateTime.val();
                    savedValues.endTime = endDateTime.val();
                    startDateTime.val("12:00 AM");
                    endDateTime.val("12:59 PM");
                    datesSaved = true;
                }else{
                    startDateTime.val("12:00 AM");
                    endDateTime.val("12:59 PM");
                }
            }else{
                parent.removeClass('allDay');
                if(datesSaved){
                    startDateTime.val(savedValues.startTime);
                    endDateTime.val(savedValues.endTime);
                };
            };
            
        });

        _body.on("change",".evrp_modal select, .evrp_modal input", function (ev) {
            var $this = $(ev.currentTarget),
                el = ev.currentTarget;

            //Ends Radio Buttons and Associated Inputs
            if(el.classList.contains('venti-frequency--select')){
                var value = (parseInt(el.value) + 1);
                $('.evrp_modal').attr('data-state', value);
            }
        });


        _body.on('click','.venti_modal_tabs a',erw.Widget.tabs);

        /*
         * On Exclude/Include Date change highlight add button.
         */
        _body.on('change keyup','.venti_datefield', function (evt) {

            var parentTab = $(this).parents('.venti_modal_tab_content'),
                addDate = parentTab.find('.venti_adddate');
            if ($(this).val() != "") {
                addDate.addClass('ready');
            } else {
                addDate.removeClass('ready');
            }
        });


        _body.on('click','.venti_adddate', function (evt) {
            var parentTab = $(this).parent(),
                input = parentTab.find('.venti_datefield'),
                date = input.val(),
                tempClass = parentTab.data('element-template'),
                temp = parentTab.find(tempClass).text(),
                output = parentTab.find('.venti_elements'),
                element = $(temp);

            if(date.trim() != ""){
                element.find("input").attr("value",date);
                element.find(".title").append(date);
                output.append(element);
                input.val('');
                $(this).removeClass('ready');
            }
        });

        _body.on('click','.venti_elements .delete',function () {
            var $this = $(this);
            $this.parent().fadeOut( function (){
                $(this).remove();
            });
        });


        /**
        * Observe change in Start Date input to set End Date as same if there is not value.
        */

        var intID,
            startDateInputField = $('#fields-eventStartDate-date'),
            endDateInputField = $('#fields-eventEndDate-date');

        function ObserveStartDateInput() {
            if (endDateInputField.val() == "") {
                endDateInputField.val(startDateInputField.val());
                window.clearInterval(intID);
            };
        }

        startDateInputField.on('focusin',function(){
            intID = window.setInterval(function() { ObserveStartDateInput(); }, 100);
        });

        startDateInputField.on('focusout',function(){
            window.clearInterval(intID);
        });

    };



    erw.Widget.tabs = function(e){
        var $this = $(this),
            parentNav = $this.parents('.venti_modal_tabs'),
            id = $this.attr("href");

            parentNav.find(".sel").removeClass('sel');
            $this.addClass('sel');
            $(id).siblings().hide();
            $(id).show();
            e.preventDefault();
    };

    erw.Widget.repeatEnds = function(elm){
        var $this = $(elm);

        if($this.is("#er_endsnever")){
            $("#er_endson_rdio").next('label').find("input").attr("disabled","disabled");
            $("#er_endsafter").next('label').find("input").attr("disabled","disabled");
        }

        if($this.is("#er_endsafter")){
            $this.next("label").find('input').removeAttr('disabled');
            $("#er_endson_rdio").next('label').find("input").attr("disabled","disabled");
        }

        if($this.is("#er_endson_rdio")){
            $this.next("label").find('input').removeAttr('disabled');
            $("#er_endsafter").next('label').find("input").attr("disabled","disabled");
        }
    };


    /**
     * SUMMARY TEXT & HIDDEN INPUTS.
     */

    erw.Widget.setSummary = function(txt){
        var text = txt ? txt.capitalize() : txt;
            
        $(".evrp_summary,.rrule_human_text").html(text);
        $("[data-summary]").attr("value",text);
        if($("[data-events-edit]:hidden")){
            $("[data-events-edit]:hidden").show();
        }
    };



    /**
     * CLEAR SUMMARY
     */

    erw.Widget.clearSummary = function(){
        // clears rRule & summary hidden input as well as text holders next
        // too repeat checkbox and event modal summary box.
        $(".evrp_summary,.rrule_human_text").html("");
        $("[data-summary],[data-rrule]").attr("value","");
        $("#fields-rRule").attr("data-rule-string","");

        if($("[data-events-edit]:visible")){
            $("[data-events-edit]:visible").hide();
        }

    };


    // Convert date back to original format (8/25/2014) from 20140825T000000Z
    erw.strToDateUTC = function(date){
        var re = /^(\d{4})(\d{2})(\d{2})(T(\d{2})(\d{2})(\d{2})Z?)?$/;
        var bits = re.exec(date),
                year = "",
                month = "",
                day = "";
        if (!bits) {
                throw new Error('Invalid DATE value: ' + date)
        }
        year = bits[1];

        if(bits[2].charAt(0) === "0"){
            month = bits[2].replace("0","");
        }else{
            month = bits[2];
        }
        if(bits[3].charAt(0) === "0"){
            day = bits[3].replace("0","");
        }else{
            day = bits[3];
        }
        var datetxt = month + "/" + day + "/" + year;
        return datetxt;
    };




    erw.stringToDate = function(until){
        // Barrowed from RRULE.js to reconvert datestring: Dateutil.untilStringToDate
        var re = /^(\d{4})(\d{2})(\d{2})(T(\d{2})(\d{2})(\d{2})Z)?$/;
        var bits = re.exec(until);
        if (!bits) {
            throw new Error('Invalid UNTIL value: ' + until)
        }
        return new Date(
            Date.UTC(
                bits[1],
                bits[2] - 1,
                bits[3],
                bits[5] || 0,
                bits[6] || 0,
                bits[7] || 0
            )
        );
    }




    /**
     * DONE.
     */

    erw.Widget.done = function (modal) {
        var formData = modal.find(".evrp_modal-form").serialize();
        Craft.postActionRequest('venti/ajax/getRuleString', formData, function(data){
            erw.Widget.saveRuleOptions(data);
        });
    };



    erw.Widget.saveRuleOptions = function(response){
        if(response){
            $("[data-rrule]").attr("value",response.rrule);
            erw.Widget.setSummary(response.readable);
            erw.Modal.close();
        }
    };


    erw.objectsAreSame = function(x, y) {
        var objectsAreSame = true;
        for(var propertyName in x) {
            if(x[propertyName] !== y[propertyName]) {
                 objectsAreSame = false;
                 break;
            }
        }
        return objectsAreSame;
    }

    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }


    erw.Widget.init();
    return erw;

}($, EVRP || {}, this, this.document));
