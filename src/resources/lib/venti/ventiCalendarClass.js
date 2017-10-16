/**
 * Venti Calendar Class
 */

class VentiCalendar {
    constructor (options) {
        var $this = this;
        this._id = options.id;
        this._params = options.params;
        this._input  = document.getElementById(options.id);
        this._tooltip = null;
        this._cal = null;
        this._localebtn = null;
        this._locale  = Craft.getLocalStorage('BaseElementIndex.locale') ? Craft.getLocalStorage('BaseElementIndex.locale') : "en_us";
        this._sources = null;
        this._localized = options.params.localized;
        this._alertModal = null;
        this._editLocales = options.params.editLocales;
        this._cpLanguage = options.params.cpLanguage ? this.mapLocales(options.params.cpLanguage) : "en";
        //FullCalendar default settings
        this._defaults = {
            customButtons: {
                localeSelectButton: {
                    text: this._params.locales[0].title,
                    click: function(evt) {
                        if($this._localebtn.data("menu") != "true") {
                            var $menu = $('<div class="menu" data-align="right"></div>').insertAfter(evt.currentTarget),
			                    $menuList = $('<ul></ul>').appendTo($menu),
                                selOps = options.params.locales;
                            for (var i = 0; i < selOps.length; i++)
                    		{
                                if(options.params.editLocales[selOps[i].handle]){
                    			    $('<li><a data-value="'+selOps[i].handle+'">'+selOps[i].title+'</a></li>').appendTo($menuList);
                                }
                    		}
                            //console.log(options.params.locales);
                            (new Garnish.MenuBtn(evt.currentTarget,{onOptionSelect: $.proxy($this,"onLocaleChange",evt.currentTarget)}).showMenu());

                            $this._localebtn.data("menu","true");

                        }
                    }
                },
                groupsToggleButton: {
                    text: Craft.t("Groups"),
                    click : $.proxy(this, "groupToggles")
                }
            },
            header: {
                left: 'title',
                center: '',
                right: this._localized === "true" ? 'localeSelectButton groupsToggleButton today prev,next ' : 'groupsToggleButton today prev,next'
            },
            viewRender: $.proxy(this,"viewRender"),
            editable: true,
            eventClick: $.proxy(this, "onEventAction"),
            eventDrop: $.proxy(this, "updateEventDates"),
            dayClick: $.proxy(function() {  }),
		    eventResizeStart: $.proxy(function() {  }),
		    eventDragStart: $.proxy(function() {  }),
            //eventMouseover: $.proxy(this, "onEventAction"),
            //eventMouseout: $.proxy(this, "onMouseout"),
            eventRender: $.proxy(this,"renderEvent"),
            lang: this._cpLanguage,
            eventLimit: 6,

        };

        // Initialize Calendar
        this.initCalendar();
    }

    get input () {
        return this._input;
    }

    get id () {
        return this._id;
    }

    get params () {
        return this._params;
    }

    get defaults () {
        return this._defaults;
    }

    get cal () {
        return this._cal;
    }

    set cal (cal) {
        this._cal = cal;
    }

    get sources () {
        return this._sources;
    }

    set sources (sources) {
        this._sources = sources;
    }

    get localebtn (){
        return this._localebtn;
    }

    set localebtn (btn) {
        this._localebtn = btn;
    }

    set locale (locale) {
        this._locale = locale;
    }

    get locale (){
        return this._locale;
    }

    set localized (loc) {
        this._localized = loc;
    }

    get localized () {
         return this._localized;
    }

    set alertModal (modal) {
        this._alertModal = modal;
    }

    get alertModal () {
         return this._alertModal;
    }

    get tooltip () {
         return this._tooltip;
    }

    set tooltip (ttip) {
        this._tooltip = ttip;
    }

    get cpLanguage () {
        return this._cpLanguage;
    }

    set editLocales (loc) {
         this._editLocales = loc;
    }

    get editLocales () {
        return this._editLocales;
    }

    initCalendar () {

        var settings = $.extend({},this.params,this.defaults);

        if(Craft.getLocalStorage('Venti.eventSources')) {
            // if sources are already stored in local storage retrieve them
            this._sources = Craft.getLocalStorage('Venti.eventSources');
            settings.eventSources = this._sources;
        }else{
            this._sources = this._params.eventSources;
            // initially set the storage for sources
            Craft.setLocalStorage('Venti.eventSources',this._sources);
        }




        //init full calendar
        this._cal = $(this._id).fullCalendar(settings);

        this._localebtn = $(".fc-localeSelectButton-button");
        this.updateLocaleBtnText(this._locale);

    }

