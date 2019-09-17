<template>
    <div class="venti-inputs" ref="venti">
        <div class="venti-input-group input">
            <div class="field">
                <div class="heading">
                    <label v-text="startDateLabel" class="required"></label>
                </div>
                <date-pick 
                    :format="datePickerFormat"
                    :inputAttributes="{ 
                        name:'startDate[date]', 
                        class:'text',
                        autocomplete: 'off' }" 
                    v-model="startDate.date"
                ></date-pick>
            </div>
            <div class="field"  v-if="!allDay">
                <v-select :options="times" v-model="startDate.time" class="venti-time" :clearable="false" selectOnTab push-tags taggable :create-option="time => ({label: time, value: time})"></v-select>
            </div>
            <input type="hidden" name="startDate[time]" v-model="startDate.time">
            <input type="hidden" name="startDate[timezone]" v-model="timezone" />
        </div>

        <div class="venti-input-group input">
            <div class="field">
                <div class="heading">
                    <label v-text="endDateLabel" class="required"></label>
                </div>
                <date-pick 
                    :format="datePickerFormat"
                    :inputAttributes="{ 
                        name:'endDate[date]', 
                        class:'text',
                        autocomplete: 'off' }" 
                    v-model="endDate.date"
                ></date-pick>
            </div>
            <div class="field" v-if="!allDay">
                <v-select :options="times" v-model="endDate.time" class="venti-time" :clearable="false" selectOnTab push-tags taggable :create-option="time => ({label: time, value: time})"></v-select>
            </div>
            <input type="hidden" name="endDate[time]" v-model="endDate.time">
            <input type="hidden" name="endDate[timezone]" v-model="timezone" />
        </div>

        <div class="venti-input-group input">   
            <div class="field-onedge field-flex">
                <checkbox id="venti-all-day" v-model="allDay" name="allDay"></checkbox>
                <span class="heading">
                    <label v-text="allDayLabel" for="venti-all-day"></label>
                </span>
            </div>
            <div class="field field-onedge">
                <v-select 
                    v-model="repeat.frequency" 
                    :options="frequency"  
                    :searchable="false" 
                    :clearable="false" 
                    :selectOnTab="true" 
                    :reduce="option => option.value" 
                    placeholder="Does not repeat" 
                    class="venti-reccuring-select"></v-select>
            </div>
        </div>
        <div class="venti-input-sep" style='margin:24px 0px;'></div>

        <div class="input" v-if='recurring == 1'>
            <div class="field">
                <div class="heading">
                    <label v-text="'Excluded Dates'" for="venti-excluded-dates"></label>
                </div>
                <div>
                    <date-pick 
                        :format="datePickerFormat"
                        :inputAttributes="{ 
                            class:'text',
                            placeholder: 'Select Date',
                            autocomplete: 'off' }" 
                        v-model="excludedDate"
                    ></date-pick>
                </div>
            </div>
            <div class="field">
                <vue-tags-input
                    v-model="excludedTag"
                    :tags="excludedDates"
                    :placeholder="''"
                    @tags-changed="excludedTagsChanged"
                />
            </div>
            
            <input v-for="(item, index) in repeat.exclude" type='hidden' name="repeat[exclude][]" :value="item.text" :key="'exdate'+index">
            
        </div>

        <div class="input" v-if='recurring == 1'>
            <div class="field">
                <div class="heading">
                    <label v-text="'Included Dates'" for="venti-included-dates"></label>
                </div>
                <div>
                    <date-pick 
                        :format="datePickerFormat"
                        :inputAttributes="{ 
                            class:'text',
                            placeholder: 'Select Date',
                            autocomplete: 'off' }" 
                        v-model="includedDate"
                    ></date-pick>
                </div>
            </div>
            <div class="field">
                <vue-tags-input
                    v-model="includedTag"
                    :tags="includedDates"
                    :placeholder="''"
                    @tags-changed="includedTagsChanged"
                />
            </div>
            
            <input v-for="(item, index) in repeat.include" type='hidden' name="repeat[include][]" :value="item.text" :key="'rdate'+index">
        </div>
        
        <input type="hidden" name="recurring" v-model="recurring" />
        <input type="hidden" name="rRule" v-model="rrule" />

        <modal name="venti-repeat" transition="pop-out" :width="320" :height="400">
            <div class="box">
                <div clas="box-header">
                    <h2>Custom recurrence</h2>
                </div>
                <div class="box-body">
                    <div class="input">
                        <label>
                            <strong class="heading">Repeat every</strong>&nbsp;
                            <input type="number" class="text " min="1" max="365" v-model="repeat.every"/>
                        </label>
                    </div>
                    <div class="input" v-if="repeat.frequency >= 1 && repeat.frequency <= 4">
                        <strong class="heading">
                            <label v-text="'Repeat On'"></label>
                        </strong>
                        <div class="venti-repeat-on">
                            <checkbox name="repeat[on][]" v-model="repeat.on" value="sunday" class="-S">
                            </checkbox>
                            <checkbox name="repeat[on][]" v-model="repeat.on" value="monday" class="-M">
                            </checkbox>
                            <checkbox name="repeat[on][]" v-model="repeat.on" value="tuesday" class="-T">
                            </checkbox>
                            <checkbox name="repeat[on][]" v-model="repeat.on" value="wednesday" class="-W">
                            </checkbox>
                            <checkbox name="repeat[on][]" v-model="repeat.on" value="thursday" class="-T">
                            </checkbox>
                            <checkbox name="repeat[on][]" v-model="repeat.on" value="friday" class="-F">
                            </checkbox>
                            <checkbox name="repeat[on][]" v-model="repeat.on" value="saturday" class="-S">
                            </checkbox>
                        </div>
                    </div>
                    <div class="input" v-if="repeat.frequency == 5">
                        <strong class="heading">
                            <label v-text="'Repeat by'"></label>
                        </strong>
                        <div class="venti-repeat-by">
                            <v-select :options="byLabels" :searchable="false" :clearable="false" :selectOnTab="true" v-model="repeat.by" :reduce="option => option.value" value="0"></v-select>
                        </div>
                    </div>

                    <div class="input">
                        <strong class="heading">
                            <label v-text="'Ends'"></label>
                        </strong>
                        <div class="venti-endson">
                            <div class="input-row">
                                <div class="input-row-toggle">
                                    <radio id="repeat-endson-never" name="repeat[endsOn]" v-model="repeat.endsOn" value="0" :checked="repeat.endsOn == 0"></radio>
                                    <label for="repeat-endson-never">Never</label>
                                </div>
                            </div>
                            <div class="input-row">
                                <div class="input-row-toggle">
                                    <radio id="repeat-endson-on" name="repeat[endsOn]" v-model="repeat.endsOn" value="1" :checked="repeat.endsOn == 1"></radio>
                                    <label for="repeat-endson-on">On</label>
                                </div>
                                <div :class="repeat.endsOn != 1 ? 'disabled' : ''">
                                    <date-pick 
                                        :focus="repeat.endsOn != 1"
                                        :format="datePickerFormat"
                                        :inputAttributes="{ 
                                            name:'repeat[endDate]', 
                                            class:'text',
                                            autocomplete: 'off' }" 
                                        v-model="repeat.until.date"
                                    ></date-pick>
                                </div>
                            </div>
                            <div class="input-row">
                                <div class="input-row-toggle">
                                    <radio id="repeat-endson-after" name="repeat[endsOn]" v-model="repeat.endsOn" value="2" :checked="repeat.endsOn == 2"></radio>
                                    <label for="repeat-endson-after">After</label>
                                </div>
                                <div :class="repeat.endsOn != 2 ? 'disabled' : ''">
                                    <input type="number" min="1" max="365" v-model="repeat.occur" class="text" :disabled="repeat.endsOn != 2"/>&nbsp;
                                    <label>
                                        occurences
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-actions">
                    <button class="cancel" @click.prevent="cancelModal">Cancel</button>
                    <button class="submit" @click.prevent="doneModal">Done</button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
