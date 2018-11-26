import { Calendar } from 'fullcalendar';

class VentiCalendar {
    constructor(options) {
        let $this = this;
        // Options 
        this._id = options.id;
        this._params = options.params;
        this._input = document.getElementById(options.id);
        this._tooltip = null;
        this._cal = null;
        this._huds = {};
        this._localebtn = null;
        this._site = Craft.siteId;
        this._sources = null;
        this._multisite = options.params.multisite;
        this._alertModal = null;
        this._editLocales = options.params.editLocales;
        this._editSites = options.params.editSites;
        this._cpLanguage = options.params.cpLanguage ? this.mapLocales(options.params.cpLanguage) : "en";

        //FullCalendar default settings
        this._defaults = {
            customButtons: {
                siteSelectButton: {
                    text: this._params.sites[0].title,
                    click: function (evt) {
                        let currentTarget = event.currentTarget();
                        if ($this._sitebtn.data("menu") != "true") {
                            let menuList = "";
                            for (var i = 0; i < selOps.length; i++) {
                                if (options.params.editSites[selOps[i].handle]) {
                                    menuList += `<li><a data-value="${ selOps[i].handle }">${ selOps[i].title }</a></li>`;
                                }
                            }
                            
                            let menu = `<div class="menu" data-align="right"><ul>${ menuList }</ul></div>`;
                            currentTarget.parentNode.append(menu);
                            
                            let siteSelectProxy = new Proxy(evt.currentTarget, $this.onSiteChange);
                            new Garnish.MenuBtn(evt.currentTarget, { onOptionSelect: siteSelectProxy}).showMenu();

                            $this._sitebtn.data("menu", "true");

                        }
                    }
                },
                groupsToggleButton: {
                    text: Craft.t("venti", "Groups"),
                    click: new Proxy({}, this.groupToggles) //$.proxy(this, "groupToggles")
                }
            },
            header: {
                left: 'title',
                center: '',
                right: this._multisite === "true" ? 'siteSelectButton groupsToggleButton today prev,next ' : 'groupsToggleButton today prev,next'
            },
            viewRender: $.proxy(this, "viewRender"),
            editable: true,
            eventClick: $.proxy(this, "onEventAction"),
            eventDrop: $.proxy(this, "updateEventDates"),
            dayClick: $.proxy(function () { }),
            eventResizeStart: $.proxy(function () { }),
            eventDragStart: $.proxy(function () { }),
            eventRender: $.proxy(this, "renderEvent"),
            lang: this._cpLanguage,
            eventLimit: 6,

        };
    }

    initCalendar() {

        let settings = Object.assign(this.params, this.defaults);

        if (Craft.getLocalStorage('Venti.eventSources')) {
            // if sources are already stored in local storage retrieve them
            this._sources = Craft.getLocalStorage('Venti.eventSources');
            settings.eventSources = this._sources;
        } else {
            this._sources = this._params.eventSources;
            // initially set the storage for sources
            Craft.setLocalStorage('Venti.eventSources', this._sources);
        }

        //init full calendar
        this._cal = new Calendar(this._input, settings);
        this._cal.render();

        this._sitebtn = this._input.querySelector(".fc-siteSelectButton-button");
        this.updateSiteBtnText(this._site);

    }

    viewRender(view, element) {
        $('.fc-day-number.fc-today').wrapInner('<span class="day-number-today"></span>');
        $('.fc-siteSelectButton-button').addClass("btn menubtn");
        $('.fc-groupsToggleButton-button').addClass('btn');
    }

    renderEvent(event, element) {
        var $this = this;
        element.data({ "id": event.id, "site": event.siteId });
        if (event.multiDay || event.allDay) {
            element.addClass('fc-event-multiday');
        } else {
            element.addClass('fc-event-singleday');
            element.find('.fc-content').prepend('<span class="event_group_color" style="background-color:' + event.color + '"/>');
        }


        /*element.qtip({
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
                text: $(content)
            },
            show: {
                solo: true,
                delay: 200
            },
            hide: {
                fixed: true,
                delay: 400
            },
            style: {
                classes: "venti-event-tip"
            },
            events: qtipEvents
        }).qtip('api');*/
    }

