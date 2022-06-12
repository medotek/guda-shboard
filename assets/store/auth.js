import store from './app'
import axios from 'axios'

const getDiscordUserInfo = async (accessToken) => {
    let response = null;
    try {
        response = await axios.get(
            'https://discord.com/api/v8/users/@me',
            {
                headers: {
                    'Authorization': `Bearer ${accessToken}`
                }
            }
        );

        return {
            status: response.status,
            data: response.data
        }
    } catch (e) {
        return {
            status: 'error'
        }
    }

}
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
                // TODO : Set discord user session
                let response = await axios.post('/discord/register', data).catch((e) => {
                    console.log(e);
                })
                return dispatch('attempt', 'discord')
            } else {
                return next({
                    name: 'login'
                })
            }
        },
        async attempt({commit, state}, token) {
            if (token && token !== 'discord') {
                commit('setToken', token)
            }

            if (!state.token) {
                return
            }

            let user = null;
            try {
                let response = await axios.get('/profile')
                user = JSON.parse(response.data)
                // Get discord info only if accessToken exists
                commit('setUser', user)
            } catch (e) {
                commit('setUser', null)
                commit('setToken', null)
            }

            let userDiscordData = null;
            if (user) {
                try {
                    if (user.discordCredentials.accessToken) {
                        let discordUserInfo = await getDiscordUserInfo(user.discordCredentials.accessToken)
                        if (discordUserInfo.status === 200) {
                            userDiscordData = discordUserInfo.data
                        }
                    }
                } catch (e) {
                    console.log(e)
                }
            }
            commit('setDiscordUser', userDiscordData)

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
        },
        async revoke({commit}, token) {
            console.log(token)
            return await axios.get('http://localhost:3333/api/auth/discord/revoke?access_token=' + token).then((res) => {
                commit('setDiscordUser', null)
                return res
            }).catch(e => {
                console.log(e)
            })
        }
    }
}
