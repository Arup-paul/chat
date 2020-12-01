require("./bootstrap");

window.Vue = require("vue");
//vuex

import Vuex from "vuex";
Vue.use(Vuex);

import storeVuex from './store/index';
import filter from './filter';
const store = new Vuex.Store(storeVuex);

//chat scroll
import VueChatScroll from 'vue-chat-scroll'
Vue.use(VueChatScroll)

Vue.component("mainapp", require("./components/MainApp.vue").default);

const app = new Vue({
    el: "#app",
    store
});
