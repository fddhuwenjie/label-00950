import { defineStore } from 'pinia'
import { ref } from 'vue'
import { reviewApi } from '../utils/api'

export const useReviewStore = defineStore('reviews', () => {
  const reviewsByProduct = ref({})
  const reviewStats = ref({})
  const myReviews = ref([])
  const loading = ref(false)

  async function fetchProductReviews(productId, params = {}) {
    loading.value = true
    try {
      const result = await reviewApi.getByProduct(productId, params)
      reviewsByProduct.value[productId] = result
      reviewStats.value[productId] = result.stats
      return result
    } catch (error) {
      console.error('获取评价失败:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function createReview(data) {
    loading.value = true
    try {
      const result = await reviewApi.create(data)
      if (reviewsByProduct.value[data.product_id]) {
        delete reviewsByProduct.value[data.product_id]
      }
      return result
    } catch (error) {
      console.error('提交评价失败:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchMyReviews() {
    loading.value = true
    try {
      myReviews.value = await reviewApi.getMyReviews()
      return myReviews.value
    } catch (error) {
      console.error('获取我的评价失败:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function uploadImage(file) {
    return reviewApi.uploadImage(file)
  }

  function isReviewed(orderId, orderItemId) {
    return myReviews.value.some(r => r.order_id === orderId)
  }

  return {
    reviewsByProduct,
    reviewStats,
    myReviews,
    loading,
    fetchProductReviews,
    createReview,
    fetchMyReviews,
    uploadImage,
    isReviewed,
  }
})
