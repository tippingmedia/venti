import Vue from "vue";
import DateTimeInputs from './components/DateTimeInputs.vue';
import VModal from "vue-js-modal";

window.Event = new Vue();
Vue.use(VModal);

let ventiInput = document.getElementById('venti-input');
if(ventiInput !== null ) {
    new Vue({
      el: "#venti-input",
      components: {
        DateTimeInputs
      }
    });
}