/*
 * Venti Modal
 */
class VentiSchedule {
    constructor(options, modal) {
        this._options = options;
        this._container = document.getElementById(options.id + "-venti-modal");
        this._input = document.getElementById(options.id);
        this._freqSelect = this._container.querySelectorAll('.venti-frequency--select')[0];
        this._modal = modal;

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

    get freqSelect() {
        return this._freqSelect;
    }

    set container(container) {
        this._container = container;
    }

    set options(options) {
        this._options = options;
    }

    set freqSelect(freqsel) {
        this._freqSelect = freqsel;
    }

    //[jQ]
    initEvents() {
        var $this = this,
            mdl = $this._container,
            dp = mdl.querySelectorAll('.venti-endson-datefield')[0],
            excludeDp = mdl.querySelectorAll('.venti-exclude-datefield')[0],
            includeDp = mdl.querySelectorAll('.venti-include-datefield')[0],
            ends = mdl.querySelectorAll('.venti_endson')[0];


        // Update scheduler state
        this.freqSelect.addEventListener('change', function() {
            var sel = this,
                idx = sel.selectedIndex;
            $this.updateState(sel.options[idx].value);
        }, false);

        dp.addEventListener('focusout', function(evt) {
            setTimeout(function() {
                // update rule string
                $this.getRuleString(mdl, function(data) {
                    setTimeout(function() {
                        $this._modal.setInputValues(data);
                    }, 200);
                });
            }, 200);
        }, false);

        // Action after exclude datepicker focusout event
        excludeDp.addEventListener('focusout', function(evt) {
            var thisDP = this;
            setTimeout(function() {
                $this.setDateElement(thisDP, evt);
                // update rule string
                $this.getRuleString(mdl, function(data) {
                    setTimeout(function() {
                        $this._modal.setInputValues(data);
                    }, 200);
                });
            }, 200);
        }, false);

        // Action after incude datepicker focusout event
        includeDp.addEventListener('focusout', function(evt) {
            var thisDP = this;
            setTimeout(function() {
                $this.setDateElement(thisDP, evt);
                // update rule string
                $this.getRuleString(mdl, function(data) {
                    setTimeout(function() {
                        $this._modal.setInputValues(data);
                    }, 200);
                });
            }, 200);
        }, false);

        $("#" + mdl.id).on('click', '.delete', function() {
            $(this).parent().fadeOut(function() {
                $(this).remove();
                $this.getRuleString(mdl, function(data) {
                    setTimeout(function() {
                        $this._modal.setInputValues(data);
                    }, 200);
                });
            });
        });


        ends.addEventListener('click', function(evt) {
            var parent = this,
                elm = evt.target,
                textInputs = parent.querySelectorAll('input[type=text]');

            if (elm.className === "venti-endson__after" && elm.checked) {

                textInputs[0].disabled = false;
                textInputs[0].classList.remove('disabled');
                textInputs[1].disabled = true;
                textInputs[1].classList.add('disabled');
                textInputs[1].value = "";

            } else if (elm.className === "venti-endson__date" && elm.checked) {

                textInputs[1].disabled = false;
                textInputs[1].classList.remove('disabled');
                textInputs[0].disabled = true;
                textInputs[0].classList.add('disabled');
                textInputs[0].value = "";

            } else if (elm.className === "venti-endson__never" && elm.checked) {
                for (var i = 0; i < textInputs.length; i++) {
                    textInputs[i].disabled = true;
                    textInputs[i].classList.add('disabled');
                    textInputs[i].value = "";
                }
            }

            // update rule string
            $this.getRuleString(mdl, function(data) {
                setTimeout(function() {
                    $this._modal.setInputValues(data);
                }, 200);
            });

        }, false);
    }

    clearSummary() {
        var $this = this,
            mdl = $this._container;
        mdl.querySelectorAll('.venti-summary')[0].innerHTML = "";
    }

    updateState(value) {
        var stateID = (parseInt(value) + 1);
        this._container.dataset.state = stateID;
    }

    setStartOn() {
        var $this = this,
            input = $this._input,
            mdl = $this._container,
            startOnInput = mdl.querySelectorAll('.venti-starts-on')[0],
            startOnTimeInput = mdl.querySelectorAll('.venti-starts-on')[1],
            startDate = input.querySelectorAll('.venti-startdate--input')[0],
            startTime = input.querySelectorAll('.venti-startdate--input')[1];

        if (startDate.value !== "") {
            startOnInput.value = startDate.value;
            if (startTime.value !== "") {
                startOnTimeInput.value = startTime.value;
            }
        }
    }

    setEnds() {
        var $this = this,
            input = $this._input,
            mdl = $this._container,
            endOnInput = mdl.querySelectorAll('.venti-ends-on')[0],
            endDate = input.querySelectorAll('.venti-enddate--input')[0],
            endTime = input.querySelectorAll('.venti-enddate--input')[1];
        if (endDate.value !== "") {
            endOnInput.value = endDate.value + " " + endTime.value;
        }
    }

    //[jQ]
    setDateElement(obj, evt) {
            var input = obj,
                value = input.value,
                tab = this.getNthParent(input, 4),
                elmList = $("#" + tab.id).find('.venti_elements'),
                tempName = tab.dataset.template,
                temp = $(tempName).text(),
                elm = $(temp);

            if (value.trim() !== "") {
                elm.find('input').attr('value', value);
                elm.find('.title').append(value);
                elmList.append(elm);
                input.value = "";
            }
        }
        //[jQ]
    getRuleString(elm, callback) {
        var $this = this,
            mdl = $this._container,
            formData = $("#" + mdl.id).find(".venti_modal-form").serialize();
        Craft.postActionRequest('venti/event/get-rule-string', formData, function(data) {
            if (typeof callback == 'function') {
                callback(data);
            }
        });
    }

    getNthParent(elm, idx) {
        var el = elm,
            i = idx;
        while (i-- && (el = el.parentNode));
        return el;
    }
}