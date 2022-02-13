import Vue from 'vue';
import VueRouter from 'vue-router';
import Home from '../views/Home';
import Register from "../views/Register";
import Login from "../views/Login";
import Dashboard from "../views/Dashboard";
import store from "../store/app";
import Account from "../views/Account";
import NotFound from "../components/NotFound";
import DiscordOauth2 from "../components/DiscordOauth2";
import Discord from "../components/Discord";
import WebhookFeatures from "../views/WebhookFeatures";
import WebhookForm from "../components/webhook/WebhookForm";

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        beforeEnter: (to, from, next) => {
            if (store.getters['auth/isAuthenticated']) {
                return next({
                    name: 'home'
                })
            }
            next()
        }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        beforeEnter: (to, from, next) => {
            if (store.getters['auth/isAuthenticated']) {
                return next({
                    name: 'home'
                })
            }
            next()
        }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        beforeEnter: (to, from, next) => {
            if (!store.getters['auth/isAuthenticated']) {
                return next({
                    name: 'login'
                })
            }
            next()
        }
    },
    {
        path: '/account',
        name: 'account',
        component: Account,
        beforeEnter: (to, from, next) => {
            if (!store.getters['auth/isAuthenticated']) {
                return next({
                    name: 'login'
                })
            }
            next()
        }
    },
    {
        path: '/not-found',
        name: 'notFound',
        component: NotFound
    },
    {
        path: '/discord',
        name: 'discord',
        // component: Discord,
        component: {
            // render router-view into parent
            render(c) {
                return c('router-view');
            }
        },
        beforeEnter: (to, from, next) => {
            if (!store.getters['auth/isAuthenticated']) {
                return next({
                    name: 'login'
                })
            }
            next()
        },
        children: [
            {
                path: 'auth',
                name: 'discord.auth',
                component: DiscordOauth2,
            }
        ]
    },
    {
        path: '/webhooks',
        name: 'webhooks',
        component: WebhookFeatures,
        beforeEnter: (to, from, next) => {
            if (!store.getters['auth/isAuthenticated']) {
                return next({
                    name: 'login'
                })
            }
            next()
        },
        children: [
            {
                path: 'new',
                name: 'webhooks.new',
                component: WebhookForm,
            }
        ]
    }
]

const router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes
})

router.beforeEach((to, from, next) => {
    if (!to.matched.length) {
        next({name: 'notFound'});
    } else {
        next();
    }
})

export default router
