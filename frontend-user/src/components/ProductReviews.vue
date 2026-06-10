<template>
  <div class="product-reviews">
    <div class="reviews-summary">
      <div class="summary-score">
        <span class="score-number">{{ stats.average_rating.toFixed(1) }}</span>
        <StarRating
          :model-value="stats.average_rating"
          :size="'lg'"
        />
        <span class="score-count">{{ stats.total_count }} 条评价</span>
      </div>
      <div class="summary-distribution">
        <div
          v-for="star in 5"
          :key="star"
          class="dist-row"
        >
          <span class="dist-label">{{ 6 - star }}星</span>
          <div class="dist-bar">
            <div
              class="dist-fill"
              :style="{ width: getDistPercent(6 - star) + '%' }"
            />
          </div>
          <span class="dist-count">{{ getDistCount(6 - star) }}</span>
        </div>
      </div>
    </div>

    <div class="reviews-filter">
      <button
        v-for="filter in filters"
        :key="filter.id"
        :class="{ active: activeFilter === filter.id }"
        @click="activeFilter = filter.id"
      >
        {{ filter.label }}
      </button>
    </div>

    <div
      v-if="loading"
      class="reviews-loading"
    >
      <span>加载中...</span>
    </div>

    <div
      v-else-if="reviews.length > 0"
      class="reviews-list"
    >
      <div
        v-for="review in reviews"
        :key="review.id"
        class="review-item"
      >
        <div class="review-header">
          <span class="review-user">{{ maskName(review.user_name) }}</span>
          <StarRating
            :model-value="review.rating"
            :size="'sm'"
          />
          <span class="review-date">{{ formatDate(review.created_at) }}</span>
        </div>
        <p class="review-content">
          {{ review.content }}
        </p>
        <div
          v-if="review.images && review.images.length"
          class="review-images"
        >
          <img
            v-for="(img, idx) in review.images"
            :key="idx"
            :src="img"
            @click="previewImage(img)"
          >
        </div>
      </div>
    </div>

    <div
      v-if="!loading && reviews.length === 0"
      class="reviews-empty"
    >
      <svg
        width="64"
        height="64"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1"
      >
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
      </svg>
      <p>暂无评价，快来发表第一条评价吧</p>
    </div>

    <div
      v-if="totalPages > 1"
      class="reviews-pagination"
    >
      <button
        class="page-btn"
        :disabled="currentPage <= 1"
        @click="currentPage--"
      >
        上一页
      </button>
      <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
      <button
        class="page-btn"
        :disabled="currentPage >= totalPages"
        @click="currentPage++"
      >
        下一页
      </button>
    </div>

    <div
      v-if="previewUrl"
      class="image-lightbox"
      @click="previewUrl = ''"
    >
      <img
        :src="previewUrl"
        @click.stop
      >
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import StarRating from '@/components/StarRating.vue'
import { useReviewStore } from '@/stores/review'

const props = defineProps({
  productId: {
    type: Number,
    required: true
  }
})

const reviewStore = useReviewStore()

const activeFilter = ref('all')
const currentPage = ref(1)
const totalPages = ref(1)
const previewUrl = ref('')

const filters = [
  { id: 'all', label: '全部' },
  { id: 'good', label: '好评' },
  { id: 'medium', label: '中评' },
  { id: 'bad', label: '差评' }
]

const reviews = computed(() => reviewStore.reviews)
const loading = computed(() => reviewStore.loading)
const stats = computed(() => reviewStore.getStatsForProduct(props.productId))

const getDistCount = (star) => {
  return stats.value.distribution?.[star] || 0
}

const getDistPercent = (star) => {
  const count = getDistCount(star)
  const total = stats.value.total_count
  if (!total) return 0
  return Math.round((count / total) * 100)
}