    viewRender (view, element) {
        $('.fc-day-number.fc-today').wrapInner('<span class="day-number-today"></span>');
        $('.fc-localeSelectButton-button').addClass("btn menubtn");
        $('.fc-groupsToggleButton-button').addClass('btn');
    }

    renderEvent (event, element) {
        var $this = this;
        element.data({"id": event.id, "locale": event.locale});
        if(event.multiDay || event.allDay){
            element.addClass('fc-event-multiday');
        }else{
            element.addClass('fc-event-singleday');
            element.find('.fc-content').prepend('<span class="event_group_color" style="background-color:'+ event.color +'"/>');
        }
        var content = $('<div data-eid=" ' + event.id + ' "/>'),
            title = $('<div class="event-tip--header"><h3>' + event.title + '</h3><h6><span class="event_group_color" style="background-color:'+ event.color +';"></span>'+ event.group +'</h6></div>').appendTo(content),
            close = $('<span class="closer"><svg height=16px version=1.1 viewBox="0 0 16 16"width=16px xmlns=http://www.w3.org/2000/svg xmlns:xlink=http://www.w3.org/1999/xlink><defs></defs><g id=Page-1 fill=none fill-rule=evenodd stroke=none stroke-width=1><g id=close.3.3.1><g id=Group><g id=Filled_Icons_1_ fill=#CBCBCC><g id=Filled_Icons><path d="M15.81248,14.90752 L9.22496,8.32 L15.81184,1.73248 C16.06208,1.48288 16.06208,1.07776 15.81184,0.82752 C15.5616,0.57728 15.15712,0.57728 14.90688,0.82752 L8.32,7.41504 L1.73184,0.82688 C1.4816,0.57728 1.07712,0.57728 0.82688,0.82688 C0.57664,1.07712 0.57664,1.4816 0.82688,1.73184 L7.41504,8.32 L0.82688,14.90816 C0.57728,15.15776 0.57664,15.56352 0.82688,15.81312 C1.07712,16.06336 1.48224,16.06272 1.73184,15.81312 L8.32,9.22496 L14.90752,15.81184 C15.15712,16.06208 15.56224,16.06208 15.81248,15.81184 C16.06272,15.56224 16.06208,15.15712 15.81248,14.90752 L15.81248,14.90752 Z"id=Shape></path></g></g><g id=Invisible_Shape transform="translate(0.640000, 0.640000)"><rect height=15.36 id=Rectangle-path width=15.36 x=0 y=0></rect></g></g></g></g></svg></span>').appendTo(title),
            dateWrap = $('<div class="event-tip--datetime"/>').appendTo(content),
            date = $(this.tipDateFormat(event)).appendTo(dateWrap),
            repeats = parseInt(event.repeat) === 1 ? $('<div class="repeats"><strong>'+ Craft.t("Repeats") + ':</strong> ' + event.summary + '</div>').appendTo(dateWrap) : '',
            buttons = (event.source.canEdit === true || event.source.canDelete) ? $('<div class="event-tip--actions"/>').appendTo(content) : '',
            occur =  (parseInt(event.repeat) === 1 && event.source.canEdit === true && $this._editLocales[event.locale]) ? $('<button class="btn">' + Craft.t("Remove Occurence") + '</button>').appendTo(buttons) : '',
            del = (event.source.canDelete === true  && $this._editLocales[event.locale]) ? $('<button class="btn">' +  Craft.t("Delete") + '</button>').appendTo(buttons) : '',
            edit = (event.source.canEdit === true  && $this._editLocales[event.locale]) ? $('<button class="btn submit">' + Craft.t("Edit") + '</button>').appendTo(buttons) : '';

        event.tooltip = $('<div/>').qtip({
    		position: {
                my: "bottom center",
                at: "top center",
                target: element.find('.fc-title'),
                viewport: $("#venti-calendar"),
                adjust: {
                    method: "shift flip"
                },

    		},
            content: {
                text: content
            },
            show: {
                solo: !0,
                delay: 200
            },
            hide: {
                fixed: !0,
                delay: 400
            },
            style: {
                classes: "venti-event-tip"
            }
    	}).qtip('api');


        Craft.cp.addListener(edit, 'click', $.proxy(function () {
            $this.editEvent(event,element);
            event.tooltip.hide();
        }));

        Craft.cp.addListener(del, 'click', $.proxy(function () {
            $this.deleteEvent(event,element);
            event.tooltip.hide();
        }));

        Craft.cp.addListener(occur, 'click', $.proxy(function () {
            $this.removeOccurence(event,element);
            event.tooltip.hide();
        }));

        Craft.cp.addListener(close, 'click', $.proxy(function () {
            event.tooltip.hide();
        }));

    }

