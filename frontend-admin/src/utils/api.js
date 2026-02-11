import axios from 'axios'
import { ElMessage } from 'element-plus'

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
    const message = error.response?.data?.message || error.response?.data?.error || '请求失败'
    
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')
      window.location.href = '/login'
    } else if (error.response?.status === 403 && error.response?.data?.error === 'CSRF token invalid') {
      ElMessage.warning('安全令牌已过期，页面将刷新')
      setTimeout(() => window.location.reload(), 1500)
    } else if (error.response?.status === 429) {
      ElMessage.warning('请求过于频繁，请稍后再试')
    } else {
      ElMessage.error(message)
    }
    
    return Promise.reject(error)
  }
)

export default api
