import store from './app'
import axios from 'axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        async getDiscordWebhook(_, form) {
            store.commit('setLoading', true)
            return axios.post('/discord/webhook/new', form).then((res) => {
                store.commit('setLoading', false)
                return res
            }).catch((e) => {
                store.commit('setLoading', false)
                console.log(e)
                return e
            })
        },
        /**
         * Get a list of discord webhook of the user
         */
        async getWebhooksList(_, userId) {
            return axios.post('/discord/webhook/list', userId).then((res) => {
                return res
            })
        }
    },
}
