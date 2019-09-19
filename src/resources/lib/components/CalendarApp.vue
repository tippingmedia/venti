<template>
    <div>
        <div id="venti-calendar" class="venti-calendar"></div>
        <event-modal></event-modal>
        <delete-dialog></delete-dialog>
    </div>
</template>
<script>
    import { Calendar } from '@fullcalendar/core';
    import dayGridPlugin from '@fullcalendar/daygrid';
    import interactionPlugin from '@fullcalendar/interaction';
    import EventModal from './EventModal.vue';
    import DeleteDialog from './DeleteDialog.vue';
    window.Craft = window.Craft || {};
    export default {
        props:[
            'options'
        ],
        data() {
            const _this = this;
            return {
                siteId: Craft.siteId,
                calendar: {},
                selectedEvent: {},
                defaults : {
                    plugins:[ dayGridPlugin, interactionPlugin ],
                    header : {
                        left: 'title',
                        center: '',
                        right: this.options.multisite == "true" ? "today prev,next" : "prev,next"
                    },
                    editable: true,
                    eventStartEditable: false,
                    eventDurationEditable: false,
                    eventLimit: 6
                }
            }
        },
        computed: {
            settings() {
                return Object.assign({ eventSources:this.options.eventSources }, this.defaults);
            }
        },
        methods: {
            groupToggles() {

            },
            datesRender(info) {
                // console.log('DATES RENDER');
                // console.log(info);
            },
            eventRender(info) {
                //console.log(info.event);
                let elm = info.el;
                elm.dataset.id = info.event.id;
                elm.dataset.site = info.event.extendedProps.siteId;

                if (info.event.extendedProps.multiDay || info.event.allDay) {
                    elm.classList.add('fc-event-multiday');
                } else {
                    elm.classList.add('fc-event-singleday');
                    let content = elm.querySelector('.fc-content');
                    let span = document.createElement('span');
                    span.classList.add('event_group_color');
                    
                    content.prepend(span);
                    span.style.backgroundColor = info.event.backgroundColor;
                }
            },
            eventClick(info) {
                /* Cancel default behavior */
                info.jsEvent.preventDefault();
//                console.log(info);
                this.selectedEvent = info.event;

                Event.$emit('EventModalOpen', { event:info.event, jsEvent: info.jsEvent, el: info.el });
            },
            dateClick(info) {
                console.info("DATE CLICKED");
                console.log(info);
            },
            eventDrop(info) {
                console.info("EVENT DROPPED");
                console.log(info);
            },
            loading(info) {
                //console.log(info);
            }
        },
        mounted() {
            const _this = this;
            let cal = document.getElementById('venti-calendar');
            this.calendar = new Calendar(cal, this.settings);
            // Set up handlers
            //this.calendar.on('datesRender', this.datesRender);
            this.calendar.on('eventRender', this.eventRender);
            this.calendar.on('eventClick', _this.eventClick);
            this.calendar.on('dateClick', this.dateClick);
            this.calendar.on('eventDrop', this.eventDrop);
            //this.calendar.on('loading', this.loading);
            // Render Calendar
            this.calendar.render();
        },
        components: {
            EventModal,
            DeleteDialog
        }
    }
</script>