<template>
  <section class="product-reviews">
    <div class="reviews-header">
      <h2 class="section-title">商品评价</h2>
      <div class="summary" v-if="summary.count > 0">
        <span class="avg-score">{{ summary.average.toFixed(1) }}</span>
        <StarRating :model-value="summary.average" readonly size="md" />
        <span class="total-count">共 {{ summary.count }} 条评价</span>
      </div>
      <div class="summary empty" v-else>
        <span>暂无评价</span>
      </div>
    </div>

    <div class="filter-tabs">
      <button
        v-for="tab in filterTabs"
        :key="tab.id"
        :class="{ active: filter === tab.id }"
        @click="changeFilter(tab.id)"
      >
        {{ tab.label }}
        <span class="count">({{ distribution[tab.id] || 0 }})</span>
      </button>
    </div>

    <div v-if="loading" class="state">加载中...</div>

    <div v-else-if="reviews.length === 0" class="state empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
      </svg>
      <p>{{ filter === 'all' ? '还没有评价，快来抢沙发吧～' : '该筛选条件下暂无评价' }}</p>
    </div>

    <ul v-else class="review-list">
      <li v-for="r in reviews" :key="r.id" class="review-item">
        <div class="review-head">
          <div class="user">
            <img v-if="r.avatar" :src="r.avatar" :alt="r.nickname" class="avatar" />
            <div v-else class="avatar avatar-fallback">{{ (r.nickname || '匿').slice(0, 1) }}</div>
            <div class="user-meta">
              <span class="nickname">{{ r.nickname }}</span>
              <span class="date">{{ formatDate(r.created_at) }}</span>
            </div>
          </div>
          <StarRating :model-value="r.rating" readonly size="sm" />
        </div>

        <div class="review-content">{{ r.content }}</div>

        <div v-if="r.images && r.images.length > 0" class="review-images">
          <img
            v-for="(img, idx) in r.images"
            :key="idx"
            :src="img"
            class="thumb"
            :alt="`评价图片 ${idx + 1}`"
            @click="previewImage(img)"
          />
        </div>
      </li>
    </ul>

    <div v-if="!loading && reviews.length > 0 && total > reviews.length" class="load-more">
      <button @click="loadMore" :disabled="loadingMore">
        {{ loadingMore ? '加载中...' : '加载更多评价' }}
      </button>
    </div>

    <!-- 大图预览 -->
    <div v-if="previewSrc" class="image-preview" @click="previewSrc = ''">
      <img :src="previewSrc" alt="预览" />
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { productReviewApi } from '@/utils/api'
import StarRating from './StarRating.vue'

const props = defineProps({
  productId: {
    type: [Number, String],
    required: true,
  },
})

const filterTabs = [
  { id: 'all', label: '全部' },
  { id: 'good', label: '好评 (5星)' },
  { id: 'mid', label: '中评 (3-4星)' },
  { id: 'bad', label: '差评 (1-2星)' },
]

const filter = ref('all')
const reviews = ref([])
const total = ref(0)
const page = ref(1)
const perPage = 10
const loading = ref(false)
const loadingMore = ref(false)
const previewSrc = ref('')

const summary = reactive({ average: 0, count: 0 })
const distribution = reactive({ all: 0, good: 0, mid: 0, bad: 0 })

const fetchSummary = async () => {
  try {
    const data = await productReviewApi.summary(props.productId)
    summary.average = Number(data.average || 0)
    summary.count = Number(data.count || 0)
  } catch (e) {
    summary.average = 0
    summary.count = 0
  }
}

