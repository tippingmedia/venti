/**
 * Venti Input Class
 */

class VentiInput {
    constructor(options) {

        this._id = options.id;
        this._input = document.getElementById(options.id);
        this._defaultEventDuration = this._input.dataset['defaultEventDuration'],
            this._groupMenu = null,
            this._storedDates = {
                state: false,
                startTime: '',
                endTime: ''
            };
        // Inlining modal for action html
        if (options.inline) {
            this.loadInline(options, this);
        } else {
            this.loadModal(options, this);
        }

    }


    get input() {
        return this._input;
    }

    get modal() {
        return this._modal;
    }

    get id() {
        return this._id;
    }

    get storedDates() {
        return this._storedDates;
    }

    set input(input) {
        this._input = input;
    }

    set modal(modal) {
        this._modal = modal;
    }

    set id(id) {
        this._id = id;
    }

    set storedDates(storedDates) {
        this._storedDates = storedDates;
    }

    get defaultEventDuration() {
        return this._defaultEventDuration;
    }

    set groupMenu(menu) {
        this._groupMenu = menu;
    }

    get groupMenu() {
        return this._groupMenu;
    }

    initEvents() {
        var $this = this,
            input = $this._input,
            modal = $this._modal,
            startDateInput = input.querySelectorAll('.venti-startdate--input')[0],
            endDateInput = input.querySelectorAll('.venti-enddate--input')[0],
            startDateTimeInput = input.querySelectorAll('.venti-startdate--input')[1],
            endDateTimeInput = input.querySelectorAll('.venti-enddate--input')[1],
            rrule = input.querySelectorAll('.venti-rrule--input')[0];
        let rruleDeposite = "";

        input.addEventListener('click', function(evt) {
            var elm = evt.target;
            if (elm.classList.contains('venti-eventRepeat-edit')) {
                modal.show();
            } else if (elm.classList.contains('venti-allday--input')) {
                $this.toggleAllDay(evt);
            } else if (elm.classList.contains('venti-recurring--input')) {
                if (elm.checked) {
                    $this.endDateValidate(endDateInput);
                    modal.show();
                } else {
                    modal.hide();
                }
                //$this.endDateFauxDisable(elm);
                if (rrule.value !== "") {
                    $this.clearSummary();
                }
            }
        }, false);

        startDateInput.addEventListener('focusout', function(evt) {
            var thisSDI = this;
            setTimeout(function() {
                $this.startDateValidate(thisSDI);
            }, 200);
        }, false);

        startDateTimeInput.addEventListener('focusout', function(evt) {
            var thisSDTI = this;
            setTimeout(function() {
                $this.startDateValidate(thisSDTI);
            }, 200);
        });

        endDateInput.addEventListener('focusout', function(evt) {
            var thisEDI = this;
            setTimeout(function() {
                $this.endDateValidate(thisEDI);
            }, 200);
        }, false);

        endDateTimeInput.addEventListener('focusout', function(evt) {
            var thisEDTI = this;
            setTimeout(function() {
                $this.endDateValidate(thisEDTI);
            }, 200);
        });


        if (this._groupMenu === null) {
            this._groupMenu = new Garnish.MenuBtn($('.groupbtn'));
            this._groupMenu.on('optionSelect', $.proxy(function(evt) {
                $this.groupMenuActions(evt, $this);
            }, this));
        } else {
            this._groupmenu.showMenu();
        }

        /*document.body.addEventListener('onHide', function (evt) {
            if(evt.srcElement.id === modal.container.id){
                var rruleInput = input.querySelectorAll('.venti-rrule--input')[0],
                    repeatCbBtn = input.querySelectorAll('.venti-repeat--input')[0];
                if(rruleInput.value === ""){
                    repeatCbBtn.checked = false;
                    $this.endDateFauxDisable(repeatCbBtn);
                }
            }
        },false);*/

        document.body.addEventListener('onShow', function(evt) {
            if (evt.srcElement.id === modal.container.id) {
                rruleDeposite = rrule.value;
                modal.schedule.setStartOn();
                modal.schedule.setEnds();
            }
        }, false);

        document.body.addEventListener('onCancel', function(evt) {
            if (evt.srcElement.id === modal.container.id) {
                if (rrule.value === "" || (rruleDeposite !== rrule.value && rruleDeposite === "")) {
                    setTimeout(function() {
                        recurring.checked = false;
                        rrule.value = rruleDeposite;
                        $this.clearSummary();
                    }, 400);
                }
            }
        });
    }

