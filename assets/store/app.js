import Vue from 'vue'
import Vuex from 'vuex'
import auth from './auth'
import Register from "../views/Register";

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        isLoading: false
    },
    getters: {
        getLoading(state) {
          return state.isLoading
        }
    },
    mutations: {
        setLoading(state, newLoadingState) {
            state.isLoading = newLoadingState
        }
    },
    actions: {

    },
    modules: {
        auth
    }
})
