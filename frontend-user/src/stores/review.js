import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

export const useReviewStore = defineStore('review', () => {
  const reviews = ref([])
  const reviewStats = ref({})
  const loading = ref(false)
  const submitting = ref(false)

  const getStatsForProduct = computed(() => {
    return (productId) => {
      return reviewStats.value[productId] || { average_rating: 0, total_count: 0, distribution: {} }
    }
  })

  async function fetchReviews(productId, params = {}) {
    loading.value = true
    try {
      const res = await api.get('/reviews', { product_id: productId, ...params })
      reviews.value = res.reviews || res.data || []
      return res
    } finally {
      loading.value = false
    }
  }

  async function fetchReviewStats(productIds) {
    const ids = Array.isArray(productIds) ? productIds.join(',') : productIds
    const res = await api.get('/reviews/stats', { product_id: ids })
    const stats = res.stats || res.data || {}
    Object.keys(stats).forEach((id) => {
      reviewStats.value[id] = stats[id]
    })
    return stats
  }

  async function submitReview(data) {
    submitting.value = true
    try {
      const res = await api.post('/reviews', data)
      return res
    } finally {
      submitting.value = false
    }
  }

  async function uploadImage(file) {
    const formData = new FormData()
    formData.append('file', file)

    const token = localStorage.getItem('token') || ''
    const headers = {}
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }

    const response = await fetch(`${api.baseURL}/reviews/upload`, {
      method: 'POST',
      headers,
      body: formData,
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: '上传失败' }))
      throw new Error(error.message || `HTTP ${response.status}`)
    }

    return response.json()
  }

  async function checkCanReview(orderId, productId) {
    const res = await api.get('/reviews/check', { order_id: orderId, product_id: productId })
    return res
  }

  return {
    reviews,
    reviewStats,
    loading,
    submitting,
    getStatsForProduct,
    fetchReviews,
    fetchReviewStats,
    submitReview,
    uploadImage,
    checkCanReview
  }
})