    loadModal(options) {
        var $this = this,
            rrule = $this._input.querySelectorAll('.venti-rrule--input')[0];
        Craft.postActionRequest(
            'venti/event/modal', {
                name: options.id,
                rrule: options.values !== null ? options.values.rRule : "",
                siteId: options.siteId,
                inline: false,
            },
            function(data) {
                // Append modal content
                //[jQ]
                $('body').append(data.html);
                $this._modal = new VentiModal(options);

                $this.initEvents();

            });
    }

    loadInline(options) {
        var $this = this;

        Craft.postActionRequest(
            'venti/event/modal', {
                name: options.id,
                rrule: options.values !== null ? options.values.rRule : "",
                siteId: options.siteId,
                inline: true,
            },
            function(data) {
                // Append modal content
                //[jQ]
                $("#" + options.id + ' .venti-inline').append(data.html);
                $this._modal = new VentiModal(options);

                $this.initEvents();
            });
    }


    toggleAllDay(evt) {
        var $this = this,
            input = $this._input,
            startDateTime = input.querySelectorAll('.venti-startdate--input')[1],
            endDateTime = input.querySelectorAll('.venti-enddate--input')[1],
            timeFormat = input.dataset['timeFormat'],
            dateFormat = input.dataset['dateFormat'],
            endOfDay = input.dataset.eod,
            startOfDay = input.dataset.sod;

        if (evt.target.checked) {
            input.classList.add('allDay');
            if (!$this._storedDates.state) {
                $this._storedDates.startTime = startDateTime.value;
                $this._storedDates.endTime = endDateTime.value;
                startDateTime.value = startOfDay;
                endDateTime.value = endOfDay;
                $this._storedDates.state = true;
            } else {
                startDateTime.value = startOfDay;
                endDateTime.value = endOfDay;
            }
        } else {
            input.classList.remove('allDay');
            if ($this._storedDates.state) {
                startDateTime.value = $this._storedDates.startTime;
                endDateTime.value = $this._storedDates.endTime;
            }
        }
    }

    clearSummary() {
        var $this = this,
            input = $this._input,
            modal = $this._modal,
            //[jQ]
            edit = $('.venti-eventRepeat-edit');

        input.querySelectorAll('.venti-summary--input')[0].value = "";
        input.querySelectorAll('.venti-rrule--input')[0].value = "";
        input.querySelectorAll('.venti-summary--human')[0].innerHTML = "";
        const incDates = input.querySelectorAll('.venti-included-dates')[0];
        const excDates = input.querySelectorAll('.venti-excluded-dates')[0];

        //[jQ] if visible
        if (edit.is(":visible")) {
            edit.hide();
        }

        if (!incDates.classList.contains('hidden')) {
            incDates.classList.add('hidden');
        }

        if (!excDates.classList.contains('hidden')) {
            excDates.classList.add('hidden');
        }

        modal.clearSummary();
    }

    startDateValidate(elm) {
        var $this = this,
            input = $this._input,
            modal = $this._modal,
            startDateInput = elm,
            sdValue = startDateInput.value,
            startDateInput = input.querySelectorAll('.venti-startdate--input')[0],
            startDateTimeInput = input.querySelectorAll('.venti-startdate--input')[1],
            endDateInput = input.querySelectorAll('.venti-enddate--input')[0],
            endDateTimeInput = input.querySelectorAll('.venti-enddate--input')[1],
            edValue = endDateInput.value,
            repeatChbx = input.querySelectorAll('.venti-recurring--input')[0],
            rruleInput = input.querySelectorAll('.venti-rrule--input')[0],
            timeFormat = input.dataset['timeFormat'];


        /*
         * When StartDate focusout if no EndDate make same as StartDate
         * If EndDate is populate && repeat checkbox is checked always make EndDate = StartDate
         * Else make sure EndDate is >= StartDate
         */
        if (edValue === "") {
            endDateInput.value = sdValue;
        } else {
            var sD_Date = new Date(sdValue),
                eD_Date = new Date(edValue);

            if (repeatChbx.checked && eD_Date == "") {
                endDateInput.value = sdValue;
            } else {
                if (sD_Date > eD_Date && eD_Date == "") {
                    endDateInput.value = sdValue;
                }
            }

            /* If start date is updated and there is a recurrence set update
             * the recurrence rule.
             */
            if (repeatChbx.checked && rruleInput.value !== "") {
                modal.schedule.setStartOn();
                modal.schedule.setEnds();
                modal.schedule.getRuleString(modal, function(data) {
                    modal.setInputValues(data);
                });
            }
        }

        // automatically add default event duration time to start date time
        // and set end date time
        if (elm.classList.contains('ui-timepicker-input')) {
            if (endDateTimeInput.value === "" && sdValue !== "") {
                var startDate = moment(startDateInput.value + " " + sdValue);
                var timeDiff = parseInt($this._defaultEventDuration);
                endDateTimeInput.value = startDate.add(timeDiff, 'minutes').formatPHP(timeFormat);
            }
        }
        //console.log("START: " + sdValue);
        //console.log("END: " + edValue);
    }

