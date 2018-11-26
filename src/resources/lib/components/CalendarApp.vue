<template>
    <div id="venti-calendar" class="venti-calendar"></div>
</template>
<script>
    import { Calendar } from 'fullcalendar';
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
                defaults : {
                    header : {
                        left: 'title',
                        center: '',
                        right: this.options.multisite == "true" ? "today prev,next" : "prev,next"
                    },
                    editable: true,
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
                let elm = info.el;
                elm.dataset.id = info.event.id;
                elm.dataset.site = info.event.def.extendedProps.siteId;
                console.log(info.event);

                if (info.event.def.extendedProps.multiDay || info.event.def.allDay) {
                    elm.classList.add('fc-event-multiday');
                } else {
                    elm.classList.add('fc-event-singleday');
                    let content = elm.querySelector('.fc-content');
                    let span = document.createElement('span');
                    span.classList.add('event_group_color');
                    
                    content.prepend(span);
                    span.style.backgroundColor = info.event.def.backgroundColor;
                }
            },
            eventClick(info) {
                /* Cancel default behavior */
                info.jsEvent.preventDefault();
            },
            loading(info) {
                //console.log(info);
            }
        },
        mounted() {
            let cal = document.getElementById('venti-calendar');
            this.calendar = new Calendar(cal, this.settings);
            // Set up handlers
            //this.calendar.on('datesRender', this.datesRender);
            this.calendar.on('eventRender', this.eventRender);
            this.calendar.on('eventClick', this.eventClick);
            //this.calendar.on('loading', this.loading);
            // Render Calendar
            this.calendar.render();
        }
    }
</script>