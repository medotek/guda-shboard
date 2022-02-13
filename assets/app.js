import Vue from 'vue';
import App from './components/App';
import router from "./router/app";
import store from "./store/app";
import axios from "axios";
// import controller style
import './styles/controllers/gudashboard-auth.scss'
import BootstrapVue from 'bootstrap-vue/dist/bootstrap-vue.esm';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import 'bootstrap/dist/css/bootstrap.css';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowLeft } from '@fortawesome/free-solid-svg-icons'
import { faDiscord } from '@fortawesome/free-brands-svg-icons';

library.add(faArrowLeft, faDiscord);

Vue.use(BootstrapVue);
Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.config.productionTip = false
axios.defaults.baseURL = 'http://localhost:8000/api'

require('./store/subscriber')

store.dispatch('auth/attempt', localStorage.getItem('token')).then(() => {
    new Vue({
        router,
        store,
        render: h => h(App),
    }).$mount('#root')
})

