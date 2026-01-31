import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

// 模拟用户数据库（实际应用中应该存储在后端）
const registeredUsers = ref(
  JSON.parse(localStorage.getItem('registeredUsers') || '[]')
)

// 初始化默认测试账号
if (!registeredUsers.value.find(u => u.username === 'admin')) {
  registeredUsers.value.push({
    id: 1,
    username: 'admin',
    email: 'admin@example.com',
    password: 'admin123',
    name: 'Admin'
  })
  localStorage.setItem('registeredUsers', JSON.stringify(registeredUsers.value))
}

export const useUserStore = defineStore('user', () => {
  const token = ref(localStorage.getItem('token') || '')
  const userInfo = ref(JSON.parse(localStorage.getItem('userInfo') || '{}'))

  const isLoggedIn = computed(() => !!token.value)

  // 登录 - 验证已注册的用户
  async function login(username, password) {
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // 从已注册用户中查找
    const users = JSON.parse(localStorage.getItem('registeredUsers') || '[]')
    const user = users.find(u => 
      (u.username === username || u.email === username) && u.password === password
    )
    
    if (user) {
      const mockToken = 'user_token_' + Date.now()
      const mockUserInfo = {
        id: user.id,
        name: user.name || user.username,
        email: user.email,
        username: user.username
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

  // 注册 - 将用户保存到本地
  async function register(username, email, password) {
    await new Promise(resolve => setTimeout(resolve, 500))
    
    const users = JSON.parse(localStorage.getItem('registeredUsers') || '[]')
    
    // 检查用户名是否已存在
    if (users.find(u => u.username === username)) {
      return { 
        success: false, 
        message: '用户名已存在' 
      }
    }
    
    // 检查邮箱是否已存在
    if (users.find(u => u.email === email)) {
      return { 
        success: false, 
        message: '邮箱已被注册' 
      }
    }
    
    // 创建新用户
    const newUser = {
      id: users.length + 1,
      username,
      email,
      password, // 实际应用中需要加密
      name: username,
      createdAt: new Date().toISOString()
    }
    
    users.push(newUser)
    localStorage.setItem('registeredUsers', JSON.stringify(users))
    
    return { success: true }
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
