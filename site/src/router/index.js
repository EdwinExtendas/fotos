import Vue from 'vue'
import Router from 'vue-router'

// Helpers
import CookieHelper from '@/helpers/CookieHelper'

// Containers
import Full from '@/containers/Full'

// Views
import Dashboard from '@/views/Dashboard'
import Upload from  '@/views/Upload'

import Category_All from '@/views/category/All'
import Category_View from '@/views/category/View'

import Login from '@/views/Login'

Vue.use(Router)

/**
 * The - characters are being replaced with a space for the navbar
 * The string after the LAST _ character is begin used for the name in de breadcrumb path
 * @type {VueRouter}
 */
let router = new Router({
    mode: 'hash',
    linkActiveClass: 'open active',
    scrollBehavior: () => ({ y: 0 }),
    routes: [
        {
            path: '/',
            redirect: '/dashboard',
            name: 'Home',
            meta: {label: 'Home'},
            component: Full,
            children: [
                {
                    path: 'dashboard',
                    name: 'Dashboard',
                    meta: {label: 'Dashboard'},
                    component: Dashboard,
                },
                {
                    path: 'upload',
                    name: 'Upload',
                    meta: {label: 'Upload'},
                    component: Upload,
                },
                {
                    path: 'albums',
                    name: 'Albums',
                    meta: {label: 'Albums'},
                    redirect: '/albums/all',
                    component: {
                        render (c) { return c('router-view') }
                    },
                    children: [
                        {
                            path: 'all',
                            name: 'AlbumsAll',
                            meta: {label: 'Overzicht'},
                            component: Category_All,
                        },
                        {
                            path: ':id/view',
                            name: 'AlbumsView',
                            meta: {label: 'Bekijk'},
                            component: Category_View,
                        }
                    ],
                },
            ]
        },
        {
            path: '/login',
            name: 'Login',
            component: Login,
            meta: {
                noAuthRequired: true
            }
        }
    ]
});

router.beforeEach((to, from, next) => {
    let currentUser = CookieHelper.checkLogin();
    let noAuthRequired = to.matched.some(record => record.meta.noAuthRequired);

    //if requiresAuth and user isn't set -> login
    if (!noAuthRequired && !currentUser) next('login');
    else next()
})

export default router