import Vue from 'vue';
import { DateTime } from 'luxon';
import RepeatModal from './RepeatModal.vue';
import DatePick from 'vue-date-pick';
import vSelect from 'vue-select';
import Checkbox from './Checkbox';
import Radio    from './Radio';
import VueTagsInput from '@johmun/vue-tags-input';
import 'vue-date-pick/dist/vueDatePick.css';
window.Craft = window.Craft || {};
d3 = d3 || {};

vSelect.props.components.default = () => ({
    Deselect: {
        render: createElement => createElement('span', {class: 'vdpClearInput' }),
    },
    OpenIndicator: {
        render: createElement => createElement('span', ''),
    },
});

export default {
    props: {
        inputStartDate: {
            type: String,
            default: ''
        },
        inputEndDate: {
            type: String
        },
        inputStartTime: {
            type: String,
            default: '10:00 AM'
        },
        inputEndTime: {
            type: String,
            default: '10:30 AM'
        },
        inputTimeZone: {
            type: String,
            default:Intl.DateTimeFormat().resolvedOptions().timeZone
        },
        inputAllDay: {
            type: Number
        },
        inputRecurring: {
            type: Number
        },
        inputRrule: {
            type: String,
            default: ''
        },
        timeSelectInterval: {
            type: Number,
            default: 30
        }
    },
    data:() =>({
        today: new Date(),
        startDate: {
            date: '',
            time: '',
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        },
        endDate: {
            date: '',
            time: '',
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        },
        allDay: 0,
        recurring: 0,
        repeat: {
            frequency: 7,
            startsOn: {
                date:'',
                time: '12:59 PM',
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
            },
            every: 1,
            on: [],
            endsOn: 0,
            occur: 1,
            until: {
                date:'',
                time: '12:59 PM',
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
            },
            by: 0,
            exclude: [],
            include: []
        },
        rrule: '',
        dateFormat: null,
        timeFormat: null,
        dateTimeFormat: null,
        monthsNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        dayNames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        frequency: [
            {
                label: Craft.t('venti', 'Does not repeat'),
                value: 7,
            },
            {
                label: Craft.t('venti', 'Daily'),
                value: 0,
            },
            {
                label: Craft.t('venti', 'Every weekday (Monday to Friday)'),
                value: 1,
            },
            {
                label: Craft.t('venti', 'Every Monday, Wednesday, and Friday'),
                value: 2,
            },
            {
                label: Craft.t('venti', 'Every Tuesday, and Thursday'),
                value: 3,
            },
            {
                label: Craft.t('venti', 'Weekly'),
                value: 4,
            },
            {
                label: Craft.t('venti', 'Monthly'),
                value: 5,
            },
            {
                label: Craft.t('venti', 'Yearly'),
                value: 6,
            }
        ],
        byLabels: [
            {
                label: Craft.t('venti', 'Day of the month'),
                value: 0
            },
            {
                label: Craft.t('venti', 'Day of the week'),
                value: 1
            }
        ],
        excludedDate:'',
        excludedDates:[],
        excludedTag:'',
        includedDate:'',
        includedDates:[],
        includedTag:'',
        updatingRepeat: false
    }),
    computed : {
        isReccuring() {
            return this.repeat.frequency !== null && this.repeat.frequency > 0 && this.repeat.frequency < 7;
        },
        datePickerFormat() {
            return this.dateFormat !== null ? this.dateFormat.toUpperCase() : 'MM/DD/YYYY';
        },
        times() {
            return Array.from({
                length: 24 * 60 / this.timeSelectInterval
                }, (v, i) => {
                    let h = Math.floor(i * this.timeSelectInterval / 60);
                    let m = i * this.timeSelectInterval - h * 60;
                    //convert to 12 hours time
                    //pad zero to minute
                    if (m < 10) {
                        m = '0' + m;
                    }
                    let label = 'AM';
                    if (h >= 12) {
                        label = 'PM';
                        h -= 12;
                    }
                    if (h === 0) {
                        h = 12;
                    }
                    return h + ':' + m + ' ' + label;
            });
        },
        startDateLabel() {
            return Craft.t('venti','Start Date');
        },
        endDateLabel() {
            return Craft.t('venti','End Date');
        },
        allDayLabel() {
            return Craft.t('venti', 'All Day');
        }
    },
    watch: {
        'startDate.date': function(val) {
            this.repeat.startsOn.date = val;
        },
        'startDate.time': function(val) {
            this.repeat.startsOn.time = val;
        },
        'endDate.date': function(val) {
            if (this.repeat.endsOn === 1) {
                this.repeat.until.date = val;
            }
        },
        'endDate.time': function(val) {
            if (this.repeat.endsOn === 1) {
                this.repeat.until.time = val;
            }
        },
        'repeat.frequency': function(val) {
            // prevent showing modal if updating repeat on inital load
            if(!this.updatingRepeat) {
                
                // reset reccuring, repeat, & rrule values
                if (val === 7) {
                    this.recurring = 0;
                    this.rrule = "";
                    this.repeat = this.repeatModel;
                }

                if(val <= 6 & val >= 0) {
                    //Show Modal
                    this.$modal.show('venti-repeat');
                }
                
                // Populate on data 
                switch (val) {
                    case 1:
                        this.repeat.on = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                        break;
                    case 2:
                        this.repeat.on = ['monday','wednesday','friday'];
                        break;
                    case 3:
                        this.repeat.on = ['tuesday','thursday'];
                        break;
                }
            }
        },
        'excludedDate': function(val) {
            if (val !== '' && !this.repeat.exclude.includes(val)) {
                this.repeat.exclude.push(val);
                this.excludedDates.push({text:val});
                this.excludedDate = '';
                this.getRule();
            }
        },
        'includedDate': function(val) {
            if (val !== '' && !this.repeat.include.includes(val)) {
                this.repeat.include.push(val);
                this.includedDates.push({text:val});
                this.includedDate = '';
                this.getRule();
            }
        }
    },
    methods: {
        formatDate(date) {
            let formatter = d3.timeFormat( window.d3TimeFormatLocaleDefinition.date );
            //console.log(date);
            //console.log(formatter(date));
            return formatter(date);
        },
        getRule() {
            const _this = this;
            let params = this.repeat;
                params.ends = this.endDate.date;
                params.endTime = this.endDate.time;

            return new Promise((resolve, reject) => {
                Craft.postActionRequest('venti/event/get-rule-string', params, function(response, textStatus) {
                    //console.log(response);
                    if (textStatus == 'success') {
                        _this.recurring = 1;
                        _this.rrule = response.rrule;
                        resolve(textStatus);
                    }
                });
            });
        },
        getMappedRules() {
            let _this = this;
            this.updatingRepeat = true;
            let params = { rrule: this.rrule };
            return new Promise((resolve, reject) => {
                Craft.postActionRequest('venti/event/repeat-object', params, function(response, textStatus) {
                    console.log(response);
                    if (textStatus == 'success') {
                        Object.assign(_this.repeat, response);
                        // Populate Tag inputs with excluded & included dates
                        _this.excludedDates = response.exclude !== undefined ? response.exclude.map(function(val){
                            return { text: val };
                        }) : [] ;
                        _this.includedDates = response.include !== undefined ? response.include.map(function(val){
                            return { text: val };
                        }) : [] ;
                        resolve(textStatus);
                    }
                });
            });
        },
        cancelModal(evt) {
            this.$modal.hide('venti-repeat');
        },
        doneModal(evt) {
            this.getRule().then((status) => { 
                this.$modal.hide('venti-repeat');
            });
        },
        excludedTagsChanged(tags) {
            this.repeat.exclude = tags.map(t => t.text);
            this.excludedDates = tags;
            this.getRule();
        },
        includedTagsChanged(tags) {
            this.repeat.include = tags.map(t => t.text);
            this.includedDates = tags;
            this.getRule();
        }
    },
    created() {
        // save a copy of the repeat model 
        this.repeatModel = JSON.parse(JSON.stringify(this.repeat));
        // populate data model from saved values
        this.startDate.date = this.inputStartDate;
        this.startDate.time = this.inputStartTime;
        this.startDate.timezone = this.inputTimeZone;
        this.endDate.date = this.inputEndDate;
        this.endDate.time = this.inputEndTime;
        this.endDate.timezone = this.inputTimeZone;
        this.repeat.startsOn.timezone = this.inputTimeZone;
        this.repeat.until.timezone = this.inputTimeZone;
        this.timezone = this.inputTimeZone;
        this.allDay = this.inputAllDay;
        this.recurring = this.inputRecurring;
        this.rrule = this.inputRrule;
        if(this.rrule !== '') {
            this.getMappedRules().then((response) => {
                this.updatingRepeat = false;
            });
        }
    },
    components: {
        RepeatModal,
        DatePick,
        vSelect,
        Checkbox,
        Radio,
        VueTagsInput
    }
}
</script>