    eventClick(calEvent, jsEvent, view) {
        this.editEvent(calEvent, $(jsEvent.currentTarget));
    }

    onSiteChange(btn, selection) {
        var $this = this,
            value = $(selection).data('value');

        //$this.updateLocaleBtnText(value);
        // sets Crafts local storage local variable to persist local in CP
        //Craft.setLocalStorage("BaseElementIndex.locale", value);

        $this.updateEventSourceSite($this._sources, value);
        // saves event sources in local storage
        Craft.setLocalStorage("Venti.eventSources", $this._sources);

        $this.resetEventSources();

    }

    updateEventSourceSite(sources, site) {
        var srcs = sources;
        for (var i = 0; i < srcs.length; i++) {
            var urlParts = srcs[i].url.split("/"),
                urlPartsLength = urlParts.length;
            urlParts[urlPartsLength - 1] = site;
            srcs[i].url = urlParts.join('/');
        }

        // update instance sources variable
        this._sources = srcs;

    }

    updateSiteBtnText(handle) {
        const btn = $(this._localebtn);
        const sites = this._params.sites;
        let label = "";

        for (var i = 0; i < sites.length; i++) {
            if (sites[i].handle === handle) {
                label = sites[i].title;
                break;
            }
        }

        btn.text(label);
    }

    resetEventSources() {
        var sources = this._sources;
        this._cal('removeEventSources');
        for (var i = 0; i < sources.length; i++) {
            this._cal('addEventSource', sources[i]);
        }
    }


    // On event click or mouseover
    onEventAction(event, element, view) {
        //$(element).qtip('show', true);
        let $this = this;
        let id = event._id;

        if (undefined === $this._huds[id]) {
            let repeats = `<div class="repeats"><strong>${Craft.t("venti", "Repeats")}:</strong>${event.summary}</div>`;
            let occur = `<button class="btn" data-occur>${Craft.t("venti", "Remove Occurence")}</button>`;
            let del = `<button class="btn" data-del>${Craft.t("venti", "Delete")}</button>`;
            let edit = `<button class="btn submit" data-edit>${Craft.t("venti", "Edit")}</button>`;
            let buttons = !$this._multisite || ($this._multisite && $this._editSites[event.siteId]) ? `
                <div class="hud-footer">
                    ${ (parseInt(event.recurring) === 1 && event.source.ajaxSettings.canManageEvents === true) ? occur : ''}
                    ${ (event.source.ajaxSettings.canManageEvents === true) ? del : ''}
                    ${ (event.source.ajaxSettings.canManageEvents === true) ? edit : ''}
                </div>` : '';
            let content = `
                <div data-eid='${event.id}'>
                    <div class="event-tip--header">
                        <h3>${event.title}</h3>
                        <h6>
                            <span class="event_group_color" style="background-color:${event.color};"></span>
                            ${event.group}
                        </h6>
                        <span class="closer">
                            <svg height=16px version=1.1 viewBox="0 0 16 16"width=16px xmlns=http://www.w3.org/2000/svg xmlns:xlink=http://www.w3.org/1999/xlink><defs></defs><g id=Page-1 fill=none fill-rule=evenodd stroke=none stroke-width=1><g id=close.3.3.1><g id=Group><g id=Filled_Icons_1_ fill=#CBCBCC><g id=Filled_Icons><path d="M15.81248,14.90752 L9.22496,8.32 L15.81184,1.73248 C16.06208,1.48288 16.06208,1.07776 15.81184,0.82752 C15.5616,0.57728 15.15712,0.57728 14.90688,0.82752 L8.32,7.41504 L1.73184,0.82688 C1.4816,0.57728 1.07712,0.57728 0.82688,0.82688 C0.57664,1.07712 0.57664,1.4816 0.82688,1.73184 L7.41504,8.32 L0.82688,14.90816 C0.57728,15.15776 0.57664,15.56352 0.82688,15.81312 C1.07712,16.06336 1.48224,16.06272 1.73184,15.81312 L8.32,9.22496 L14.90752,15.81184 C15.15712,16.06208 15.56224,16.06208 15.81248,15.81184 C16.06272,15.56224 16.06208,15.15712 15.81248,14.90752 L15.81248,14.90752 Z"id=Shape></path></g></g><g id=Invisible_Shape transform="translate(0.640000, 0.640000)"><rect height=15.36 id=Rectangle-path width=15.36 x=0 y=0></rect></g></g></g></g></svg>
                        </span>
                    </div>
                    <div class="event-tip--datetime">
                        ${$this.tipDateFormat(event)}
                        ${ parseInt(event.repeat) === 1 ? repeats : ''}
                    </div>        
                </div>
                ${ event.source.ajaxSettings.canManageEvents === true ? buttons : ''}
            `;


            $this._huds[id] = new Garnish.HUD($(element.currentTarget), $(content), {
                hudClass: 'hud venti-event-tip',
                tipWidth: 500,
                closeBtn: '.closer',
                onShow: $.proxy(function (evt) {

                    const tip = $(evt.target.$footer);

                    const btnEdit = tip.find('button[data-edit]');
                    const btnDelete = tip.find('button[data-del]');
                    const btnOccur = tip.find('button[data-occur]');

                    btnEdit.on('click', $.proxy(function (evt) {
                        evt.preventDefault();
                        $this.editEvent(event, element);
                        $this._huds[id].hide();
                    }));

                    btnDelete.on('click', $.proxy(function (evt) {
                        evt.preventDefault();
                        $this.deleteEvent(event, element);
                        $this._huds[id].hide();
                    }));

                    btnOccur.on('click', $.proxy(function (evt) {
                        evt.preventDefault();
                        $this.removeOccurence(event, element);
                        $this._huds[id].hide();
                    }));

                }, this)
            });
        } else {
            $this._huds[id].show();
        }
    }

