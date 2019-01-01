<template>
    <transition name="fade">
        <div class="card-area" v-show="visible" ref="eventCardArea">
            <div class="card-pad-top" ref="eventCardPadTop"></div>
            <div class="card" ref="eventDetailCard" style="">
                <div class="card-header">
                    <div class="card-action" @click='edit' aria-label="Edit Event" role="button" data-tooltip="Edit" title="Edit" v-if="manageEvent">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM5.92 19H5v-.92l9.06-9.06.92.92L5.92 19zM20.71 5.63l-2.34-2.34c-.2-.2-.45-.29-.71-.29s-.51.1-.7.29l-1.83 1.83 3.75 3.75 1.83-1.83c.39-.39.39-1.02 0-1.41z"/></svg>
                    </div>
                    <div class="card-action" @click="deleteDialog" aria-label="Delete Event" role="dialog" data-tooltip="Delete" title="Delete" v-if="manageEvent">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/></svg>
                    </div>
                    <div class="card-action" @click="close" aria-label="Close Event" role="button" data-tooltip="Close" title="Close">
                        <svg xmlns="https://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path></svg>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <span :style="groupAccent" class="card-group-accent" title="event.extendedProps.group"></span> <h2 v-text="event.title"></h2>
                    </div>
                    <p v-text="startDate"></p>
                    <p v-if="event.extendedProps !== undefined && event.extendedProps.recurring == 1" v-text="event.extendedProps.summary"></p>
                </div>
                <div class="card-footer">
                </div>
            </div>
            <div class="card-pad-bottom"></div>
        </div>
    </transition>
</template>

<script>
    import { toDateTime } from 'fullcalendar/dist/plugins/luxon';
    export default {
        data() {
            return {
                event: {},
                jsEvent: {},
                el: {},
                elmRec: { height: 0, width: 0, top: 0, left: 0, x: 0, y: 0 },
                cardRec: { height: 0, width: 0, top: 0, left: 0, x: 0, y: 0 },
                width: 448,
                visible: false,
                lang: Craft.primarySiteLanguage,
                manageEvent: true
            }
        },
        computed: {
            groupAccent() {
                return `background-color: ${ this.event.backgroundColor };`;
            },
            startDate() {
                //console.log(toDateTime);
                if(this.event.extendedProps !== undefined) {
                    let dt = toDateTime(this.event.start, this.$parent.calendar);
                    let edt = toDateTime(this.event.end, this.$parent.calendar);
                    return `${ dt.setLocale(this.lang).toFormat("DDDD") } • ${ dt.setLocale(this.lang).toFormat("t") } - ${ edt.setLocale(this.lang).toFormat("t") }`;
                }
            }
        },
        methods: {
            open() {
                this.visible = true;
            },
            close() {
                this.visible = false;
            },
            edit() {
                window.location.href = this.event.url;
            },
            delete(data) {
                let _this = this;
                let exDate = this.event.start.toUTCString();
                let cpData = { "eventId": this.event.id, "siteId": this.event.extendedProps.siteId, "groupId": this.event.groupId, "exDate": exDate };
                // action = allevents || thisevent
                if(data.action === 'thisevent') {
                    Craft.postActionRequest('venti/event/remove-occurence', cpData, function(response, textStatus) {
                        if (textStatus == 'success') {
                            Craft.cp.displayNotice( Craft.t('Venti','Event Occurence Removed'));
                            // refresh events to show updates
                            _this.event.source.refetch();
                        }
                    });
                }

                if(data.action === 'allevents') {
                    Craft.postActionRequest('venti/event/delete-event', cpData, function(response, textStatus) {
                        if (textStatus == 'success') {
                            Craft.cp.displayNotice( Craft.t('Venti','Event Deleted') );
                            // refresh events to show updates
                            _this.event.source.refetch();
                            // the event is gone the modal is no longer needed
                            _this.close();
                        }
                    });
                }
            },
            deleteDialog() {
                Event.$emit('DeleteDialogOpen', { event: this.event });
            },
            getPosition() {
                const elm = this.el;
                const w = window;
                const d = document;
                const e = d.documentElement;
                const g = d.getElementsByTagName('body')[0];
                const x = w.innerWidth || e.clientWidth || g.clientWidth;
                const y = w.innerHeight|| e.clientHeight|| g.clientHeight;

                if(this.elmRec !== undefined && this.$refs.eventDetailCard !== undefined) {
                    // set the cards Rect data
                    this.cardRec = this.$refs.eventDetailCard.getBoundingClientRect();
                    if( (this.elmRec.left + this.elmRec.width) + this.width < x ) {
                        let left = (this.elmRec.left + this.elmRec.width)
                        let top = (this.elmRec.top);
                        
                        if ((this.elmRec.top + this.cardRec.height) > y) {
                            top = (y - this.cardRec.height);
                        }

                        return { 
                            'left': left,
                            'top': top
                        }; 
                    } else {
                        let left = (this.elmRec.left - this.width);
                        let top = (this.elmRec.top)

                        if ( (this.elmRec.top + this.cardRec.height) > y ) {
                            top = (y - this.cardRec.height);
                        }

                        return { 
                            'left': left,
                            'top': top
                        };
                    }
                }

                return null;
            },
            transform() {
                //let eventDetailCard = this.$refs.eventDetailCard;
                let eventCardArea = this.$refs.eventCardArea;
                let eventCardPadTop = this.$refs.eventCardPadTop;

                eventCardArea.style.setProperty('--cardAreaMarginL', this.getPosition().left);
                eventCardPadTop.style.setProperty('--maxH', this.getPosition().top);
                eventCardPadTop.style.setProperty('--minH', this.getPosition().top - this.cardRec.height);
            }
        },
        created() {
            const _this = this;
            Event.$on("EventModalOpen", (data) => {
                // set card's content
                this.event = data.event;
                console.log(data.event);
                // event permissions
                this.manageEvent = data.event.source.internalEventSource.extendedProps.canManageEvents;
                // get the clicked events Rect data
                this.elmRec = data.el.getBoundingClientRect();
                // open card
                this.open();
                // reposition event detail card
                this.transform();
                // set the target of the click
                this.el = data.jsEvent.target;
            });

            Event.$on("DeleteDialogAction", (data) => {
                this.delete(data);
            });
        }
    }