<style lang="scss">
    .venti-input-group {
        display: flex;
        align-items: center;
    }

    .venti-input-group > .field:nth-child(2) {
        margin-left: 20px !important;
    }

    .venti-input-group .v-select,
    .venti-repeat-by .v-select {
        padding: 5px 7px;
        border: 1px solid rgba(0, 0, 20, 0.1);
        border-radius: 2px;
        -webkit-transition: border linear 50ms;
        transition: border linear 50ms;
    }

    .venti-input-group .v-select.venti-time {
        width: 120px;
    }

    .venti-input-group .vs__dropdown-toggle,
    .venti-repeat-by .vs__dropdown-toggle {
        padding: 0px;
        border: none;
    }
    .venti-input-group .vs__actions,
    .venti-repeat-by .vs__actions {
        padding: 0px;
    }
    .venti-input-group .vs__search, 
    .venti-input-gropu.vs__search:focus,
    .venti-repeat-by .vs__search,
    .venti-repeat-by .vs__search:focus {
        margin: 0px;
    }

    .venti-input-group .vs__selected,
    .venti-repeat-by .vs__selected {
        padding: 0px;
        margin: 0px;
        border: none;
    }

    .venti-input-group .vs__dropdown-menu,
    .venti-repeat-by .vs__dropdown-menu {
        border-radius: 2px;
        padding: 0 0px;
        overflow: auto;
        background: #fff;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-box-shadow: 0 0 0 1px rgba(0, 0, 20, 0.1), 0 5px 20px rgba(0, 0, 0, 0.25);
        box-shadow: 0 0 0 1px rgba(0, 0, 20, 0.1), 0 5px 20px rgba(0, 0, 0, 0.25);
    }

    .venti-input-group .v-select.venti-time .vs__dropdown-menu {
        min-width: 120px;
    }
    .venti-input-group .v-select.venti-reccuring-select {
        min-width: 300px;
    }

    .venti-repeat-on {
        margin-top: 10px;
        .checkbox-component > input + label > .input-box {
            padding: 7px;
            font-size: .8em;
            text-align: center;
            line-height: 1;
            color: transparent;
            border: none;
            border-radius: 100%;
            background-color: #f1f3f4;
        }
        .checkbox-component > input:checked + label > .input-box {
            background-color: #489AF9;
        }
        .checkbox-component > input:checked + label > .input-box:before {
            color: #ffffff;
        }
        .checkbox-component > input + label > .input-box > .input-box-tick {
            display: none;
        }
        .checkbox-component > input + label > .input-box:before {
            color: #757575;
        }
        .checkbox-component.-S > input + label > .input-box:before {
            content: 'S';
        }
        .checkbox-component.-M > input + label > .input-box:before {
            content: 'M';
        }
        .checkbox-component.-T > input + label > .input-box:before {
            content: 'T';
        }
        .checkbox-component.-W > input + label > .input-box:before {
            content: 'W';
        }
        .checkbox-component.-F > input + label > .input-box:before {
            content: 'F';
        }
    }

    .venti-repeat-by {
        margin-top: 10px;
        .v-select {
            width: 200px;
        }
        .v-select .vs__dropdown-menu {
            width: 200px;
        }
    }

    .venti-inputs {
        .field-flex {
            display: flex;
        }
        .field-onedge {
            margin: 0px;
        }
        label[for*='venti-'] {
            cursor: pointer;
        }
    }

    .v--modal-overlay .v--modal-box {
        overflow: visible;
    }
    .v--modal {
        background: transparent;
        box-shadow: none;
    }

    .box {
        width: 320px;
        border-radius: 7px;
        box-shadow: 0 1px 3px 0 rgba(60,64,67,0.302), 0 4px 8px 3px rgba(60,64,67,0.149);
        background-color: #ffffff;
        padding: 24px;

        .input {
            margin-top: 25px;
        }

        .heading {
            color: #576575;
        }
        .venti-endson {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            justify-content:space-between;
            height: 125px;
        }
        .input-row {
            display: flex;
            flex-direction: row;
            align-items: center;
            >* {
                flex: 0 0 auto;
            }

            .disabled {
                pointer-events: none;
            }

            .input-row-toggle {
                display: flex;
                align-content: center;
                width:120px;
                >:first-child {
                    margin-right: 7px;
                }

                label {
                    cursor: pointer;
                    font-weight: 700;
                    color: #576575;
                }
            }
            .radio-component {
                .input-box {
                    border-width: 2px; 
                    border-color: #489AF9;
                    .input-box-circle {
                        background-color: #489AF9;
                    }
                }
            }
        }
    }

    .box-actions {
        padding-top: 25px;
        text-align: right;
        >button:last-child {
            margin-left: 10px;
        }

        .cancel {
            cursor: pointer;
            padding: 0px 8px;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: .25px;
            line-height: 36px;
            color: #5f6368;
            border: none;
            border-radius: 2px;
            background-color: rgba(95, 99, 104, 0);

            &:hover {
                transition: background-color .2s ease-out;
                background-color: rgba(95, 99, 104, .04);
            }
        }

        .submit {
            cursor: pointer;
            padding: 0px 8px;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: .25px;
            line-height: 36px;
            color: #489AF9;
            border: none;
            border-radius: 2px;
            background-color: rgba(95, 99, 104, 0);

            &:hover {
                transition: background-color .2s ease-out;
                background-color: rgba(95, 99, 104, .04);
            }
        }
    }

    .pop-out-enter-active,
    .pop-out-leave-active {
        transition: all 0.5s;
    }
    .pop-out-enter,
    .pop-out-leave-active {
        opacity: 0;
        transform: translateY(24px);
    }
    // Tags
    /* style the background and the text color of the input ... */
  .vue-tags-input {
    max-width: 100% !important;
    background: #324652;
  }

  .vue-tags-input .ti-new-tag-input {
    background: transparent;
    color: #b7c4c9;
  }

  .vue-tags-input .ti-input {
    padding: 4px 10px;
    border: 1px solid rgba(0, 0, 20, 0.1);
    transition: border-bottom 200ms ease;
  }

  .ti-new-tag-input-wrapper {
      display: none;
  }

  /* we cange the border color if the user focuses the input */
  .vue-tags-input.ti-focus .ti-input {
    border: 1px solid #0a99f2;
  }

  /* some stylings for the autocomplete layer */
  .vue-tags-input .ti-autocomplete {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 20, 0.1);
    border-top: none;
  }

  /* the selected item in the autocomplete layer, should be highlighted */
  .vue-tags-input .ti-item.ti-selected-item {
    background: #0a99f2;
    color: #333;
  }

  /* style the placeholders color across all browser */
  .vue-tags-input ::-webkit-input-placeholder {
    color: #a4b1b6;
  }

  .vue-tags-input ::-moz-placeholder {
    color: #a4b1b6;
  }

  .vue-tags-input :-ms-input-placeholder {
    color: #a4b1b6;
  }

  .vue-tags-input :-moz-placeholder {
    color: #a4b1b6;
  }

  /* default styles for all the tags */
  .vue-tags-input .ti-tag {
    position: relative;
    background: #0a99f2;
    color: #ffffff;
  }

  /* we defined a custom css class in the data model, now we are using it to style the tag */
  .vue-tags-input .ti-tag.custom-class {
    background: transparent;
    border: 1px solid rgba(0, 0, 20, 0.1);
    color: #333333;
    margin-right: 4px;
    border-radius: 0px;
    font-size: 13px;
  }

  /* the styles if a tag is invalid */
  .vue-tags-input .ti-tag.ti-invalid {
    background-color: #e88a74;
  }

  /* if the user input is invalid, the input color should be red */
  .vue-tags-input .ti-new-tag-input.ti-invalid {
    color: #e88a74;
  }

  /* if a tag or the user input is a duplicate, it should be crossed out */
  .vue-tags-input .ti-duplicate span,
  .vue-tags-input .ti-new-tag-input.ti-duplicate {
    text-decoration: line-through;
  }

  /* if the user presses backspace, the complete tag should be crossed out, to mark it for deletion */
  .vue-tags-input .ti-tag:after {
    transition: transform .2s;
    position: absolute;
    content: '';
    height: 2px;
    width: 108%;
    left: -4%;
    top: calc(50% - 1px);
    background-color: #000;
    transform: scaleX(0);
  }

  .vue-tags-input .ti-deletion-mark:after {
    transform: scaleX(1);
  }
</style>