    editEvent(event, target) {
        var $this = this,
            id = event.id;

        var settings = {
            showSiteSwitcher: true,
            elementId: id,
            elementType: 'tippingmedia\\venti\\elements\\VentiEvent',
            saveButton: true,
            cancelButton: true,
            siteId: this._site,
            onHideModal: function () {
                $this._cal.fullCalendar('refetchEvents');
            }
        }
        new Venti.ElementEditor($(target), settings);
    }

    /**
     * Delete Event
     * @param {VentiEvent} event 
     * @param {*} target 
     */
    deleteEvent(event, target) {
        var $this = this,
            id = event.id,
            data = { "eventId": id, "groupId": event.groupId };

        if (window.confirm(Craft.t("venti", "Are you sure you want to delete this event?")) === true) {
            Craft.postActionRequest('venti/event/delete-event', data, $.proxy(function (response, textStatus) {
                if (textStatus == 'success') {
                    $this._cal.fullCalendar('refetchEvents');
                } else {

                }
            }, this));
        }
    }

    removeOccurence(event, target) {
        var $this = this,
            id = event.id,
            exDate = event.start.format(),
            data = { "eventId": id, "exDate": exDate, "siteId": event.siteId };

        if (window.confirm(Craft.t("venti", "Are you sure you want to remove this occurence?")) === true) {
            Craft.postActionRequest('venti/event/remove-occurence', data, $.proxy(function (response, textStatus) {
                if (textStatus == 'success') {
                    $this._cal.fullCalendar('refetchEvents');
                } else {

                }
            }, this));
        }
    }

    updateEventDates(event, delta, revertFunc, jsEvent, ui) {
        //console.log(jsEvent);

        var $this = this;
        if (event.recurring == 1) {
            var ruleCollection = this.ruleParams(event.rRule);
            if (ruleCollection['FREQ'] === 'WEEKLY' || ruleCollection['FREQ'] === 'MONTHLY') {
                this.repeatAlertWindow(event, jsEvent.target);
                $this._cal.fullCalendar('refetchEvents');
                return false;
            }
        }
        Craft.postActionRequest(
            'venti/event/update-event-dates', {
                eventId: event.id,
                locale: event.locale,
                site: event.siteId,
                start: (event.start).toISOString(),
                end: (event.end).toISOString(),
            },
            function (data) {
                if (data.success) {
                    $this._cal.fullCalendar('refetchEvents');
                }
            });
    }

