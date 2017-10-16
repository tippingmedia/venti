
/*
 * Author: Adam Randlett
 * adam@tippingmedia.com
 *
 *
*/

EVRP.rul = (function ($, rul, window, document, undefined){

    var _body = $('body');
    rul.RRule = {};
    rul.RRule.context = {
        "repeats": [
            "Every day",
            "Every weekday",
            "Every Monday, Wednesday, Friday",
            "Every Tuesday, Thursday",
            "Every week",
            "Every month",
            "Every year"
         ],
        "dow":{
            "MO":"Monday",
            "TU":"Tuesday",
            "WE":"Wednesday",
            "TH":"Thursday",
            "FR":"Friday",
            "SA":"Saturday",
            "SU":"Sunday"
        }
    };


    /**
     * RUL.RRULE INITIALIZE.
     */

    rul.RRule.init = function(){
        //rul.log("EVRP.RRULE.INIT --");
    };





    /**
     * RULE STRING.
        Generate Rule Text Based On Repeat
        Event Input Selections
     */

    rul.RRule.getRuleString = function(obj,callback){
        var pof = parseInt(obj["repeats"]),
                ruleString = [];

        try {

            if(pof === 0){ // Daily
                if(parseInt(obj["repeatsevery"]) === 1){
                    ruleString[0] = "Every day";
                }else{
                    ruleString[0] = "Every " + obj["repeatsevery"] + " days";
                }
            }

            if(pof === 1){ // Every Weekday
                ruleString[0] = "Every weekday";
            }

            if(pof === 2){ // Every week on Monday, Wednesday and Friday
                ruleString[0] = "Every week on Monday, Wednesday, Friday,";
            }

            if(pof === 3){ // Every week on Tuesday and Thursday
                ruleString[0] = "Every week on Tuesday, and Thursday,";
            }

            if(pof === 4){ // Weekly + days of weeks if selected
                var dowArry = obj["repeatson"].map(function(){
                    return rul.RRule.context["dow"][this];
                }).get().join(", ");

                if(dowArry.length > 0){
                    if(parseInt(obj["repeatsevery"]) > 1){
                        ruleString[0] = "Every " + obj["repeatsevery"] + " weeks on " + dowArry;  // Every 5 weeks on Monday, Wednesday, Saturday
                    }else{
                        ruleString[0] = "Every week on " + dowArry;  // Every week on Monday, Wednesday, Saturday
                    }
                }else{
                    ruleString[0] = "Every week"; // Every week
                }
            }

            if(pof === 5){ // Every month
                var sD = rul.Widget.getStartDate(); // sD = startDate

                if(parseInt(obj["repeatsby"]) === 0){ // Every month on the 17th
                    ruleString[0] = "Every month on the " + sD.getDate() + sD.getDaySuffix();
                }

                if(parseInt(obj["repeatsby"]) === 1){ // Every month on the fifth Friday
                    //console.log(sD.nthDay());
                    ruleString[0] = "Every month on the " + rul.getNthDaySuffix(sD.nthDay()) + " " + sD.dayOfWeek();
                }
            }

            if(pof === 6){ // Every year
                if(parseInt(obj["repeatsevery"]) === 1){
                    ruleString[0] = "Every year";
                }else{
                    ruleString[0] = "Every " + obj["repeatsevery"] + obj["repeatsevery"].getNumberSuffix()  + " year";  // Every 3rd year
                }
            }


            // Ends On by occurrence or date
            if(parseInt(obj["endson"]) > 0){
                var endson = parseInt(obj["endson"]);

                if(endson === 1 && obj["occur"] !== undefined){
                    ruleString[1] = "for " + obj["occur"] + " times";
                }

                if(endson === 2 && obj["endsondate"] !== undefined){
                    ruleString[1] = "until " + obj["endsondate"];
                }
            };

        response = ruleString.join(" ");
        callback(response);

        } catch(e) {
             callback(false);
        }

    };




    /**
     * RRULE OPTIONS.
        Generates RRULE.js Options Object.
     */

    rul.RRule.getRRuleOptions = function(rulestr){
        var ruleString = rulestr,
                options = RRule.parseText(ruleString);
        //setting dtstart was ruining everything.
        //options.dtstart = rul.Widget.getStartDate();
        var output = RRule.optionsToString(options);
        return output;
    };





    /**
     * RRULE DATES.
            Generates Array Of Dates Based On
            Repeat Date Criteria
     */

    rul.RRule.getRRuleInstances = function(rulestr,callback){
        try{
            var rule = new RRule(RRule.parseString(rulestr));
            response = rule.all();
            callback(response);
        } catch(e) {
            callback(false);
        }
        //console.log(rule.all());
    }





    /**
     * HELPERS.
     */

    Date.prototype.nthDay = function(){
        return Math.ceil(this.getDate()/7);
    };


    Date.prototype.getDaySuffix = function(utc) {
        var n = utc ? this.getUTCDate() : this.getDate();
        // If not the 11th and date ends at 1
        if (n != 11 && (n + '').match(/1$/)){
            return 'st';
        }
        // If not the 12th and date ends at 2
        else if (n != 12 && (n + '').match(/2$/)){
            return 'nd';
        }
        // If not the 13th and date ends at 3
        else if (n != 13 && (n + '').match(/3$/)){
            return 'rd';
        }
        else{
            return 'th';
        }
    };

    String.prototype.getNumberSuffix = function(){
        var n = parseInt(this);
        if (n != 11 && (n + '').match(/1$/)){
            return 'st';
        }
        // If not the 12th and date ends at 2
        else if (n != 12 && (n + '').match(/2$/)){
            return 'nd';
        }
        // If not the 13th and date ends at 3
        else if (n != 13 && (n + '').match(/3$/)){
            return 'rd';
        }
        else{
            return 'th';
        }
    }


    Date.prototype.dayOfWeek = function(){
        var dowArry = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
            ];

        return dowArry[this.getDay()];
    };


    rul.getNthDaySuffix = function(num){
        var suffix = [
            "1st",
            "2nd",
            "3rd",
            "4th",
            "5th"
        ],
        //subtract 1 to get proper item from suffix array;
        idx = parseInt(num) - 1;
        return suffix[idx];
    };


    // Parses 'FREQ=WEEKLY;DTSTART=20140124T070000Z;COUNT=10;BYDAY=MO,TU,WE,TH,FR' type string.

    rul.getRuleParams = function(str){
        if (str !== undefined) {
            var ret = {},
                seg = str.split(';'),
                len = seg.length, i = 0, s;
            for (;i<len;i++) {
                if (!seg[i]) { continue; }
                s = seg[i].split('=');
                ret[s[0]] = s[1];
                 
                if(ret['BYDAY']){
                    var idx = ret['BYDAY'].indexOf(',');
                    if(idx != -1){
                        ret['BYDAY'] = ret['BYDAY'].split(',');
                    }else{
                        ret['BYDAY'] = ret['BYDAY'];
                    }
                }
            }
            return ret;
        }else{
            return false;
        }
    };


    //rul.RRule.init();
    return rul;

}($, EVRP || {}, this, this.document));
