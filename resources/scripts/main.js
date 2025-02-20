import '../sass/crater.scss'
import 'v-tooltip/dist/v-tooltip.css'
import '@/scripts/plugins/axios.js'
import * as VueRouter from 'vue-router'
import router from '@/scripts/router/index'
import * as pinia from 'pinia'
import * as Vue from 'vue'
import * as Vuelidate from '@vuelidate/core'

window.pinia = pinia
window.Vuelidate = Vuelidate

import Crater from './Crater'

window.Vue = Vue
window.router = router
window.VueRouter = VueRouter

import axios from 'axios'
// Retrieve token from localStorage
const token = localStorage.getItem('authToken')

if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// // Retrieve customer from localStorage
// const company_id = localStorage.getItem('company_id')

// if (company_id) {
//   axios.defaults.headers.common['company'] = `${company_id}`
// }
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['X-CSRF-TOKEN'] = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute('content')

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      // Token expired or invalid, redirect to login
      localStorage.removeItem('authToken')
      delete axios.defaults.headers.common['Authorization']

      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

window.Crater = new Crater()
