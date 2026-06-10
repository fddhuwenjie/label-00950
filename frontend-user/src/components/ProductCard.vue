<template>
  <div class="product-card" @click="goToProduct">
    <div class="product-image">
      <img :src="product.image" :alt="product.name" loading="lazy" />
      <div v-if="product.salePrice" class="sale-badge">
        -{{ discountPercent }}%
      </div>
      <div class="quick-actions">
        <button class="action-btn" @click.stop="handleAddToCart" title="加入购物车">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1"/>
            <circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          <span>加入购物车</span>
        </button>
      </div>
    </div>
    <div class="product-info">
      <h3 class="product-name">{{ product.name }}</h3>
      <div class="product-rating" v-if="hasRating">
        <StarRating :model-value="ratingAverage" readonly size="sm" />
        <span class="rating-score">{{ ratingAverage.toFixed(1) }}</span>
        <span class="rating-count">({{ ratingCount }})</span>
      </div>
      <div class="product-rating empty" v-else>
        <StarRating :model-value="0" readonly size="sm" />
        <span class="rating-count">暂无评价</span>
      </div>
      <div class="product-price">
        <span class="current-price">${{ displayPrice }}</span>
        <span v-if="product.salePrice" class="original-price">
          ${{ product.price.toFixed(2) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useUserStore } from '@/stores/user'
import toast from '@/utils/toast'
import StarRating from '@/components/StarRating.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  ratingSummary: {
    type: Object,
    default: () => ({ average: 0, count: 0 })
  }
})

const router = useRouter()
const cartStore = useCartStore()
const userStore = useUserStore()

const ratingAverage = computed(() => {
  const v = Number(props.product?.ratingAverage ?? props.ratingSummary?.average ?? 0)
  return Number.isFinite(v) ? v : 0
})

const ratingCount = computed(() => {
  const v = Number(props.product?.ratingCount ?? props.ratingSummary?.count ?? 0)
  return Number.isFinite(v) ? v : 0
})

const hasRating = computed(() => ratingCount.value > 0)

const displayPrice = computed(() => {
  return (props.product.salePrice || props.product.price).toFixed(2)
})

const discountPercent = computed(() => {
  if (!props.product.salePrice) return 0
  return Math.round((1 - props.product.salePrice / props.product.price) * 100)
})

const goToProduct = () => {
  router.push(`/product/${props.product.id}`)
}

const handleAddToCart = () => {
  // 检查是否登录
  if (!userStore.isLoggedIn) {
    toast.warning('请先登录后再加入购物车')
    router.push('/login')
    return
  }
  
  cartStore.addToCart({
    id: props.product.id,
    name: props.product.name,
    price: props.product.salePrice || props.product.price,
    image: props.product.image
  })
  
  toast.success('已加入购物车')
}
</script>

<style lang="scss" scoped>
.product-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid #f0f0f0;
  
  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border-color: transparent;
    
    .product-image img {
      transform: scale(1.08);
    }
    
    .quick-actions {
      opacity: 1;
      transform: translateY(0);
    }
  }
}

.product-image {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  background: #f8f8f8;
  
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .sale-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 8px 14px;
    background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
  }
}

.quick-actions {
  position: absolute;
  bottom: 16px;
  left: 16px;
  right: 16px;
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.3s ease;
}

.action-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 20px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: none;
  border-radius: 12px;
  cursor: pointer;
  color: #1a1a1a;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s ease;
  
  &:hover {
    background: #1a1a1a;
    color: #fff;
  }
}

.product-info {
  padding: 20px;
}

.product-name {
  font-size: 15px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 8px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 45px;
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 10px;
  font-size: 12px;

  .rating-score {
    font-weight: 700;
    color: #f59e0b;
  }

  .rating-count {
    color: #999;
  }

  &.empty .rating-count {
    color: #bbb;
  }
}

.product-price {
  display: flex;
  align-items: center;
  gap: 10px;
  
  .current-price {
    font-size: 20px;
    font-weight: 800;
    color: #1a1a1a;
  }
  
  .original-price {
    font-size: 14px;
    color: #999;
    text-decoration: line-through;
  }
}
</style>
