import Vue from "vue";
import Vuex from "vuex";
Vue.use(Vuex);

import createPersistedState from 'vuex-persistedstate';

import axios from "axios";

export default new Vuex.Store({
  state: {
    events: [],
    groups: [],
    checkedGroups: []
  },
  getters: {
    isGroupChecked: (state, getters) => id => {
      return state.checkedGroups.includes(id);
    }
  },
  mutations: {
    setGroups(state, payload) {
      state.groups = payload;
    },
    setCheckedGroups(state, payload) {
      state.checkedGroups = payload;
    },
    setEvents(state, payload) {
      state.events = payload;
    },
    setDayEvents(state, payload) {
      state.dayEvents = payload;
    }
  },
  actions: {
    loadMonthEvents({ commit, state }, groups) {
      let _monthStart = moment(
        `${state.currentYear}-${state.currentMonth}-1`,
        "YYYY-M-D"
      ).format("YYYY-MM-DD");
      let _monthEnd = moment(
        `${state.currentYear}-${state.currentMonth}-1`,
        "YYYY-M-D"
      )
        .endOf("month")
        .format("YYYY-MM-DD");
      let url = `/api/monthEvents/${groups.join(
        ","
      )}/${_monthStart}/${_monthEnd}.json`;

      axios
        .get(url)
        .then(response => {
          commit("setEvents", response.data.data);
          //_this.$store.state.events = response;
        })
        .catch(response => {
          console.log(response);
        });
    },

    loadFeaturedEvents({ commit, state }, groups) {
      let url = `/api/featuredEvents/${groups.join(",")}.json`;

      axios
        .get(url)
        .then(response => {
          commit("setFeaturedEvents", response.data.data);
        })
        .catch(response => {
          console.log(response);
        });
    },

    loadGroups({ commit, state }) {
      const _this = this;
      let url = `api/eventGroups`;

      axios
        .get(url)
        .then(response => {
          commit("setGroups", response.data.groups);
          if (state.checkedGroups.length == 0) {
            let groups = [];
            for (let i = 0; i < state.groups.length; i++) {
              groups.push(parseInt(state.groups[i].id));
            }
            commit("setCheckedGroups", groups);
          }
          Event.$emit("EventGroupsLoaded");
        })
        .catch(response => {
          console.log(response);
        });
    }
  },
  plugins: [createPersistedState({ key: 'venticalendar' })]
});