</script>

<style>
    :root {
        --cardX: 0;
        --cardY: 0;
        --cardAreaMarginL: 0;
        --maxH: 0;
        --minH: 0;
    }
    .card {
        width: 448px;
        max-width: 448px;
        flex-shrink: 1;
        background-color: #ffffff;
        border-radius: 3px;
        box-shadow: 0px 2px 12px 0px rgba(0,0,0,.3);
        pointer-events: all;
        /* transform: translate(0px, 0px); */
        transform-origin: 0px 0px 0px;
        transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }

    .card.trpos {
        transform-origin: 0px 0px 0px;
        transform: translateX(calc(var(--cardX) * 1px)) translateY(calc(var(--cardY) * 1px));
        transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-body {
        padding: 2rem;
        padding-top: 0;
        color: #3B3B3B;
    }

    .card-body > div {
        display: flex;
        align-items: center;
    }

    .card-body h2 {
        flex-grow: 1;
        align-items: center;
        font-size: 2rem;
        font-weight: 300;
        line-height: 1;
        padding-left: 10px;
        margin: 0px;
    }
    .card-body > p {
        margin: 0px;
        margin-bottom: 5px;
    }
    .card-body > div {
        margin-bottom: 1rem;
    }
    .card-area {
        -webkit-box-align: center;
        box-align: center;
        -webkit-align-items: center;
        align-items: flex-start;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        margin-left: calc(var(--cardAreaMarginL) * 1px);
        /* -webkit-flex-direction: column; */
        flex-direction: column;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        bottom: 0;
        left: 0;
        padding: 0 0;
        -webkit-perspective: 1000;
        position: absolute;
        right: 0;
        top: 0;
        -webkit-transition: -webkit-transform 150ms cubic-bezier(0.4,0.0,0.2,1);
        transition: -webkit-transform 150ms cubic-bezier(0.4,0.0,0.2,1);
        -webkit-transition: transform 150ms cubic-bezier(0.4,0.0,0.2,1);
        transition: all 150ms cubic-bezier(0.4,0.0,0.2,1);
        pointer-events: none;
        z-index: 101;
    }
    .card-pad-top,
    .card-pad-bottom {
        flex-grow: 1;
    }

    .card-pad-top {
        width: 0px;
        min-height: calc(var(--minH) * 1px);
        max-height: calc(var(--maxH) * 1px);
        transition: all 150ms cubic-bezier(0.4,0.0,0.2,1);
    }

    .card-header {
        padding: 3px;
        display: flex;
        justify-content: flex-end;
    }

    .card-action {
        position: relative;
        display: inlne-block;
        height: 40px;
        width: 40px;
        opacity: 1;
        flex-grow: 0;
        color: #5f6368;
        fill: #5f6368;
        transition: background .3s;
        cursor:pointer;
        outline:none;
        overflow: hidden;
        border-radius: 50%;
        text-align: center;
    }
    .card-action:hover {
        background-color: rgba(32,33,36,0.039);
    }

    /* .card-action:before {
        content: attr(data-tooltip);
        position: absolute;
        top: 40px;
        left: 10px;
        color: #ffffff;
        padding: 5px;
        border-radius: 3px;
        background-color: rgba(0,0,0,.7);
        opacity: 0;
        transition: all 150ms ease-out;
    }
    .card-action:hover:after {
        opacity: 1;
        transition: all 150ms ease-out;
        transform: translateY(10px);
    } */

    .card-action > svg {
        display: inline-block;
        top: 10px;
        position: relative;
    }

    .card-group-accent {
        display: inline-block;
        flex-grow: 0;
        border-radius: 50%;
        height: 14px;
        width: 14px;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }

    .fade-enter-active, 
    .fade-leave-active {
        transition: opacity 150ms ease-out;
    }
    
</style>
