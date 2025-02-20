import axios from 'axios'
import { defineStore } from 'pinia'
import { useNotificationStore } from '@/scripts/stores/notification'
import { handleError } from '@/scripts/helpers/error-handling'

export const useAuthStore = (useWindow = false) => {
  const defineStoreFunc = useWindow ? window.pinia.defineStore : defineStore
  const { global } = window.i18n

  return defineStoreFunc({
    id: 'auth',
    state: () => ({
      status: '',
      loginData: {
        email: '',
        password: '',
        remember: '',
      },
    }),

    actions: {
      login(data) {
        return new Promise((resolve, reject) => {
          axios
            .post('/api/v1/auth/login', data)
            .then((response) => {
              resolve(response)
              localStorage.setItem('authToken', response.data.token)
              setTimeout(() => {
                this.loginData.email = ''
                this.loginData.password = ''
              }, 1000)
            })
            .catch((err) => {
              handleError(err)
              reject(err)
            })
        })
      },

      logout() {
        return new Promise((resolve, reject) => {
          axios
            .post('/auth/logout')
            .then((response) => {
              const notificationStore = useNotificationStore()
              notificationStore.showNotification({
                type: 'success',
                message: 'Logged out successfully.',
              })
              localStorage.removeItem('authToken')
              delete axios.defaults.headers.common['Authorization']

              window.router.push('/login')
                // resetStore.clearPinia()
              resolve(response)
            })
            .catch((err) => {
              handleError(err)
              window.router.push('/')
              reject(err)
            })
        })
      },
    },
  })()
}
