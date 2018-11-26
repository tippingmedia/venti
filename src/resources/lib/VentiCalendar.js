import Vue from "vue";
import store from './store/venticalendar';
import CalendarApp from './components/CalendarApp';

window.Event = new Vue();

new Vue({
    el: '#venti-calendar-app',
    store,
    data: {
        dateFormat: '',
        timeFormat: ''
    },
    components: {
        CalendarApp
    }
});