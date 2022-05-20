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
        async setHoyolabPost(_, data) {
            let newUrl = new URL(data.url);
            let isList = data.isList
            let params ='?'
            let id = ''
            if (isList) {
                id = newUrl.searchParams.get('id') ?? ''
                params += 'list=true'
            } else {
                id = data.url.substring(data.url.lastIndexOf('/') + 1);
                params += 'post=true'
            }

            if (!id ||typeof parseInt(id) !== 'number' || newUrl.origin !== 'https://www.hoyolab.com')
                return {status:400, error:'error thrown'}
            return axios.post(`/hoyolab/post/new/${id}${params}`).then((res) => {
                return res
            })
        },
        async getHoyoStats(_, uid) {
            return await axios.get(`/hoyolab/user/${uid}/stats`).then(res => {
                return res.data
            })
        },
        async getHoyoUserAnalytics(_, data) {
            console.log(data)
            return await axios.get(`/hoyolab/user/${data.uid}/analytics/${data.period}`).then(res => {
                return res.data
            })
        },
        async getHoyoUserPostList(_, data) {
            return await axios.get(`/hoyolab/user/${data.uid}/posts?page=${data.page}`).then(res => {
                return res.data
            })
        },
        async getHoyoUser(_, uid) {
            return await axios.get(`/hoyolab/user/${uid}`).then(res => {
                return res.data
            })
        },
        async getHoyoUsers() {
            return await axios.get('/hoyolab/users').then(res => {
                return res.data
            })
        },
        async setHoyolabWebhook(_, form) {
            return axios.post(`/hoyolab/user/${form.uid}/webhookcronjob`, form).then((res) => {
                return res
            }).catch((e) => {
                console.log(e)
                return e
            })
        },
    },
}