const fetchReviews = async (reset = true) => {
  if (reset) {
    page.value = 1
    loading.value = true
  } else {
    loadingMore.value = true
  }
  try {
    const data = await productReviewApi.list(props.productId, {
      filter: filter.value,
      page: page.value,
      perPage,
    })
    if (reset) {
      reviews.value = data.reviews || []
    } else {
      reviews.value = reviews.value.concat(data.reviews || [])
    }
    total.value = Number(data.total || 0)
    if (data.distribution) {
      distribution.all = data.distribution.all || 0
      distribution.good = data.distribution.good || 0
      distribution.mid = data.distribution.mid || 0
      distribution.bad = data.distribution.bad || 0
    }
  } catch (e) {
    if (reset) reviews.value = []
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const changeFilter = (id) => {
  if (filter.value === id) return
  filter.value = id
  fetchReviews(true)
}

const loadMore = async () => {
  page.value += 1
  await fetchReviews(false)
}

const previewImage = (src) => {
  previewSrc.value = src
}

const formatDate = (s) => {
  if (!s) return ''
  // 后端返回 'YYYY-MM-DD HH:mm:ss'，简单替换以兼容 Safari
  return s.replace(' ', ' ').slice(0, 16)
}

const reload = async () => {
  await Promise.all([fetchSummary(), fetchReviews(true)])
}

defineExpose({ reload })

onMounted(() => {
  reload()
})

watch(
  () => props.productId,
  () => {
    reload()
  }
)
</script>

<style lang="scss" scoped>
.product-reviews {
  margin-top: 32px;
  background: #fff;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.reviews-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
  margin-bottom: 20px;

  .section-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
  }

  .summary {
    display: flex;
    align-items: center;
    gap: 12px;

    .avg-score {
      font-size: 28px;
      font-weight: 800;
      color: #f59e0b;
    }

    .total-count {
      color: #999;
      font-size: 14px;
    }

    &.empty {
      color: #999;
      font-size: 14px;
    }
  }
}

.filter-tabs {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 20px;

  button {
    padding: 8px 16px;
    background: #f5f5f5;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: 13px;
    color: #555;
    cursor: pointer;
    transition: all 0.2s;

    .count {
      color: #999;
      margin-left: 4px;
    }

    &:hover {
      background: #ececec;
    }

    &.active {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      color: #fff;
      border-color: transparent;

      .count { color: rgba(255,255,255,0.85); }
    }
  }
}

.state {
  text-align: center;
  padding: 50px 0;
  color: #999;

  &.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: #bbb;

    p { margin: 0; font-size: 14px; color: #999; }
  }
}

.review-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.review-item {
  padding: 20px 0;
  border-bottom: 1px solid #f5f5f5;

  &:last-child { border-bottom: none; }
}

.review-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;

  .user {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background: #eee;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;

    &.avatar-fallback {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
    }
  }

  .user-meta {
    display: flex;
    flex-direction: column;
    gap: 2px;

    .nickname {
      font-size: 14px;
      font-weight: 600;
      color: #1a1a1a;
    }

    .date {
      font-size: 12px;
      color: #999;
    }
  }
}

.review-content {
  font-size: 14px;
  color: #333;
  line-height: 1.7;
  white-space: pre-wrap;
  word-break: break-word;
}

.review-images {
  display: flex;
  gap: 10px;
  margin-top: 12px;
  flex-wrap: wrap;

  .thumb {
    width: 90px;
    height: 90px;
    border-radius: 10px;
    object-fit: cover;
    cursor: pointer;
    border: 1px solid #eee;
    transition: transform 0.2s;

    &:hover { transform: scale(1.04); }
  }
}

.load-more {
  text-align: center;
  margin-top: 24px;

  button {
    padding: 10px 28px;
    background: #fff;
    color: #6366f1;
    border: 1.5px solid #6366f1;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &:hover:not(:disabled) {
      background: #f5f3ff;
    }

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }
}

.image-preview {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  cursor: zoom-out;

  img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  }
}

@media (max-width: 768px) {
  .product-reviews {
    padding: 20px;
  }

  .reviews-header {
    .section-title { font-size: 18px; }
    .summary .avg-score { font-size: 22px; }
  }

  .review-images .thumb {
    width: 76px;
    height: 76px;
  }
}
</style>