    endDateFauxDisable(elm) {
        var $this = this,
            input = $this._input;

        if (elm.checked) {
            input.classList.add('repeats');
        } else {
            input.classList.remove('repeats');
        }
    }

    endDateValidate(elm) {
        var $this = this,
            input = $this._input,
            modal = $this._modal,
            endDateInput = elm,
            edValue = endDateInput.value,
            startDateInput = input.querySelectorAll('.venti-startdate--input')[0],
            sdValue = startDateInput.value,
            repeatChbx = input.querySelectorAll('.venti-recurring--input')[0],
            rruleInput = input.querySelectorAll('.venti-rrule--input')[0];

        if (sdValue !== "") {
            var sD_Date = new Date(sdValue),
                eD_Date = new Date(edValue);
            if (eD_Date < sD_Date && eD_Date !== "") {
                endDateInput.value = sdValue;
            }
            // if(repeatChbx.checked && eD_Date !== ""){
            //     endDateInput.value = sdValue;
            // }

            /* If end date is updated and there is a recurrence set update
             * the recurrence rule.
             */
            if (repeatChbx.checked && rruleInput.value !== "") {
                modal.schedule.setStartOn();
                modal.schedule.setEnds();
                modal.schedule.getRuleString(modal, function(data) {
                    modal.setInputValues(data);
                });
            }
        }
    }

    groupMenuActions(evt, $this) {
        const _fields = $('#venti-fields');
        const _spinner = $('#venti-group-menu-field .spinner');
        let input = $this._input;
        let data = evt.option.dataset;
        let groupInput = input.querySelectorAll('.venti-groupId')[0];
        let optionValue = data.value;
        let optionLabel = data.label;
        let optionColor = data.color;
        let groupBtn = evt.target.$btn[0];
        let groupBtnLabel = groupBtn.querySelectorAll('.groupbtn-label')[0];
        let groupBtnLabelClr = groupBtn.querySelectorAll('.groupbtn-color')[0];
        const saveContinueEdit = document.querySelector('.save-continue-editing');
        const saveAddAnother = document.querySelector('.save-add-another');
        const shortcurtRedirect = document.querySelector('form[data-saveshortcut-redirect]');
        const continueEditing = document.querySelector('[name=continueEditingUrl]');

        //reveal spinner
        _spinner.removeClass('hidden');

        groupInput.value = optionValue;
        groupBtnLabel.innerHTML = optionLabel;
        groupBtnLabelClr.style.backgroundColor = optionColor;


        Craft.postActionRequest('venti/event/switch-group', Craft.cp.$primaryForm.serialize(), $.proxy(function(response, textStatus) {
            _spinner.addClass('hidden');

            if (textStatus == 'success') {
                //console.log(response.variables);
                var fieldsPane = _fields.data('pane');
                fieldsPane.deselectTab();
                _fields.html(response.paneHtml);
                fieldsPane.destroy();
                _fields.pane();
                Craft.initUiElements(_fields);

                Craft.appendHeadHtml(response.headHtml);
                Craft.appendFootHtml(response.footHtml);

                shortcurtRedirect.dataset.saveshortcutRedirect = response.variables.continueEditingUrl;
                continueEditing.value = response.variables.continueEditingUrl;
                //window.Craft.path = response.variables.continueEditingUrl;
                //saveContinueEdit.dataset.redirect = response.variables.continueEditingUrl;
                //saveAddAnother.dataset.redirect = `venti/${response.variables.group.handle}/new`;

                // Update the slug generator with the new title input
                if (typeof slugGenerator != "undefined") {
                    slugGenerator.setNewSource('#title');
                }

            }
        }, this));

        // Set the hidden input group id value

    }
}