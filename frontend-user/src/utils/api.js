/**
 * API 工具 - 对接 WordPress/WooCommerce REST API
 * 所有请求通过 nginx 代理到后端 WordPress
 */

const API_BASE = '/wp-json/cbc/v1'
const REVIEW_API_BASE = '/wp-json/pr/v1'

class ApiClient {
  constructor(baseURL = API_BASE) {
    this.baseURL = baseURL
  }

  getToken() {
    return localStorage.getItem('token') || ''
  }

  async request(method, path, data = null, options = {}) {
    const url = this.baseURL + path
    const headers = {
      'Content-Type': 'application/json',
    }

    const token = this.getToken()
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }

    const config = {
      method,
      headers,
      ...options,
    }

    if (data && (method === 'POST' || method === 'PUT')) {
      config.body = JSON.stringify(data)
    }

    const response = await fetch(url, config)

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: '请求失败' }))
      throw new Error(error.message || `HTTP ${response.status}`)
    }

    return response.json()
  }

  get(path, params = {}) {
    const query = new URLSearchParams(params).toString()
    const fullPath = query ? `${path}?${query}` : path
    return this.request('GET', fullPath)
  }

  post(path, data) {
    return this.request('POST', path, data)
  }

  put(path, data) {
    return this.request('PUT', path, data)
  }

  delete(path) {
    return this.request('DELETE', path)
  }
}

const api = new ApiClient()
const reviewApi = new ApiClient(REVIEW_API_BASE)

// ===== 认证 API =====
export const authApi = {
  login: (username, password) => api.post('/auth/login', { username, password }),
  register: (data) => api.post('/auth/register', data),
  me: () => api.get('/auth/me'),
}

// ===== 商品 API =====
export const productApi = {
  getAll: (params = {}) => api.get('/products', params),
  getById: (id) => api.get(`/products/${id}`),
  create: (data) => api.post('/products', data),
  update: (id, data) => api.put(`/products/${id}`, data),
  delete: (id) => api.delete(`/products/${id}`),
}

// ===== 分类 API =====
export const categoryApi = {
  getAll: () => api.get('/categories'),
}

// ===== 订单 API =====
export const orderApi = {
  getAll: (params = {}) => api.get('/orders', params),
  create: (data) => api.post('/orders', data),
  updateStatus: (id, status) => api.put(`/orders/${id}/status`, { status }),
  pay: (id) => api.post(`/orders/${id}/pay`),
}

// ===== 跨境电商功能 API =====
export const crossBorderApi = {
  convertCurrency: (amount, from, to) => api.get('/currency/convert', { amount, from, to }),
  calculateShipping: (data) => api.post('/shipping/calculate', data),
  calculateDuty: (data) => api.post('/duty/calculate', data),
}

// ===== 站点设置 API =====
export const settingsApi = {
  get: () => api.get('/settings'),
  update: (data) => api.post('/settings', data),
}

// ===== 仪表盘 API =====
export const dashboardApi = {
  get: () => api.get('/dashboard'),
}

// ===== 商品评价 API =====
export const productReviewApi = {
  // 获取某商品的评价列表（支持按评分筛选）
  list: (productId, { filter = 'all', page = 1, perPage = 10 } = {}) =>
    reviewApi.get('/reviews', {
      product_id: productId,
      filter,
      page,
      per_page: perPage,
    }),

  // 获取商品综合评分
  summary: (productId) => reviewApi.get(`/products/${productId}/summary`),

  // 批量获取多个商品综合评分（用于商品列表）
  summaryBatch: (ids = []) => {
    if (!ids || ids.length === 0) return Promise.resolve({})
    return reviewApi.get('/products/summary', { ids: ids.join(',') })
  },

  // 获取订单中可评价的商品
  orderReviewable: (orderId) => reviewApi.get(`/orders/${orderId}/reviewable`),

  // 创建评价
  create: (data) => reviewApi.post('/reviews', data),

  // 上传评价图片（FormData，调用 WordPress 媒体接口）
  uploadImage: async (file) => {
    const formData = new FormData()
    formData.append('file', file)
    const token = localStorage.getItem('token') || ''
    const headers = {}
    if (token) headers['Authorization'] = `Bearer ${token}`
    const res = await fetch('/wp-json/pr/v1/upload', {
      method: 'POST',
      headers,
      body: formData,
    })
    if (!res.ok) {
      const err = await res.json().catch(() => ({ message: '上传失败' }))
      throw new Error(err.message || `HTTP ${res.status}`)
    }
    return res.json()
  },
}

export default api
