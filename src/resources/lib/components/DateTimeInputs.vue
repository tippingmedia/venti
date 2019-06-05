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
                <v-select :options="times" v-model="startDate.time" class="venti-time" :clearable="false"></v-select>
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
                <v-select :options="times" v-model="endDate.time" class="venti-time" :clearable="false"></v-select>
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
                <v-select :options="frequency"  :searchable="false" :clearable="false" :selectOnTab="true" v-model="repeat.frequency" :reduce="option => option.value" placeholder="Does not repeat" class="venti-reccuring-select"></v-select>
            </div>
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
        updatingRepeat: false
    }),
    computed : {
        isReccuring() {
            return this.repeat.frequency !== null && this.repeat.frequency > 0;
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
                    if (h > 12) {
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
        }
    },
    methods: {
        formatDate(date) {
            let formatter = d3.timeFormat( window.d3TimeFormatLocaleDefinition.date );
            console.log(date);
            console.log(formatter(date));
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
        }
    },
    created() {
        this.startDate.date = this.inputStartDate;
        this.startDate.time = this.inputStartTime;
        this.endDate.date = this.inputEndDate;
        this.endDate.time = this.inputEndTime;
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
        Radio
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
</style>