    ruleParams(rrule) {
        var ruleChunks = rrule.split(';'),
            ruleCollection = [];

        for (var i = 0; i < ruleChunks.length; i++) {
            var keyAry = ruleChunks[i].split("=");
            ruleCollection[keyAry[0]] = keyAry[1];
        }
        return ruleCollection;
    }

    repeatAlertWindow(event, target) {
        var $this = this,
            quickShow = false;

        this.showingAlertModal = true;

        if (!this.alertModal) {

            var $content = $('<div id="venti_alertmodal" class="modal alert fitted"/>'),
                $body = $('<div class="body"><h2>' + Craft.t("venti", 'You\'re changing a repeating event.') + '</h2><p>' + Craft.t('venti', 'You\’re changing the date of a repeating event with specific day(s) of the week or month. Edit the event to update the repeat schedule.') + '</p></div>').appendTo($content),
                $container = $('<div class="inputcontainer text--right"/>').appendTo($body),
                $footer = $('<div class="hud-footer"/>').appendTo($content),
                $cancel = $('<button class="btn cancel">' + Craft.t("venti", "Cancel") + '</button>').appendTo($footer),
                $edit = $('<button class="btn submit">' + Craft.t("venti", "Edit") + '</button>').appendTo($footer);

            this.alertModal = new Garnish.Modal($content, {
                autoShow: false,
                closeOtherModals: true,
                hideOnEsc: true,
                hideOnShadeClick: true,
                shadeClass: 'modal-shade dark'
            });

            // Listeners
            Craft.cp.addListener($cancel, 'click', $.proxy(this, 'hideAlertModal'));
            Craft.cp.addListener($edit, 'click', $.proxy(function (evt) {

                var tg = $(target).parent().removeStyle('position left right top bottom width height opacity z-index');

                $this.editEvent(event, tg);
                $this.hideAlertModal();

            }));
        }

        if (quickShow) {
            this.alertModal.quickShow();
        } else {
            this.alertModal.show();
        }

    }

