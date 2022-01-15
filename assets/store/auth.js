import store from './app'
import axios from 'axios'

export default {
    namespaced: true,
    state: {
        token: null,
        user: null,
        discordUser: null,
        discordAccessToken: null,
        discordRefreshToken: null
    },
    getters: {
        isAuthenticated(state) {
            return state.token && state.user
        },
        isDiscordLinked(state) {
            return state.user
        },
        getUser(state) {
            return state.user
        },
        getDiscordUser(state) {
            return state.discordUser
        }
    },
    mutations: {
        setToken(state, token) {
            state.token = token
        },
        setUser(state, user) {
            state.user = user
        },
        setDiscordUser(state, discordUser) {
            state.discordUser = discordUser
        },
    },
    actions: {
        async login({dispatch}, credentials) {
            store.commit('setLoading', true)
            let response = await axios.post('/login', credentials)
                .catch((e) => {
                    store.commit('setLoading', false);
                    console.log(e);
                })

            return dispatch('attempt', response.data.token)
        },
        async discordLogin({dispatch}, data) {
            if (store.getters['auth/isAuthenticated']) {
                let response = await axios.post('/discord/register', data).catch((e) => {
                        console.log(e);
                    })
                return dispatch('attempt', this.isAuthenticated.token)
            } else {
                return next({
                    name: 'login'
                })
            }
        },
        async attempt({commit, state}, token) {
            if (token) {
                commit('setToken', token)
            }

            if (!state.token) {
                return
            }

            let user = null;
            try {
                let response = await axios.get('/profile')
                user = JSON.parse(response.data)
                commit('setUser', user)
            } catch (e) {
                commit('setUser', null)
                commit('setToken', null)
            }

            if (user) {
                // Commit Discord User - Session
                try {
                    let response = await axios.get(
                        'https://discord.com/api/v8/users/@me',
                        {
                            headers: {
                                'Authorization': `Bearer ${user.discordCredentials.accessToken}`
                            }
                        }
                    );
                    commit('setDiscordUser', response.data)
                } catch (e) {
                    commit('setDiscordUser', null)
                    console.log(e)
                }
            }

            store.commit('setLoading', false)
        },
        async register(_, form) {
            store.commit('setLoading', true)
            return await axios.post('/register', form)
                .then((res) => {
                    store.commit('setLoading', false)
                    return res
                })
                .catch((e) => {
                    store.commit('setLoading', false)
                    console.log(e)
                    return e
                })
        },
        /**
         * Guess it ^^
         * @param commit
         */
        logout({commit}) {
            store.commit('setLoading', true)
            localStorage.removeItem('token')
            commit('setUser', null)
            commit('setDiscordUser', null)
            commit('setToken', null)
            store.commit('setLoading', false)
        }
    }
}
