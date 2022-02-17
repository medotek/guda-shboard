import store from './app'
import axios from 'axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        async setDiscordWebhook(_, form) {
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
        async getWebhooksList(_, page) {
            return axios.get(`/discord/webhook/list?page=${page}`).then((res) => {
                return res
            })
        },
        async getWebhookDetail(_, webhookId) {
            return axios.get(`/discord/webhook/${webhookId}`).then((res) => {
                return res
            })
        },
        /**
         *
         * @param _
         * @param url
         * @param isList
         */
        async setHoyolabPost(_, url, isList) {
            let newUrl = new URL(url);
            let params ='?'
            if (isList) {params += 'list=true'} else {params += 'post=true'}
            let id = url.substring(url.lastIndexOf('/') + 1);
            if (!id ||typeof parseInt(id) !== 'number' || newUrl.origin !== 'https://www.hoyolab.com')
                return {status:400, error:'error thrown'}
            return axios.post(`/hoyolab/post/new/${id}${params}`).then((res) => {
                return res
            })
        }
    },
}