    eventClick (calEvent, jsEvent, view) {
        this.editEvent(calEvent,$(jsEvent.currentTarget));
    }

    onLocaleChange (btn, selection) {
        var $this = this,
            value = $(selection).data('value');

        $this.updateLocaleBtnText(value);
        // sets Crafts local storage local variable to persist local in CP
        Craft.setLocalStorage("BaseElementIndex.locale",value);

        $this.updateEventSourceLocale($this._sources, value);
        // saves event sources in local storage
        Craft.setLocalStorage("Venti.eventSources", $this._sources);

        $this.resetEventSources();

    }

    updateEventSourceLocale ( sources, locale ) {
        var srcs = sources;
        for( var i = 0; i < srcs.length; i++ ) {
            var urlParts = srcs[i].url.split("/"),
                urlPartsLength = urlParts.length;
            urlParts[urlPartsLength - 1] = locale;
            srcs[i].url = urlParts.join('/');
        }

        // update instance sources variable
        this._sources = srcs;

    }

    updateLocaleBtnText ( handle ) {
        const btn = $(this._localebtn);
        const locales = this._params.locales;
        let label = "";

        for( var i = 0; i < locales.length; i++ ) {
            if( locales[i].handle === handle ) {
                label = locales[i].title;
                break;
            }
        }

        btn.text(label);
    }

    resetEventSources () {
        var sources = this._sources;
        this._cal.fullCalendar('removeEventSources');
        for (var i = 0; i < sources.length; i++) {
            this._cal.fullCalendar('addEventSource',sources[i]);
        }
    }


    // On event click or mouseover
    onEventAction (event, element, view) {
        var $this = this;
        event.tooltip.reposition(element.currentTarget,false).toggle(true).focus(element.currentTarget);
    }

    editEvent (event, target) {
        var $this = this,
            id = event.id;

        var settings = {
            showLocaleSwitcher: true,
            elementId: id,
            elementType: 'Venti_Event',
            saveButton: true,
            cancelButton: true,
            locale: this._locale,
            onHideModal: function () {
                $this._cal.fullCalendar('refetchEvents');
            }
        }
        new Venti.ElementEditor($(target), settings);
    }

    deleteEvent(event, target) {
        var $this = this,
            id = event.id,
            data = { "eventId" : id };

        if(window.confirm(Craft.t("Are you sure you want to delete this event?")) === true) {
            Craft.postActionRequest('venti/event/deleteEvent', data, $.proxy(function(response, textStatus)
            {
                if (textStatus == 'success')
                {
                    $this._cal.fullCalendar('refetchEvents');
                }
                else
                {

                }
            }, this));
        }
    }

    removeOccurence(event,target) {
        var $this = this,
            id = event.id,
            exDate = event.start.format(),
            locale = event.locale,
            data = { "id" : id, "exDate": exDate, "locale": locale };

        if(window.confirm(Craft.t("Are you sure you want to remove this occurence?")) === true) {
            Craft.postActionRequest('venti/event/removeOccurence', data, $.proxy(function(response, textStatus)
            {
                if (textStatus == 'success')
                {
                    $this._cal.fullCalendar('refetchEvents');
                }
                else
                {

                }
            }, this));
        }
    }

    updateEventDates ( event, delta, revertFunc, jsEvent, ui ) {
        //console.log(jsEvent);

        var $this = this;
        if(event.repeat == 1 ) {
            var ruleCollection = this.ruleParams(event.rRule);
            if (ruleCollection['FREQ'] === 'WEEKLY' || ruleCollection['FREQ'] === 'MONTHLY') {
                this.repeatAlertWindow(event,jsEvent.target);
                $this._cal.fullCalendar('refetchEvents');
                return false;
            }
        }
        Craft.postActionRequest(
            'venti/event/updateEventDates',
            {
                id: event.id,
                locale: event.locale,
                start: (event.start).toISOString(),
                end: (event.end).toISOString(),
            },
            function (data) {
                if(data.success) {
                    $this._cal.fullCalendar('refetchEvents');
                }
        } );
    }

    ruleParams (rrule) {
        var ruleChunks = rrule.split(';'),
            ruleCollection = [];

        for (var i = 0; i < ruleChunks.length; i++) {
            var keyAry  = ruleChunks[i].split("=");
            ruleCollection[keyAry[0]] = keyAry[1];
        }
        return ruleCollection;
    }

