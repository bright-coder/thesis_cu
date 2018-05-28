
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

//window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Vue from 'vue'
import ProjectForm from './components/ProjectForm.vue'
import ProjectMain from './components/ProjectMain.vue'
import ProjectShow from './components/ProjectShow.vue'
import ChangeRequestForm from './components/ChangeRequestForm.vue'
import ImpactResult from './components/ImpactResult.vue'
import RecentChangeRequest from './components/RecentChangeRequest.vue'
// Vue.use('ProjectForm', require('./components/ProjectForm.vue'))


console.log(Vue.version)

const app = new Vue({
    el: '#app',
    components: {
        ProjectForm, ProjectMain, ProjectShow, ChangeRequestForm, ImpactResult, RecentChangeRequest
    },
 
})
