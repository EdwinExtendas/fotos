// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import VueResource from 'vue-resource';
import BootstrapVue from 'bootstrap-vue';
import App from './App';
import router from './router';

import Config from '../api-config';
import CookieHelper from './helpers/CookieHelper';
import {Form} from 'bootstrap-vue/es/components';
import Filters from './helpers/Filters';
import VModal from 'vue-js-modal';
import Toasted from 'vue-toasted';

Vue.use(Toasted);
Vue.use(VueResource);
Vue.use(BootstrapVue);
Vue.use(Form);
Vue.use(VModal);

// this.$toasted.global.success('fancy');
Vue.use(Toasted);
Vue.toasted.register('success',
    (message) => { return message; },
    {
        type: 'success',
        duration: 5000,
        position: 'top-center'
    }
);

/**
 * intercept every http call
 * handle exception codes here.
 */
Vue.http.options.credentials = true;
Vue.http.interceptors.push(function (request, next) {
    //set base_url
    request.url = Config.base_api_url + request.url;

    // continue to next interceptor
    next(function (response) {
        if (400 === response.status && response.bodyText.indexOf('TOKEN_EXPIRED')) {
            CookieHelper.unsetCookie('user');
            this.$router.push({name: 'Login'});
        }

        if (497 === response.status) {
            this.$toasted.global.success('We hebben u opnieuw ingelogd, hierdoor was uw actie onsuccesvol. Probeer het opnieuw.');
        }
        if (498 === response.status) {
            CookieHelper.unsetCookie('user');
            this.$router.push({name: 'Login'});
        }
    });
});

//Global function, maybe put it in a file?
Vue.prototype.$_changePageByName = function (name) {
    this.$router.push({name: name});
};

/* eslint-disable no-new */
window.Vue = new Vue({
    el: '#app',
    router,
    filters: Filters,
    template: '<App/>',
    components: {
        App
    }
});
