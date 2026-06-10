<template>
  <div class="product-reviews">
    <div class="reviews-header">
      <h3 class="section-title">
        用户评价
        <span v-if="stats.total" class="count">({{ stats.total }})</span>
      </h3>
      
      <div v-if="stats.total > 0" class="rating-summary">
        <div class="rating-score">
          <span class="score">{{ stats.avg_rating.toFixed(1) }}</span>
          <StarRating :model-value="stats.avg_rating" readonly size="small" show-half />
        </div>
        <div class="rating-bars">
          <div class="bar-row" @click="filter = 'positive'">
            <span class="bar-label">好评</span>
            <div class="bar-track">
              <div class="bar-fill" :style="{ width: `${getPercent(stats.positive)}%` }"></div>
            </div>
            <span class="bar-count">{{ stats.positive }}</span>
          </div>
          <div class="bar-row" @click="filter = 'neutral'">
            <span class="bar-label">中评</span>
            <div class="bar-track">
              <div class="bar-fill neutral" :style="{ width: `${getPercent(stats.neutral)}%` }"></div>
            </div>
            <span class="bar-count">{{ stats.neutral }}</span>
          </div>
          <div class="bar-row" @click="filter = 'negative'">
            <span class="bar-label">差评</span>
            <div class="bar-track">
              <div class="bar-fill negative" :style="{ width: `${getPercent(stats.negative)}%` }"></div>
            </div>
            <span class="bar-count">{{ stats.negative }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="filter-tabs" v-if="stats.total > 0">
      <button 
        v-for="tab in filterTabs" 
        :key="tab.value"
        :class="{ active: filter === tab.value }"
        @click="handleFilterChange(tab.value)"
      >
        {{ tab.label }}
        <span v-if="tab.count !== undefined" class="count">{{ tab.count }}</span>
      </button>
    </div>

    <div v-if="loading" class="loading-state">
      <p>加载评价中...</p>
    </div>

    <div v-else-if="reviews.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
      </div>
      <p>暂无评价，快来成为第一个评价的人吧！</p>
    </div>

    <div v-else class="reviews-list">
      <div v-for="review in reviews" :key="review.id" class="review-item">
        <div class="review-header">
          <div class="user-info">
            <div class="avatar">
              {{ review.user_name.charAt(0).toUpperCase() }}
            </div>
            <div class="user-meta">
              <span class="user-name">{{ review.user_name }}</span>
              <div class="review-rating">
                <StarRating :model-value="review.rating" readonly size="small" />
              </div>
            </div>
          </div>
          <span class="review-date">{{ formatDate(review.created_at) }}</span>
        </div>
        
        <p class="review-content">{{ review.content }}</p>
        
        <div v-if="review.images && review.images.length > 0" class="review-images">
          <div 
            v-for="(img, idx) in review.images" 
            :key="idx"
            class="review-image"
            @click="previewImage(review.images, idx)"
          >
            <img :src="img" :alt="`评价图片 ${idx + 1}`" />
          </div>
        </div>
      </div>

      <div v-if="hasMore" class="load-more">
        <button @click="loadMore" :disabled="loadingMore">
          {{ loadingMore ? '加载中...' : '加载更多评价' }}
        </button>
      </div>
    </div>

    <div v-if="previewVisible" class="image-preview-overlay" @click="closePreview">
      <button class="preview-close" @click="closePreview">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
      <button v-if="previewIndex > 0" class="preview-nav prev" @click.stop="prevImage">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>
      <img :src="previewImages[previewIndex]" class="preview-image" @click.stop />
      <button v-if="previewIndex < previewImages.length - 1" class="preview-nav next" @click.stop="nextImage">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import StarRating from './StarRating.vue'
import { useReviewStore } from '@/stores/reviews'

const props = defineProps({
  productId: {
    type: Number,
    required: true
  },
  initialStats: {
    type: Object,
    default: () => ({ avg_rating: 0, total: 0, positive: 0, neutral: 0, negative: 0 })
  }
})

const reviewStore = useReviewStore()

const reviews = ref([])
const stats = ref(props.initialStats)
const filter = ref('all')
const page = ref(1)
const hasMore = ref(false)
const loading = ref(false)
const loadingMore = ref(false)
const perPage = 10

const previewVisible = ref(false)
const previewImages = ref([])
const previewIndex = ref(0)

const filterTabs = computed(() => [
  { label: '全部', value: 'all', count: stats.value.total },
  { label: '好评', value: 'positive', count: stats.value.positive },
  { label: '中评', value: 'neutral', count: stats.value.neutral },
  { label: '差评', value: 'negative', count: stats.value.negative },
])

const getPercent = (count) => {
  if (!stats.value.total) return 0
  return Math.round((count / stats.value.total) * 100)
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) {
    const hours = Math.floor(diff / (1000 * 60 * 60))
    if (hours === 0) {
      const minutes = Math.floor(diff / (1000 * 60))
      return minutes <= 0 ? '刚刚' : `${minutes}分钟前`
    }
    return `${hours}小时前`
  }
  if (days === 1) return '昨天'
  if (days < 7) return `${days}天前`
  return dateStr.slice(0, 10)
}

