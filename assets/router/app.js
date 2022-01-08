import Vue from 'vue';
import VueRouter from 'vue-router';
import Home from '../views/Home';
import Register from "../views/Register";
import Login from "../views/Login";
import Dashboard from "../views/Dashboard";
import store from "../store/app";
import Account from "../views/Account";

Vue.use(VueRouter)

const routes = [
    {
        path: '/home',
        name: 'home',
        component: Home
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        beforeEnter: (to, from, next) => {
            if(store.getters['auth/isAuthenticated']){
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
            if(store.getters['auth/isAuthenticated']){
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
            if(!store.getters['auth/isAuthenticated']){
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
            if(!store.getters['auth/isAuthenticated']){
                return next({
                    name: 'login'
                })
            }
            next()
        }
    }
]

const router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes
})

export default router
