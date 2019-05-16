import Vue from "vue";
import DateTimeInputs from './components/DateTimeInputs.vue';
import VModal from "vue-js-modal";

window.Event = new Vue();
Vue.use(VModal);

new Vue({
    el: '#venti-input',
    components: {
        DateTimeInputs
    }
});