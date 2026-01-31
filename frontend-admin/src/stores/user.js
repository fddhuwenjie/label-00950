import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useUserStore = defineStore('user', () => {
  const token = ref(localStorage.getItem('token') || '')
  const userInfo = ref(JSON.parse(localStorage.getItem('userInfo') || '{}'))

  const isLoggedIn = computed(() => !!token.value)

  // 模拟登录 - 验证测试账号
  async function login(username, password) {
    // 模拟网络延迟
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // 验证测试账号
    if (username === 'admin' && password === 'admin123') {
      const mockToken = 'mock_admin_token_' + Date.now()
      const mockUserInfo = {
        id: 1,
        name: 'Admin',
        email: 'admin@example.com',
        role: 'administrator',
        avatar: ''
      }
      
      token.value = mockToken
      userInfo.value = mockUserInfo
      
      localStorage.setItem('token', mockToken)
      localStorage.setItem('userInfo', JSON.stringify(mockUserInfo))
      
      return { success: true }
    }
    
    return { 
      success: false, 
      message: '用户名或密码错误' 
    }
  }

  function logout() {
    token.value = ''
    userInfo.value = {}
    localStorage.removeItem('token')
    localStorage.removeItem('userInfo')
  }

  // 检查登录状态
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
