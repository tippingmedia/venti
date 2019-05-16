<style lang="scss">
    .checkbox-component {
        display: inline;
        margin-right: 5px;
        > input {
            opacity: 0;
            position: absolute;
            cursor: pointer;

            + label {
                cursor: pointer;
            }

            + label > .input-box {
                display: inline-block;
                border: 2px solid #0D99F2;
                border-radius: 14%;
                margin: 0;
                padding: 0;
                width: 1em;
                height: 1em;
                background: #ffffff;
                overflow: hidden;
                vertical-align: -5%;
                user-select: none;
                cursor: pointer;
                transform: background .2s ease-in;

                > .input-box-tick {
                    width: 100%;
                    height: 100%;

                    > path {
                        opacity: 0;
                        stroke: #0D99F2;
                        stroke-width: 2.3px;
                        stroke-dashoffset: 20;
                        stroke-dasharray: 20;
                        transition: stroke-dashoffset 0.2s ease-out;
                    }
                }
            }

            &:checked + label > .input-box {
                background: #0D99F2;
                transform: background .2s ease-in;
                > .input-box-tick {
                    > path {
                        stroke: #ffffff;
                    }
                }
            }

            &:checked + label > .input-box > .input-box-tick > path {
                opacity: 1;
                stroke-dashoffset: 0;
            }

            &:focus + label > .input-box {
                box-shadow: 0 0 1px 2px rgba(13, 153, 242, 0.3);
            }

            &:disabled + label {
                opacity: 0.7;
            }
        }
    }
</style>

<template>
    <div class="checkbox-component">
        <input type="checkbox"
               :id="id"
               :name="name"
               :value="value"
               :class="className"
               :required="required"
               :disabled="disabled"
               @change="onChange"
               :checked="state">
        <label :for="id">
            <slot name="input-box">
                <span class="input-box">
                    <svg class="input-box-tick" viewBox="0 0 16 16">
                        <path fill="none" d="M1.7,7.8l3.8,3.4l9-8.8"></path>
                    </svg>
                </span>
            </slot>
            <slot></slot>
        </label>
    </div>
</template>

<script>
    export default {
        model: {
            prop: 'modelValue',
            event: 'input'
        },

        props: {
            id: {
                type: String,
                default: function () {
                    return 'checkbox-id-' + this._uid;
                },
            },
            name: {
                type: String,
                default: null,
            },
            value: {
                default: null,
            },
            modelValue: {
                default: undefined,
            },
            className: {
                type: String,
                default: null,
            },
            checked: {
                type: Boolean,
                default: false,
            },
            required: {
                type: Boolean,
                default: false,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            model: {}
        },

        computed: {
            state() {
                if (this.modelValue === undefined) {
                    return this.checked;
                }

                if (Array.isArray(this.modelValue)) {
                    return this.modelValue.indexOf(this.value) > -1;
                }

                return !!this.modelValue;
            }
        },

        methods: {
            onChange() {
                this.toggle();
            },

            toggle() {
                let value;

                if (Array.isArray(this.modelValue)) {
                    value = this.modelValue.slice(0);

                    if (this.state) {
                        value.splice(value.indexOf(this.value), 1);
                    } else {
                        value.push(this.value);
                    }
                } else {
                    value = !this.state;
                }

                this.$emit('input', value);
            }
        },

        watch: {
            checked(newValue) {
                if (newValue !== this.state) {
                    this.toggle();
                }
            }
        },

        mounted() {
            if (this.checked && !this.state) {
                this.toggle();
            }
        },
    };
</script>