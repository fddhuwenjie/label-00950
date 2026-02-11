import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json'
  },
  withCredentials: true // 允许发送 cookies
})

// 获取 CSRF token
function getCsrfToken() {
  const cookies = document.cookie.split(';')
  for (const cookie of cookies) {
    const [name, value] = cookie.trim().split('=')
    if (name === 'csrf_token') {
      return value
    }
  }
  return null
}

// 请求拦截器
api.interceptors.request.use(
  config => {
    // 添加 Authorization token
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // 对于修改数据的请求，添加 CSRF token
    if (['post', 'put', 'delete', 'patch'].includes(config.method?.toLowerCase())) {
      const csrfToken = getCsrfToken()
      if (csrfToken) {
        config.headers['X-CSRF-Token'] = csrfToken
      }
    }
    
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')
      window.location.href = '/login'
    }
    
    // 处理 CSRF token 过期
    if (error.response?.status === 403 && error.response?.data?.error === 'CSRF token invalid') {
      // 刷新页面以获取新的 CSRF token
      window.location.reload()
    }
    
    // 处理速率限制
    if (error.response?.status === 429) {
      console.warn('请求过于频繁，请稍后再试')
    }
    
    return Promise.reject(error)
  }
)

export default api
