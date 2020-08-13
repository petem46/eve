require("./bootstrap");

import Vue from "vue";
import App from "./views/App";
import router from "./router";
import store from "./store";
import Vuetify from "vuetify";
import VueFilterDateFormat from "@vuejs-community/vue-filter-date-format";
import VueFilterDateParse from "@vuejs-community/vue-filter-date-parse";
import colors from "vuetify/lib/util/colors";
import vueFilterPrettyBytes from 'vue-filter-pretty-bytes'
import ClickOutside from 'vue-click-outside'
import VueCountdown from '@chenfengyuan/vue-countdown';

Vue.use(VueFilterDateFormat);
Vue.use(VueFilterDateParse);
Vue.use(vueFilterPrettyBytes);
Vue.use(ClickOutside);
Vue.component(VueCountdown.name, VueCountdown);
// Vue.axios.defaults.baseURL = process.env.MIX_APP_URL;

Vue.component('example-component', require('./components/ExampleComponent.vue').default);


Vue.use(Vuetify);

export default new Vuetify({
  theme: {
    light: {
      a: colors.teal,
      primary: colors.teal,
      warning: "#F57C00",
      "dark-orange": "#F57C00"
    }
  },
  icons: {
    iconfont: "fa"
  }
});

const app = new Vue({
  components: {
    App
  },
  el: "#app",
  router,
  store,
  vuetify: new Vuetify()
});