    groupToggles(evt) {
        var $this = this,
            target = evt.target,
            origSources = this._params.eventSources,
            sources = this._sources,
            quickShow = false;

        if (!this.sourcesModal) {

            var $content = $('<form id="venti_groupsmodal" class="modal fitted venti_groupsmodal"/>'),
                $body = $('<div class="body"><h1 class="text--center">' + Craft.t("venti", 'Groups') + '</h1></div>').appendTo($content),
                $list = $('<ul class="venti_group_selects" />').appendTo($body),
                $footer = $('<div class="hud-footer"/>').appendTo($content),
                $cancel = $('<button class="btn cancel">' + Craft.t("venti", "Cancel") + '</button>').appendTo($footer),
                $done = $('<button class="btn submit slim" value="submit">' + Craft.t("venti", "Update") + '</button>').appendTo($footer);

            // create checkbox fields and toggle
            for (var key of origSources) {
                var selected = false,
                    $item = $('<li class="venti_group_select_item" />').appendTo($list);

                if (sources.some(function (e) { return e.id === key.id })) {
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
                onHide: $.proxy(this, 'onHideGroupsModal')
            });

            // Listeners
            Craft.cp.addListener($cancel, 'click', $.proxy(function (evt) {
                evt.preventDefault();
                $this.hideSourcesModal();
            }));

            Craft.cp.addListener($content, 'submit', $.proxy(function (evt) {

                evt.preventDefault();
                var form = $(evt.target),
                    sourceCollection = [];

                form.find('input').each(function () {
                    var _this = $(this),
                        _id = _this.data('id');

                    if (_this.is(":checked")) {
                        for (var key of origSources) {
                            if (parseInt(key.id) === parseInt(_id)) {
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

        if (quickShow) {
            this.sourcesModal.quickShow();
        } else {
            this.sourcesModal.show();
        }

    }

    onMouseout(event, jsEvent, view) {
        this._tooltip.hide();
    }

    hideAlertModal() {
        this.alertModal.hide();
    }

    hideSourcesModal() {
        this.sourcesModal.hide();
    }

    onHideGroupsModal() {
        var $this = this,
            modal = this.sourcesModal.$container;
        // reset checkbox state to saved state
        modal.find('input').each(function () {
            var _this = $(this),
                _id = _this.data('id');
            _this.prop('checked', false);
            // If checkbox is in saved sources set it to checked
            for (var key of $this._sources) {
                if (parseInt(key.id) === parseInt(_id)) {
                    _this.prop('checked', true);
                }
            }
        });
    }

    /**
     * Return formated event string for tooltip
     * @return string
     */
    tipDateFormat(event) {
        var dateFormat = $('[data-date-format]').data('date-format'),
            timeFormat = $('[data-time-format]').data('time-format'),
            //format = dateFormat + " " + timeFormat,
            startDate = moment(event.start),
            endDate = moment(event.end),
            output = "";
        // Moment JS format Crafts Locale formats don't match up to PHP's date formst.
        let format = "MMM D, YYYY h:m a";

        //console.log(format);


        if (event.allDay) {
            if (event.multiDay) {
                //output += Craft.t("All Day from");
                //output += " " + startDate.format(format) + " " + Craft.t("to") + " " + endDate.format(format);
                output += "<div><strong>" + Craft.t("venti", "Begins") + ":</strong> " + startDate.format(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("venti", "Ends") + ":</strong> " + endDate.format(format);
                output += "</div>";
            } else {
                //output += Craft.t("All Day from");
                //output += " " + startDate.format(format) + " " + Craft.t("to") + " " + endDate.format(timeFormat);
                output += "<strong>" + Craft.t("venti", "Begins") + ":</strong> " + startDate.format(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("venti", "Ends") + ":</strong> " + endDate.format(timeFormat);
                output += "</div>";
            }
        } else {
            if (event.multiDay) {
                //output += " " + startDate.format(format) + " " + Craft.t("to") + " " + endDate.format(format);
                output += "<div><strong>" + Craft.t("venti", "Begins") + ":</strong> " + startDate.format(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("venti", "Ends") + ":</strong> " + endDate.format(format);
                output += "</div>";
            } else {
                //output += " " + startDate.format(format) + " " + Craft.t("to") + " " + endDate.format(timeFormat);
                output += "<div><strong>" + Craft.t("venti", "Begins") + ":</strong> " + startDate.format(format);
                output += "</div><div>";
                output += "<strong>" + Craft.t("venti", "Ends") + ":</strong> " + endDate.format(timeFormat);
                output += "</div>";
            }
        }

        return output;
    }


    mapLocales(ventiLocale) {
        var lang = {
            "ar_ma": "ar",
            "ar_sa": "ar-sa",
            "ar_tn": "ar",
            "ar": "ar",
            "bg": "bg",
            "ca_es": "ca",
            "cs": "cs",
            "da": "da",
            "de_de": "de",
            "de_at": "de-at",
            "en": "en",
            "en_us": "en",
            "en_gb": "en-gb",
            "en_ca": "en-ca",
            "en_au": "en-au",
            "en_ie": "en-ie",
            "en_nz": "en-nz",
            "es": "es",
            "es_us": "es",
            "es_cl": "es",
            "es_es": "es",
            "es_mx": "es",
            "es_ve": "es",
            "fi": "fi",
            "fr": "fr",
            "fr_ca": "fr-ca",
            "fr_ch": "fr-ch",
            "he": "he",
            "hr": "hr",
            "hr_hr": "hr",
            "hu": "hu",
            "id": "id",
            "id_id": "id",
            "it": "it",
            "it_it": "it",
            "it_ch": "it",
            "ja": "ja",
            "ja_jp": "ja",
            "ko": "ko",
            "ko_kr": "ko",
            "lt": "lt",
            "lv": "lv",
            "nb": "nb",
            "nl": "nl",
            "nl_be": "nl",
            "nl_nl": "nl",
            "pl": "pl",
            "pl_pl": "pl",
            "pt_br": "pt-br",
            "pt": "pt",
            "ro": "ro",
            "ro_ro": "ro",
            "ru": "ru",
            "ru_ru": "ru",
            "sk": "sk",
            "sl": "sl",
            "sr": "sr",
            "sv": "sv",
            "th": "th",
            "tr": "tr",
            "tr_tr": "tr",
            "uk": "uk",
            "vi": "vi",
            "zh_cn": "zh-cn",
            "zh_tw": "zh-tw"
        };
        return lang[ventiLocale];
    }
}

export default VentiCalendar;