    repeatAlertWindow (event, target) {
        var $this = this,
            quickShow = false;

        this.showingAlertModal = true;

        if (!this.alertModal)
        {

            var $content = $('<div id="venti_alertmodal" class="modal alert fitted"/>'),
                $body = $('<div class="body"><h2>'+Craft.t('You\'re changing a repeating event.')+'</h2><p>'+Craft.t('You\’re changing the date of a repeating event with specific day(s) of the week or month. Edit the event to update the repeat schedule.')+'</p></div>').appendTo($content),
                $container = $('<div class="inputcontainer text--right"/>').appendTo($body),
                $cancel = $('<button class="btn cancel">'+Craft.t("Cancel")+'</button>').appendTo($container),
                $edit = $('<button class="btn submit">'+Craft.t("Edit")+'</button>').appendTo($container);

            this.alertModal = new Garnish.Modal($content, {
                autoShow: false,
                closeOtherModals: true,
                hideOnEsc: true,
                hideOnShadeClick: true,
                shadeClass: 'modal-shade dark'
            });

            // Listeners
            Craft.cp.addListener($cancel, 'click', $.proxy(this,'hideAlertModal'));
            Craft.cp.addListener($edit, 'click', $.proxy(function(evt){

                var tg = $(target).parent().removeStyle('position left right top bottom width height opacity z-index');

                $this.editEvent(event, tg);
                $this.hideAlertModal();

            }));
        }

        if (quickShow)
        {
            this.alertModal.quickShow();
        }
        else
        {
            this.alertModal.show();
        }

    }

    groupToggles (evt) {
        var $this = this,
            target = evt.target,
            origSources = this._params.eventSources,
            sources = this._sources,
            quickShow = false;

        if(!this.sourcesModal) {

            var $content = $('<form id="venti_groupsmodal" class="modal fitted venti_groupsmodal"/>'),
                $body = $('<div class="body"><h1 class="text--center">' + Craft.t('Groups') + '</h1></div>').appendTo($content),
                $list = $('<ul class="venti_group_selects" />').appendTo($body),
                $footer = $('<div class="footer"/>').appendTo($content),
                $cancel = $('<button class="btn cancel">' + Craft.t("Cancel") + '</button>').appendTo($footer),
                $done = $('<button class="btn submit slim" value="submit">' + Craft.t("Update") + '</button>').appendTo($footer);

            // create checkbox fields and toggle
            for (var key of origSources) {
                var selected = false,
                    $item = $('<li class="venti_group_select_item" />').appendTo($list);

                if (sources.some(function(e){ return e.id === key.id })) {
                    selected = true;
                }

                var $input = $('<input id="venti_group_select-' + key.id + '" class="venti_group_select_input" name="venti_group_select-' + key.id + '" data-id="' + key.id + '" type="checkbox"' + (selected ? 'checked=checked' : '') + '>').appendTo($item),
                    $label = $('<label for="venti_group_select-' + key.id + '" class="venti_group_select_label"><span style="background-color:' + key.color + '"></span>' + key.label + '</label>').appendTo($item);

            }

            // Create Sources Modal from Garnish.Modal
            this.sourcesModal = new Garnish.Modal($content, {
                autoShow: false,
                closeOtherModals: true,
                hideOnEsc: true,
                hideOnShadeClick: true,
                shadeClass: 'modal-shade light',
                onHide: $.proxy(this,'onHideGroupsModal')
            });

            // Listeners
            Craft.cp.addListener($cancel, 'click', $.proxy(function(evt) {
                evt.preventDefault();
                $this.hideSourcesModal();
            }));

            Craft.cp.addListener($content, 'submit', $.proxy(function(evt) {

                evt.preventDefault();
                var form = $(evt.target),
                    sourceCollection = [];

                form.find('input').each( function () {
                    var _this = $(this),
                        _id = _this.data('id');

                    if(_this.is(":checked")) {
                        for (var key of origSources) {
                            if(parseInt(key.id) === parseInt(_id)) {
                                sourceCollection.push(key);
                            }
                        }
                    }
                });

                $this._sources = sourceCollection;
                Craft.setLocalStorage("Venti.eventSources", $this._sources);

                $this.resetEventSources();
                $this.hideSourcesModal();

            }));

        }

        if (quickShow)
        {
            this.sourcesModal.quickShow();
        }
        else
        {
            this.sourcesModal.show();
        }

    }

    onMouseout (event, jsEvent, view) {
        this._tooltip.hide();
    }

    hideAlertModal () {
        this.alertModal.hide();
    }

    hideSourcesModal () {
        this.sourcesModal.hide();
    }

