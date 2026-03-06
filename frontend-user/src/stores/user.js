import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '../utils/api'

export const useUserStore = defineStore('user', () => {
  const token = ref(localStorage.getItem('token') || '')
  const userInfo = ref(JSON.parse(localStorage.getItem('userInfo') || '{}'))

  const isLoggedIn = computed(() => !!token.value)

  // 登录 - 调用 WordPress REST API
  async function login(username, password) {
    try {
      const res = await authApi.login(username, password)
      token.value = res.token
      userInfo.value = res.user
      localStorage.setItem('token', res.token)
      localStorage.setItem('userInfo', JSON.stringify(res.user))
      return { success: true }
    } catch (error) {
      return { success: false, message: error.message || '登录失败' }
    }
  }

  // 注册 - 调用 WordPress REST API
  async function register(userData) {
    try {
      const res = await authApi.register(userData)
      token.value = res.token
      userInfo.value = res.user
      localStorage.setItem('token', res.token)
      localStorage.setItem('userInfo', JSON.stringify(res.user))
      return { success: true }
    } catch (error) {
      return { success: false, message: error.message || '注册失败' }
    }
  }

  function logout() {
    token.value = ''
    userInfo.value = {}
    localStorage.removeItem('token')
    localStorage.removeItem('userInfo')
  }

  return {
    token,
    userInfo,
    isLoggedIn,
    login,
    register,
    logout
  }
})
