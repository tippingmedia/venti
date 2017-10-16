/*
 * Venti Modal
 */
class VentiModal {
    constructor(options) {
        this._options = options;
        this._container = options.inline == false ? document.getElementById(options.id + "-venti-modal") : document.getElementById(options.namespacedId + "-venti-inline");
        this._input = document.getElementById(options.id);


        this._overlay = options.inline == false ? this._container.parentNode : this._container;
        this._dateFormat = this._input.dataset['dateFormat'];
        this._schedule = new VentiSchedule(options, this);

        this.onShow = new CustomEvent("onShow", {
            'bubbles': true,
            'cancelable': true
        });

        this.onHide = new CustomEvent("onHide", {
            'bubbles': true,
            'cancelable': true
        });

        this.onCancel = new CustomEvent("onCancel", {
            'bubbles': true,
            'cancelable': true
        });

        this.initEvents();
    }

    get container() {
        return this._container;
    }

    get overlay() {
        return this._overlay;
    }

    get options() {
        return this._options;
    }

    get schedule() {
        return this._schedule;
    }

    set container(container) {
        this._container = container;
    }

    set overlay(overlay) {
        this._overlay = overlay;
    }

    set options(options) {
        this._options = options;
    }

    set schedule(schedule) {
        this._schedule = schedule;
    }

    show() {
        $(this._overlay).fadeIn('fast');
        this._container.dispatchEvent(this.onShow);
    }

    hide() {
        $(this._overlay).fadeOut('fast');
        this._container.dispatchEvent(this.onHide);
    }


    initEvents() {
        var $this = this,
            input = $this._input,
            mdl = $this._container,
            sch = $this._schedule,
            done = mdl.querySelector('button.submit.done'),
            cancel = mdl.querySelector('button.cancel'),
            tabContainer = mdl.querySelector('.venti_modal_tabs'),
            occurencesInput = mdl.querySelector('.venti-endson-afterfield'),
            rrule = input.querySelectorAll('.venti-rrule--input')[0],
            repeat = input.querySelectorAll('.venti-repeat--input')[0],
            form = mdl.querySelector('form');
        const rruleValueDeposite = rrule.value;


        // Modal done button click event
        done.addEventListener('click', function(evt) {
            evt.preventDefault();
            sch.getRuleString(mdl, function(data) {
                $this.setInputValues(data);
                $this.hide();
            });

        }, false);

        cancel.addEventListener('click', function(evt) {
            evt.preventDefault();
            $this._container.dispatchEvent($this.onCancel);
            $this.hide();
            // if rrule is not set uncheck repeat toggle.
            if (rrule.value === "") {
                repeat.checked = false;
            }
        }, false);

        $(tabContainer).on('click', 'a', function(evt) {
            evt.preventDefault();
            $this.toggleTab(
                evt.delegateTarget,
                $(this)
            );
        });

        form.addEventListener('focusout', function(evt) {
            //if(evt.target.tagName == "SELECT" || evt.target.tagName == "INPUT") {
            if (!evt.target.classList.contains('cancel')) {
                sch.getRuleString(mdl, function(data) {
                    setTimeout(function() {
                        $this.setInputValues(data);
                    }, 200);
                });
            }
            //}
        });

        form.addEventListener('change', function(evt) {
            if (!evt.target.classList.contains('cancel')) {
                sch.getRuleString(mdl, function(data) {
                    setTimeout(function() {
                        $this.setInputValues(data);
                    }, 200);
                });
            }
        });

        occurencesInput.addEventListener('keyup', function(evt) {
            sch.getRuleString(mdl, function(data) {
                setTimeout(function() {
                    $this.setInputValues(data);
                }, 200);
            });
        });

        $('#venti-fields-venti-endsOn-date').datepicker($.extend({}, Craft.datepickerOptions));
        $('#venti-fields-venti-exclude-date').datepicker($.extend({}, Craft.datepickerOptions));
        $('#venti-fields-venti-include-date').datepicker($.extend({}, Craft.datepickerOptions));
    }

    //[jQ]
    toggleTab(container, tab) {
        var container = $(container).find("ul"),
            id = tab.attr("href");

        container.find(".sel").removeClass('sel');
        tab.addClass('sel');
        $(id).siblings().hide();
        $(id).show();
    }


    /*
     * Clears rRule & summary hidden input as well as text holders next
     * too repeat checkbox and event modal summary box.
     */

    clearSummary() {
        var $this = this,
            mdl = $this._container;
        mdl.querySelectorAll('.venti-summary')[0].innerHTML = "";
    }

    setInputValues(values) {
        var $this = this,
            input = $this._input,
            mdl = $this._container,
            rruleInput = input.querySelectorAll('.venti-rrule--input')[0],
            summaryInput = input.querySelectorAll('.venti-summary--input')[0],
            summaryOutput = input.querySelectorAll('.venti-summary--human')[0],
            mdlSummaryOutput = mdl.querySelectorAll('.venti-summary')[0],
            mdlSummaryWrap = mdl.querySelectorAll('.venti-summary-extra-dates')[0],
            inpIncDates = input.querySelectorAll('.venti-included-dates')[0],
            inpExcDates = input.querySelectorAll('.venti-excluded-dates')[0],
            readable = values.readable ? values.readable.capitalize() : values.readable;


        rruleInput.value = values.rrule;
        summaryOutput.innerHTML = readable;
        summaryInput.value = readable;
        mdlSummaryOutput.innerHTML = readable;


        if (values.excluded.length > 0) {
            if (!inpExcDates.classList.contains('hidden')) {
                inpExcDates.classList.remove('hidden');
            }
            this.setExcludedDates(values.excluded);
        } else {
            if (!inpExcDates.classList.contains('hidden')) {
                inpExcDates.classList.add('hidden');
            }
        }

        if (values.included.length > 0) {
            if (inpIncDates.classList.contains('hidden')) {
                inpIncDates.classList.remove('hidden');
            }
            this.setIncludedDates(values.included);
        } else {
            if (!inpIncDates.classList.contains('hidden')) {
                inpIncDates.classList.add('hidden');
            }
        }
    }


    setExcludedDates(values) {
            var $this = this,
                input = $this._input,
                format = this._dateFormat,
                inpExcDates = input.querySelectorAll('.venti-excluded-dates')[0],
                itemsWrap = inpExcDates.querySelectorAll('.date__items')[0];

            if (inpExcDates.classList.contains('hidden')) {
                inpExcDates.classList.remove('hidden');
            }

            const items = `
            <span>${values.map(item => `${moment(item.date).formatPHP(format)}`).join(", ")}</span>
        `;

        itemsWrap.innerHTML = items;

    }

    setIncludedDates (values) {
        var $this = this,
            input = $this._input,
            format = this._dateFormat,
            inpIncDates = input.querySelectorAll('.venti-included-dates')[0],
            itemsWrap = inpIncDates.querySelectorAll('.date__items')[0];

        if(inpIncDates.classList.contains('hidden')) {
            inpIncDates.classList.remove('hidden');
        }

        const items = `
            <span>${values.map(item => `${moment(item.date).formatPHP(format)}`).join(", ")}</span>
        `;

        itemsWrap.innerHTML = items;
    }
}