    onHideGroupsModal () {
        var $this = this,
            modal = this.sourcesModal.$container;
        // reset checkbox state to saved state
        modal.find('input').each( function () {
            var _this = $(this),
                _id = _this.data('id');
                _this.prop('checked', false);
            // If checkbox is in saved sources set it to checked
            for (var key of $this._sources) {
                if(parseInt(key.id) === parseInt(_id)) {
                    _this.prop('checked', true);
                }
            }
        });
    }

    /**
     * Return formated event string for tooltip
     * @return string
     */
    tipDateFormat (event) {
        var dateFormat = $('[data-date-format]').data('date-format'),
            timeFormat = $('[data-time-format]').data('time-format'),
            format = dateFormat + " " + timeFormat,
            startDate = moment(event.start),
            endDate = moment(event.end),
            output = "";


        if (event.allDay) {
            if(event.multiDay) {
                //output += Craft.t("All Day from");
                //output += " " + startDate.formatPHP(format) + " " + Craft.t("to") + " " + endDate.formatPHP(format);
                output += "<div><strong>" + Craft.t("Begins") + ":</strong> " + startDate.formatPHP(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("Ends") + ":</strong> " + endDate.formatPHP(format);
                output += "</div>";
            }else{
                //output += Craft.t("All Day from");
                //output += " " + startDate.formatPHP(format) + " " + Craft.t("to") + " " + endDate.formatPHP(timeFormat);
                output += "<strong>" + Craft.t("Begins") + ":</strong> " + startDate.formatPHP(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("Ends") + ":</strong> " + endDate.formatPHP(timeFormat);
                output += "</div>";
            }
        }else{
            if(event.multiDay) {
                //output += " " + startDate.formatPHP(format) + " " + Craft.t("to") + " " + endDate.formatPHP(format);
                output += "<div><strong>" + Craft.t("Begins") + ":</strong> " + startDate.formatPHP(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("Ends") + ":</strong> " + endDate.formatPHP(format);
                output += "</div>";
            }else{
                //output += " " + startDate.formatPHP(format) + " " + Craft.t("to") + " " + endDate.formatPHP(timeFormat);
                output += "<div><strong>" + Craft.t("Begins") + ":</strong> " + startDate.formatPHP(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("Ends") + ":</strong> " + endDate.formatPHP(timeFormat);
                output += "</div>";
            }
        }

        return output;
    }


    mapLocales (ventiLocale)
    {
        var lang = {
            "ar_ma" : "ar",
            "ar_sa" : "ar-sa",
            "ar_tn" : "ar",
            "ar"    : "ar",
            "bg"    : "bg",
            "ca_es" : "ca",
            "cs"    : "cs",
            "da"    : "da",
            "de_de" : "de",
            "de_at" : "de-at",
            "en"    : "en",
            "en_us" : "en",
            "en_gb" : "en-gb",
            "en_ca" : "en-ca",
            "en_au" : "en-au",
            "en_ie" : "en-ie",
            "en_nz" : "en-nz",
            "es"    : "es",
            "es_us" : "es",
            "es_cl" : "es",
            "es_es" : "es",
            "es_mx" : "es",
            "es_ve" : "es",
            "fi"    : "fi",
            "fr"    : "fr",
            "fr_ca" : "fr-ca",
            "fr_ch" : "fr-ch",
            "he"    : "he",
            "hr"    : "hr",
            "hr_hr" : "hr",
            "hu"    : "hu",
            "id"    : "id",
            "id_id" : "id",
            "it"    : "it",
            "it_it" : "it",
            "it_ch" : "it",
            "ja"    : "ja",
            "ja_jp" : "ja",
            "ko"    : "ko",
            "ko_kr" : "ko",
            "lt"    : "lt",
            "lv"    : "lv",
            "nb"    : "nb",
            "nl"    : "nl",
            "nl_be" : "nl",
            "nl_nl" : "nl",
            "pl"    : "pl",
            "pl_pl" : "pl",
            "pt_br" : "pt-br",
            "pt"    : "pt",
            "ro"    : "ro",
            "ro_ro" : "ro",
            "ru"    : "ru",
            "ru_ru" : "ru",
            "sk"    : "sk",
            "sl"    : "sl",
            "sr"    : "sr",
            "sv"    : "sv",
            "th"    : "th",
            "tr"    : "tr",
            "tr_tr" : "tr",
            "uk"    : "uk",
            "vi"    : "vi",
            "zh_cn" : "zh-cn",
            "zh_tw" : "zh-tw"
        };
        return lang[ventiLocale];
    }
}
