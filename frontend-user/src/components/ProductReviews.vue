<template>
  <div class="product-reviews">
    <div class="reviews-header">
      <h2 class="section-title">
        <span class="title-icon">
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
          </svg>
        </span>
        用户评价
        <span class="review-count">({{ summary.total_count || 0 }})</span>
      </h2>
    </div>

    <div class="reviews-content">
      <div class="reviews-summary">
        <div class="average-score">
          <div class="score-number">
            {{ summary.average_rating || '0.0' }}
          </div>
          <StarRating
            :average-rating="summary.average_rating"
            :readonly="true"
            :size="18"
          />
          <div class="score-label">
            综合评分
          </div>
        </div>

        <div class="rating-bars">
          <div
            v-for="star in [5, 4, 3, 2, 1]"
            :key="star"
            class="rating-bar-item"
          >
            <span class="star-label">{{ star }}星</span>
            <div class="bar-container">
              <div 
                class="bar-fill" 
                :style="{ width: getBarWidth(star) + '%' }"
              />
            </div>
            <span class="count-label">{{ summary.rating_distribution?.[star] || 0 }}</span>
          </div>
        </div>
      </div>

      <div class="reviews-main">
        <div class="filter-tabs">
          <button
            v-for="tab in filterTabs"
            :key="tab.id"
            :class="{ active: activeFilter === tab.id }"
            @click="handleFilterChange(tab.id)"
          >
            {{ tab.label }}
            <span
              v-if="getFilterCount(tab.id) > 0"
              class="tab-count"
            >
              {{ getFilterCount(tab.id) }}
            </span>
          </button>
        </div>

        <div
          v-if="loading"
          class="loading-state"
        >
          <div class="loading-spinner" />
          <p>加载评价中...</p>
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
              <div class="user-info">
                <div class="user-avatar">
                  <img
                    v-if="review.user_avatar"
                    :src="review.user_avatar"
                    :alt="review.user_name"
                  >
                  <span v-else>{{ review.user_name?.charAt(0) || '用' }}</span>
                </div>
                <div class="user-details">
                  <span class="user-name">{{ review.user_name }}</span>
                  <div class="review-meta">
                    <StarRating
                      :model-value="review.rating"
                      :readonly="true"
                      :size="14"
                    />
                    <span class="review-date">{{ formatDate(review.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-if="review.comment"
              class="review-content"
            >
              <p>{{ review.comment }}</p>
            </div>

            <div
              v-if="review.images && review.images.length > 0"
              class="review-images"
            >
              <div
                v-for="(img, idx) in review.images"
                :key="idx"
                class="review-image"
                @click="openImagePreview(img)"
              >
                <img
                  :src="img"
                  alt="评价图片"
                >
              </div>
            </div>
          </div>
        </div>

        <div
          v-else
          class="empty-state"
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
          <p>暂无评价</p>
          <span class="empty-hint">快来成为第一个评价的人吧！</span>
        </div>

        <div
          v-if="totalPages > 1 && reviews.length > 0"
          class="pagination"
        >
          <button
            class="page-btn"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M15 18l-6-6 6-6" />
            </svg>
            上一页
          </button>
          <div class="page-numbers">
            <button
              v-for="page in totalPages"
              :key="page"
              :class="['page-num', { active: currentPage === page }]"
              @click="currentPage = page"
            >
              {{ page }}
            </button>
          </div>
          <button
            class="page-btn"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            下一页
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M9 18l6-6-6-6" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="previewImage"
      class="image-preview-modal"
      @click="closeImagePreview"
    >
      <div
        class="preview-content"
        @click.stop
      >
        <img
          :src="previewImage"
          alt="预览图片"
        >
        <button
          class="close-btn"
          @click="closeImagePreview"
        >
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <line
              x1="18"
              y1="6"
              x2="6"
              y2="18"
            />
            <line
              x1="6"
              y1="6"
              x2="18"
              y2="18"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import StarRating from './StarRating.vue'
import { reviewApi } from '@/utils/api'

const props = defineProps({
  productId: {
    type: [Number, String],
    required: true,
  },
  perPage: {
    type: Number,
    default: 10,
  },
})

const reviews = ref([])
const summary = ref({
  total_count: 0,
  average_rating: 0,
  rating_distribution: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
})
const loading = ref(false)
const activeFilter = ref('all')
const currentPage = ref(1)
const totalPages = ref(1)
const previewImage = ref('')

const filterTabs = [
  { id: 'all', label: '全部' },
  { id: 'good', label: '好评' },
  { id: 'medium', label: '中评' },
  { id: 'bad', label: '差评' },
]

const getBarWidth = (star) => {
  const total = summary.value.total_count || 1
  const count = summary.value.rating_distribution?.[star] || 0
  return (count / total) * 100
}

const getFilterCount = (filterId) => {
  if (filterId === 'all') return summary.value.total_count || 0
  if (filterId === 'good') return summary.value.rating_distribution?.[5] || 0
  if (filterId === 'medium') {
    return (summary.value.rating_distribution?.[4] || 0) + (summary.value.rating_distribution?.[3] || 0)
  }
  if (filterId === 'bad') {
    return (summary.value.rating_distribution?.[2] || 0) + (summary.value.rating_distribution?.[1] || 0)
  }
  return 0
}

const handleFilterChange = (filter) => {
  activeFilter.value = filter
  currentPage.value = 1
  fetchReviews()
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const fetchSummary = async () => {
  try {
    const data = await reviewApi.getReviewSummary(props.productId)
    summary.value = data
  } catch (e) {
    console.error('获取评价摘要失败:', e)
  }
}

const fetchReviews = async () => {
  loading.value = true
  try {
    const data = await reviewApi.getProductReviews(props.productId, {
      page: currentPage.value,
      per_page: props.perPage,
      rating_filter: activeFilter.value,
    })
    reviews.value = data.reviews || []
    totalPages.value = data.total_pages || 1
  } catch (e) {
    console.error('获取评价列表失败:', e)
    reviews.value = []
  } finally {
    loading.value = false
  }
}

const openImagePreview = (img) => {
  previewImage.value = img
}

const closeImagePreview = () => {
  previewImage.value = ''
}

watch(() => props.productId, () => {
  currentPage.value = 1
  activeFilter.value = 'all'
  loadData()
})

watch(currentPage, () => {
  fetchReviews()
})

const loadData = () => {
  fetchSummary()
  fetchReviews()
}

onMounted(() => {
  loadData()
})

defineExpose({
  refresh: loadData,
})
</script>

<style lang="scss" scoped>
.product-reviews {
  background: #fff;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.reviews-header {
  margin-bottom: 32px;

  .section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;

    .title-icon {
      color: #6366f1;
    }

    .review-count {
      font-size: 16px;
      font-weight: 500;
      color: #999;
    }
  }
}

.reviews-content {
  display: flex;
  gap: 40px;
}

.reviews-summary {
  width: 280px;
  flex-shrink: 0;
  background: #fafafa;
  border-radius: 16px;
  padding: 24px;

  .average-score {
    text-align: center;
    padding-bottom: 24px;
    border-bottom: 1px solid #eee;
    margin-bottom: 24px;

    .score-number {
      font-size: 48px;
      font-weight: 800;
      color: #f59e0b;
      line-height: 1;
      margin-bottom: 8px;
    }

    .score-label {
      font-size: 14px;
      color: #666;
      margin-top: 8px;
    }
  }

  .rating-bars {
    .rating-bar-item {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;

      &:last-child {
        margin-bottom: 0;
      }

      .star-label {
        width: 36px;
        font-size: 13px;
        color: #666;
        flex-shrink: 0;
      }

      .bar-container {
        flex: 1;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;

        .bar-fill {
          height: 100%;
          background: linear-gradient(90deg, #fbbf24, #f59e0b);
          border-radius: 4px;
          transition: width 0.3s ease;
        }
      }

      .count-label {
        width: 32px;
        text-align: right;
        font-size: 13px;
        color: #999;
        flex-shrink: 0;
      }
    }
  }
}

.reviews-main {
  flex: 1;
  min-width: 0;
}

.filter-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;

  button {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    background: #f5f5f5;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      background: #eee;
      color: #333;
    }

    &.active {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      color: #fff;

      .tab-count {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
      }
    }

    .tab-count {
      padding: 2px 8px;
      background: #e5e7eb;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 600;
    }
  }
}

.loading-state {
  text-align: center;
  padding: 60px 0;
  color: #999;

  .loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #6366f1;
    border-radius: 50%;
    margin: 0 auto 16px;
    animation: spin 1s linear infinite;
  }

  p {
    font-size: 14px;
    margin: 0;
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.reviews-list {
  .review-item {
    padding: 24px 0;
    border-bottom: 1px solid #f5f5f5;

    &:last-child {
      border-bottom: none;
    }

    .review-header {
      margin-bottom: 16px;

      .user-info {
        display: flex;
        align-items: center;
        gap: 12px;

        .user-avatar {
          width: 44px;
          height: 44px;
          border-radius: 50%;
          background: linear-gradient(135deg, #6366f1, #8b5cf6);
          display: flex;
          align-items: center;
          justify-content: center;
          color: #fff;
          font-size: 18px;
          font-weight: 600;
          overflow: hidden;
          flex-shrink: 0;

          img {
            width: 100%;
            height: 100%;
            object-fit: cover;
          }
        }

        .user-details {
          .user-name {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
          }

          .review-meta {
            display: flex;
            align-items: center;
            gap: 12px;

            .review-date {
              font-size: 13px;
              color: #999;
            }
          }
        }
      }
    }

    .review-content {
      margin-bottom: 16px;

      p {
        font-size: 15px;
        color: #333;
        line-height: 1.7;
        margin: 0;
      }
    }

    .review-images {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;

      .review-image {
        width: 100px;
        height: 100px;
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.2s ease;

        &:hover {
          transform: scale(1.05);
        }

        img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }
      }
    }
  }
}

.empty-state {
  text-align: center;
  padding: 60px 0;
  color: #ddd;

  svg {
    margin-bottom: 16px;
  }

  p {
    font-size: 16px;
    color: #666;
    margin: 0 0 8px;
  }

  .empty-hint {
    font-size: 14px;
    color: #999;
  }
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #f0f0f0;

  .page-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #1a1a1a;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
      border-color: #6366f1;
      color: #6366f1;
    }

    &:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
  }

  .page-numbers {
    display: flex;
    gap: 8px;

    .page-num {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      border: 1px solid #e5e5e5;
      border-radius: 10px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      color: #666;
      transition: all 0.2s ease;

      &:hover {
        border-color: #6366f1;
        color: #6366f1;
      }

      &.active {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-color: transparent;
        color: #fff;
      }
    }
  }
}

.image-preview-modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 40px;

  .preview-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;

    img {
      max-width: 100%;
      max-height: 90vh;
      border-radius: 12px;
    }

    .close-btn {
      position: absolute;
      top: -40px;
      right: 0;
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.1);
      border: none;
      border-radius: 50%;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;

      &:hover {
        background: rgba(255, 255, 255, 0.2);
      }
    }
  }
}

@media (max-width: 900px) {
  .reviews-content {
    flex-direction: column;
    gap: 24px;
  }

  .reviews-summary {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .product-reviews {
    padding: 24px 20px;
    border-radius: 16px;
  }

  .reviews-header {
    margin-bottom: 24px;

    .section-title {
      font-size: 20px;
    }
  }

  .reviews-summary {
    padding: 20px;

    .average-score {
      .score-number {
        font-size: 40px;
      }
    }
  }

  .filter-tabs {
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 12px;
    -webkit-overflow-scrolling: touch;

    button {
      flex-shrink: 0;
    }
  }

  .review-images {
    .review-image {
      width: 80px;
      height: 80px;
    }
  }
}
</style>
