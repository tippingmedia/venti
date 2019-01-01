<template>
    <transition name="fade">
        <div class="dialog-area" v-if="visible">
            <div class="dialog-card">
                <h3 v-text="heading"></h3>
                <div class="dialog-options" v-if="recurring">
                    <div class="dialog-options-input">
                        <input type="radio" id="delete-dialog-option-1" v-model="option" value="thisevent" role="radio" />
                        <label for="delete-dialog-option-1">
                            <div class="option-icons">
                                <span class="option-on">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/><circle cx="12" cy="12" r="5"/></svg>
                                </span>
                                <span class="option-off">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>
                                </span>
                            </div>
                            <div v-text="optionOneLabel"></div>
                        </label>
                    </div>
                    <div class="dialog-options-input">
                        <input type="radio" id="delete-dialog-option-2" v-model="option" value="allevents" role="radio" />
                        <label for="delete-dialog-option-2">
                            <div class="option-icons">
                                <span class="option-on">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/><circle cx="12" cy="12" r="5"/></svg>
                                </span>
                                <span class="option-off">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>
                                </span>
                            </div>
                            <div v-text="optionTwoLabel"></div>
                        </label>
                    </div>
                </div>
                <div class="dialog-actions">
                    <div class="btn" @click="close" role="button">Cancel</div>
                    <div class="btn" @click="action" role="button" autofocus>OK</div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    window.Craft = window.Craft || {};
    export default {
        data() {
            return {
                recurring: false,
                visible: false,
                option: 'thisevent',
                optionOneLabel: 'This Event',
                optionTwoLabel: 'All Events'
            }
        }, 
        computed: {
            heading() {
                return this.recurring ? 'Delete recurring event' : 'Delete event';
            }
        },
        methods: {
            action() {
                Event.$emit('DeleteDialogAction', { action: this.option });
                this.close();
            },
            close() {
                this.visible = false;
            }
        },
        created() {
            const _this = this;
            Event.$on("DeleteDialogOpen", (data) => {
                _this.recurring = data.event.extendedProps !== undefined && data.event.extendedProps.recurring == 1 ? true : false;
                _this.visible = true;
            });
        }
    }
</script>

<style>
    .dialog-card {
        position: realtive;
        width: 300px;
        max-width: 300px;
        padding: 2rem;
        color: #3B3B3B;
        flex-shrink: 1;
        background-color: #ffffff;
        border-radius: 3px;
        box-shadow: 0px 2px 12px 0px rgba(0,0,0,.3);
        /* transform: translate(0px, 0px); */
        transform-origin: 0px 0px 0px;
        transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
        z-index: 102;
    }
    .dialog-area {
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
        flex-direction: row;
        justify-content: center;
        align-items: center;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        bottom: 0;
        left: 0;
        padding: 0 0;
        -webkit-perspective: 1000;
        position: absolute;
        right: 0;
        top: 0;
        z-index: 101;
        background-color: rgba(0,0,0,.4);
    }

    .dialog-card h3 {
        align-items: center;
        font-size: 1.3rem;
        font-weight: 300;
        line-height: 1;
    }
    .dialog-options {
        margin-top: 1rem;
    }

    .dialog-options-input:not(:first-child) {
        margin-top: 5px;
    }

    .dialog-options-input > input {
        position: absolute;
        z-index: -1;
        opacity: 0;
    }
    .dialog-options-input > input:checked ~ label .option-on {
        opacity: 1;
    }
    .dialog-options-input > input:checked ~ label .option-off {
        opacity: 0;
    }
    .dialog-options-input > input:checked ~ label svg {
        fill: rgba(39,115,186,1.00) !important;
    }
    .dialog-options-input > input:focus ~ label {
        color: rgba(39,115,186,1.00);
    }
    .dialog-options-input > label {
        display: flex; 
        flex-direction: row;
        align-self: center;
        cursor: pointer;
    }
    .option-icons {
        position: relative;
        width: 20px;
        height: 20px;
        padding-right: 10px;
        flex-grow: 0;
        overflow: hidden;
    }
    .option-icons > span {
        position: absolute;
        top: 0px;
        left: 0px;
        transition: opacity 150ms ease-out;
    }
    .option-icons > .option-on {
        opacity: 0;
    }
    .option-icons > .option-off {
        opacity: 1;
    }

    .option-icons svg {
        fill: #5f6368;
    }

    .dialog-actions {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        align-items: center;
        margin-top: 1.2rem;
    }
    .dialog-actions > div:last-child {
        margin-left: 10px;
    }

</style>