const fetchReviews = async (reset = false) => {
  if (reset) {
    page.value = 1
    reviews.value = []
    loading.value = true
  } else {
    loadingMore.value = true
  }

  try {
    const params = { page: page.value, per_page: perPage }
    if (filter.value !== 'all') {
      params.rating = filter.value
    }
    
    const result = await reviewStore.fetchProductReviews(props.productId, params)
    stats.value = result.stats
    
    if (reset) {
      reviews.value = result.data
    } else {
      reviews.value = [...reviews.value, ...result.data]
    }
    
    hasMore.value = result.data.length >= perPage && reviews.value.length < result.total
  } catch (e) {
    console.error('加载评价失败:', e)
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const handleFilterChange = (value) => {
  filter.value = value
  fetchReviews(true)
}

const loadMore = () => {
  page.value++
  fetchReviews(false)
}

const previewImage = (images, index) => {
  previewImages.value = images
  previewIndex.value = index
  previewVisible.value = true
  document.body.style.overflow = 'hidden'
}

const closePreview = () => {
  previewVisible.value = false
  document.body.style.overflow = ''
}

const prevImage = () => {
  if (previewIndex.value > 0) previewIndex.value--
}

const nextImage = () => {
  if (previewIndex.value < previewImages.value.length - 1) previewIndex.value++
}

watch(() => props.productId, () => {
  stats.value = props.initialStats
  fetchReviews(true)
})

onMounted(() => {
  fetchReviews(true)
})
</script>

<style lang="scss" scoped>
.product-reviews {
  margin-top: 40px;
  background: #fff;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.reviews-header {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
}

.section-title {
  font-size: 22px;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 20px 0;

  .count {
    font-size: 16px;
    font-weight: 400;
    color: #999;
  }
}

.rating-summary {
  display: flex;
  gap: 40px;
  align-items: center;

  .rating-score {
    text-align: center;
    flex-shrink: 0;

    .score {
      display: block;
      font-size: 48px;
      font-weight: 800;
      color: #1a1a1a;
      line-height: 1;
      margin-bottom: 8px;
    }
  }

  .rating-bars {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
}

.bar-row {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 4px 0;
  transition: opacity 0.2s;

  &:hover {
    opacity: 0.8;
  }

  .bar-label {
    width: 40px;
    font-size: 13px;
    color: #666;
  }

  .bar-track {
    flex: 1;
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
  }

  .bar-fill {
    height: 100%;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 4px;
    transition: width 0.5s ease;

    &.neutral {
      background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    &.negative {
      background: linear-gradient(135deg, #ef4444, #dc2626);
    }
  }

  .bar-count {
    width: 30px;
    text-align: right;
    font-size: 13px;
    color: #999;
  }
}

.filter-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  flex-wrap: wrap;

  button {
    padding: 10px 20px;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 20px;
    font-size: 14px;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;

    &:hover {
      border-color: #6366f1;
      color: #6366f1;
    }

    &.active {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border-color: transparent;
      color: #fff;

      .count {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
      }
    }

    .count {
      padding: 2px 8px;
      background: #f5f5f5;
      border-radius: 10px;
      font-size: 12px;
    }
  }
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.empty-state {
  .empty-icon {
    margin-bottom: 16px;
    color: #ddd;
  }
}

.reviews-list {
  display: flex;
  flex-direction: column;
}

.review-item {
  padding: 24px 0;
  border-bottom: 1px solid #f5f5f5;

  &:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;

  .avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
  }

  .user-meta {
    .user-name {
      display: block;
      font-size: 15px;
      font-weight: 600;
      color: #1a1a1a;
      margin-bottom: 4px;
    }
  }
}

.review-date {
  font-size: 13px;
  color: #999;
  white-space: nowrap;
}

.review-content {
  font-size: 15px;
  line-height: 1.8;
  color: #333;
  margin: 0 0 16px 0;
}

.review-images {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.review-image {
  width: 100px;
  height: 100px;
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s;

  &:hover {
    transform: scale(1.05);
  }

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.load-more {
  text-align: center;
  padding-top: 24px;

  button {
    padding: 12px 32px;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 20px;
    font-size: 14px;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;

    &:hover:not(:disabled) {
      border-color: #6366f1;
      color: #6366f1;
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
}

.image-preview-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
}

.preview-close {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 50%;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;

  &:hover {
    background: rgba(255, 255, 255, 0.2);
  }
}

.preview-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 56px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 50%;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;

  &:hover {
    background: rgba(255, 255, 255, 0.2);
  }

  &.prev {
    left: 20px;
  }

  &.next {
    right: 20px;
  }
}

.preview-image {
  max-width: 90vw;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
}

@media (max-width: 768px) {
  .product-reviews {
    padding: 20px;
    margin-top: 20px;
    border-radius: 16px;
  }

  .section-title {
    font-size: 18px;
  }

  .rating-summary {
    flex-direction: column;
    gap: 20px;
    align-items: stretch;

    .rating-score .score {
      font-size: 36px;
    }
  }

  .review-header {
    flex-direction: column;
    gap: 8px;
  }

  .review-image {
    width: 80px;
    height: 80px;
  }

  .filter-tabs button {
    padding: 8px 16px;
    font-size: 13px;
  }
}
</style>
