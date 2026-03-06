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
      // 检查是否为管理员
      if (!res.user.role.includes('administrator')) {
        return { success: false, message: '仅管理员可登录后台' }
      }
      token.value = res.token
      userInfo.value = res.user
      localStorage.setItem('token', res.token)
      localStorage.setItem('userInfo', JSON.stringify(res.user))
      return { success: true }
    } catch (error) {
      return { success: false, message: error.message || '登录失败' }
    }
  }

  function logout() {
    token.value = ''
    userInfo.value = {}
    localStorage.removeItem('token')
    localStorage.removeItem('userInfo')
  }

  function checkAuth() {
    const storedToken = localStorage.getItem('token')
    const storedUserInfo = localStorage.getItem('userInfo')
    if (storedToken && storedUserInfo) {
      token.value = storedToken
      userInfo.value = JSON.parse(storedUserInfo)
      return true
    }
    return false
  }

  return {
    token,
    userInfo,
    isLoggedIn,
    login,
    logout,
    checkAuth
  }
})
