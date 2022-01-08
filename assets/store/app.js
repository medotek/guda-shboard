import Vue from 'vue'
import Vuex from 'vuex'
import auth from './auth'
import Register from "../views/Register";

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        isLoading: false,
        isPasswordStrong: false
    },
    getters: {
        getLoading(state) {
          return state.isLoading
        },
        getPasswordStrength(state) {
            return state.isPasswordStrong
        }
    },
    mutations: {
        setLoading(state, newLoadingState) {
            state.isLoading = newLoadingState
        },
        setPasswordStrength(state, newPasswordStrengthState) {
            state.isPasswordStrong = newPasswordStrengthState
        },
    },
    actions: {

    },
    modules: {
        auth
    }
})
