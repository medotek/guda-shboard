import Vue from 'vue';
import App from './components/App';
import router from "./router/app";
import store from "./store/app";
import axios from "axios";
// import controller style
import './styles/controllers/gudashboard-auth.scss'

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