const maskName = (name) => {
  if (!name) return '匿名用户'
  if (name.length <= 1) return name + '**'
  if (name.length === 2) return name[0] + '**'
  return name[0] + '**' + name[name.length - 1]
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return '刚刚'
  if (minutes < 60) return `${minutes}分钟前`
  if (hours < 24) return `${hours}小时前`
  if (days < 30) return `${days}天前`
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const previewImage = (url) => {
  previewUrl.value = url
}

const loadData = async () => {
  const params = {
    page: currentPage.value,
    per_page: 10
  }
  if (activeFilter.value !== 'all') {
    params.rating = activeFilter.value
  }
  const res = await reviewStore.fetchReviews(props.productId, params)
  if (res) {
    totalPages.value = res.total_pages || Math.ceil((res.total || 0) / 10) || 1
  }
}

watch(activeFilter, () => {
  currentPage.value = 1
  loadData()
})

watch(currentPage, () => {
  loadData()
})

onMounted(() => {
  reviewStore.fetchReviewStats(props.productId)
  loadData()
})
</script>

<style lang="scss" scoped>
.product-reviews {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 32px;
}

.reviews-summary {
  display: flex;
  gap: 40px;
  padding-bottom: 28px;
  border-bottom: 1px solid #f0f0f0;
  margin-bottom: 24px;
}

.summary-score {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  min-width: 120px;

  .score-number {
    font-size: 48px;
    font-weight: 800;
    color: #1a1a1a;
    line-height: 1;
  }

  .score-count {
    font-size: 14px;
    color: #999;
  }
}

.summary-distribution {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  justify-content: center;
}

.dist-row {
  display: flex;
  align-items: center;
  gap: 12px;

  .dist-label {
    font-size: 13px;
    color: #666;
    width: 32px;
    text-align: right;
    flex-shrink: 0;
  }

  .dist-bar {
    flex: 1;
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;

    .dist-fill {
      height: 100%;
      background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
      border-radius: 4px;
      transition: width 0.4s ease;
    }
  }

  .dist-count {
    font-size: 13px;
    color: #999;
    width: 28px;
    text-align: right;
    flex-shrink: 0;
  }
}

.reviews-filter {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  background: #f8f8f8;
  padding: 6px;
  border-radius: 14px;

  button {
    flex: 1;
    padding: 10px 16px;
    background: none;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      color: #1a1a1a;
    }

    &.active {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      color: #fff;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }
  }
}

.reviews-loading {
  text-align: center;
  padding: 60px 0;
  color: #999;
  font-size: 15px;
}

.reviews-list {
  display: flex;
  flex-direction: column;
}

.review-item {
  padding: 24px 0;
  border-bottom: 1px solid #f5f5f5;

  &:first-child {
    padding-top: 0;
  }

  &:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
}

.review-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;

  .review-user {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
  }

  .review-date {
    font-size: 13px;
    color: #999;
    margin-left: auto;
  }
}

.review-content {
  font-size: 15px;
  line-height: 1.7;
  color: #333;
  margin-bottom: 12px;
}

.review-images {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;

  img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #f0f0f0;

    &:hover {
      transform: scale(1.08);
      border-color: #6366f1;
    }
  }
}

.reviews-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 60px 0;

  svg {
    color: #ddd;
  }

  p {
    font-size: 15px;
    color: #999;
  }
}

.reviews-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding-top: 24px;
  border-top: 1px solid #f0f0f0;
  margin-top: 8px;

  .page-btn {
    padding: 10px 24px;
    background: #f5f5f5;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      color: #fff;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }

    &:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
  }

  .page-info {
    font-size: 14px;
    color: #666;
    font-weight: 500;
  }
}

.image-lightbox {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  cursor: pointer;
  padding: 40px;

  img {
    max-width: 90vw;
    max-height: 85vh;
    border-radius: 12px;
    object-fit: contain;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
  }
}

@media (max-width: 768px) {
  .product-reviews {
    padding: 20px 16px;
    border-radius: 16px;
  }

  .reviews-summary {
    flex-direction: column;
    gap: 20px;
    align-items: stretch;
  }

  .summary-score {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    min-width: auto;
    gap: 12px;

    .score-number {
      font-size: 36px;
    }
  }

  .reviews-filter {
    flex-wrap: wrap;
    gap: 6px;

    button {
      flex: 0 0 calc(50% - 3px);
      padding: 8px 12px;
      font-size: 13px;
    }
  }

  .review-header {
    flex-wrap: wrap;

    .review-date {
      margin-left: 0;
      width: 100%;
    }
  }

  .review-content {
    font-size: 14px;
  }

  .image-lightbox {
    padding: 20px;

    img {
      max-width: 95vw;
      max-height: 80vh;
      border-radius: 8px;
    }
  }
}
</style>
