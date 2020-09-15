/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

window.Vue = require('vue')

Vue.use(require('bootstrap-vue'))

import store from './store'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Vue from 'vue'

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.config.productionTip = false

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('language-expert-view', require('./components/LanguageExpertView.vue').default)
Vue.component('master-expert-view', require('./components/MasterExpertView.vue').default)
Vue.component('users-table', require('./components/User/Table.vue').default)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    store,
    mounted() {
        if (document.querySelector('meta[name="current-user"]')) {
            this.$store.dispatch('app/setUser', JSON.parse(document.querySelector('meta[name="current-user"]').content))
        }
        if (document.querySelector('meta[name="app-languages"]')) {
            this.$store.dispatch('app/setLanguages', JSON.parse(document.querySelector('meta[name="app-languages"]').content))
        }
    }
})
