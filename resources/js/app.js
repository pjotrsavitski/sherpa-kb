/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

window.Vue = require('vue')

Vue.use(require('bootstrap-vue'))

import store from './store'

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('pending-question-edit', require('./components/PendingQuestionEdit.vue').default)
Vue.component('pending-question-review', require('./components/PendingQuestionReview.vue').default)
Vue.component('pending-questions-table', require('./components/PendingQuestionsTable.vue').default)
Vue.component('questions-table', require('./components/QuestionsTable.vue').default)
Vue.component('language-expert-view', require('./components/LanguageExpertView.vue').default)
Vue.component('master-expert-view', require('./components/MasterExpertView.vue').default)
Vue.component('question-edit', require('./components/QuestionEdit.vue').default)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    store
})
