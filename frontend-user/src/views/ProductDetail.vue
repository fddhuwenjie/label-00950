<template>
  <div class="product-detail-page">
    <div class="container">
      <div class="breadcrumb">
        <router-link to="/">首页</router-link>
        <span>/</span>
        <router-link to="/products">商品</router-link>
        <span>/</span>
        <span>{{ product?.name }}</span>
      </div>
      
      <div v-if="product" class="product-detail">
        <!-- 商品图片 -->
        <div class="product-gallery">
          <div class="main-image">
            <img :src="currentImage" :alt="product.name" />
          </div>
          <div class="thumbnail-list" v-if="product.images && product.images.length > 1">
            <div 
              v-for="(img, index) in product.images" 
              :key="index"
              class="thumbnail"
              :class="{ active: currentImageIndex === index }"
              @click="currentImageIndex = index"
            >
              <img :src="img" :alt="`${product.name} - ${index + 1}`" />
            </div>
          </div>
        </div>
        
        <!-- 商品信息 -->
        <div class="product-info">
          <h1 class="product-name">{{ product.name }}</h1>

          <div class="product-rating-bar" v-if="reviewSummary.count > 0">
            <StarRating :model-value="reviewSummary.average" readonly size="md" />
            <span class="rating-score">{{ reviewSummary.average.toFixed(1) }}</span>
            <span class="rating-count">{{ reviewSummary.count }} 条评价</span>
          </div>
          <div class="product-rating-bar empty" v-else>
            <StarRating :model-value="0" readonly size="md" />
            <span class="rating-count">暂无评价</span>
          </div>

          <div class="product-price">
            <span class="current-price">${{ displayPrice }}</span>
            <span v-if="product.salePrice" class="original-price">${{ product.price.toFixed(2) }}</span>
            <span v-if="product.salePrice" class="discount-badge">-{{ discountPercent }}%</span>
          </div>
          
          <div class="product-desc">
            <p>{{ product.description || '暂无商品描述' }}</p>
          </div>
          
          <div class="product-meta">
            <div class="meta-item">
              <span class="label">分类：</span>
              <span class="value">{{ getCategoryName(product.category) }}</span>
            </div>
            <div class="meta-item">
              <span class="label">库存：</span>
              <span class="value">{{ product.stock || 99 }} 件</span>
            </div>
          </div>
          
          <div class="quantity-selector">
            <span class="label">数量：</span>
            <div class="quantity-control">
              <button @click="quantity > 1 && quantity--">-</button>
              <input type="number" v-model.number="quantity" min="1" :max="product.stock || 99" />
              <button @click="quantity < (product.stock || 99) && quantity++">+</button>
            </div>
          </div>
          
          <div class="product-actions">
            <button class="btn-add-cart" @click="handleAddToCart">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
              </svg>
              加入购物车
            </button>
            <button class="btn-buy-now" @click="handleBuyNow">立即购买</button>
          </div>
          
          <div class="product-features">
            <div class="feature">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
              <span>正品保障</span>
            </div>
            <div class="feature">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13" rx="2"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
              </svg>
              <span>全球配送</span>
            </div>
            <div class="feature">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              <span>7天无理由退换</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 商品评价模块 -->
      <ProductReviews v-if="product" :product-id="product.id" class="reviews-section" />

      <div v-else class="loading">
        <p>加载中...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useUserStore } from '@/stores/user'
import toast from '@/utils/toast'
import { productApi, productReviewApi } from '@/utils/api'
import StarRating from '@/components/StarRating.vue'
import ProductReviews from '@/components/ProductReviews.vue'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()
const userStore = useUserStore()

const product = ref(null)
const quantity = ref(1)
const currentImageIndex = ref(0)
const reviewSummary = reactive({ average: 0, count: 0 })

const categories = {
  electronics: '数码电子',
  fashion: '时尚服饰',
  beauty: '美妆护肤',
  home: '家居生活'
}

const currentImage = computed(() => {
  if (product.value?.images && product.value.images.length > 0) {
    return product.value.images[currentImageIndex.value]
  }
  return product.value?.image || ''
})

const displayPrice = computed(() => {
  return (product.value?.salePrice || product.value?.price || 0).toFixed(2)
})

const discountPercent = computed(() => {
  if (!product.value?.salePrice) return 0
  return Math.round((1 - product.value.salePrice / product.value.price) * 100)
})

const getCategoryName = (slug) => {
  return categories[slug] || slug
}

const handleAddToCart = () => {
  if (!userStore.isLoggedIn) {
    toast.warning('请先登录后再加入购物车')
    router.push('/login')
    return
  }
  
  for (let i = 0; i < quantity.value; i++) {
    cartStore.addToCart({
      id: product.value.id,
      name: product.value.name,
      price: product.value.salePrice || product.value.price,
      image: product.value.image
    })
  }
  
  toast.success(`已将 ${quantity.value} 件商品加入购物车`)
}

const handleBuyNow = () => {
  if (!userStore.isLoggedIn) {
    toast.warning('请先登录')
    router.push('/login')
    return
  }
  
  handleAddToCart()
  router.push('/cart')
}

onMounted(async () => {
  try {
    product.value = await productApi.getById(route.params.id)
  } catch (e) {
    console.error('加载商品失败:', e)
    toast.error('商品不存在')
    router.push('/products')
    return
  }

  // 拉取综合评分
  try {
    const data = await productReviewApi.summary(route.params.id)
    reviewSummary.average = Number(data.average || 0)
    reviewSummary.count = Number(data.count || 0)
  } catch (e) {
    // 静默忽略
  }
})
</script>

<style lang="scss" scoped>
.product-detail-page {
  padding: 40px 0;
  background: #f8f9fa;
  min-height: calc(100vh - 200px);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 30px;
  font-size: 14px;
  color: #666;
  
  a {
    color: #666;
    text-decoration: none;
    
    &:hover {
      color: #6366f1;
    }
  }
}

.product-detail {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  background: #fff;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.product-gallery {
  .main-image {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 16px;
    overflow: hidden;
    background: #f5f5f5;
    margin-bottom: 16px;
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }
  
  .thumbnail-list {
    display: flex;
    gap: 12px;
  }
  
  .thumbnail {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
    
    &:hover {
      border-color: #ddd;
    }
    
    &.active {
      border-color: #6366f1;
    }
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }
}

.product-info {
  .product-name {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
    line-height: 1.4;
  }

  .product-rating-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px dashed #eee;

    .rating-score {
      font-size: 18px;
      font-weight: 700;
      color: #f59e0b;
    }

    .rating-count {
      font-size: 14px;
      color: #999;
    }

    &.empty .rating-count {
      color: #bbb;
    }
  }

  .product-price {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    
    .current-price {
      font-size: 32px;
      font-weight: 800;
      color: #1a1a1a;
    }
    
    .original-price {
      font-size: 18px;
      color: #999;
      text-decoration: line-through;
    }
    
    .discount-badge {
      padding: 4px 12px;
      background: linear-gradient(135deg, #ef4444, #f97316);
      color: #fff;
      font-size: 14px;
      font-weight: 600;
      border-radius: 6px;
    }
  }
  
  .product-desc {
    margin-bottom: 24px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    
    p {
      color: #666;
      line-height: 1.8;
      margin: 0;
    }
  }
  
  .product-meta {
    margin-bottom: 24px;
    
    .meta-item {
      display: flex;
      margin-bottom: 12px;
      
      .label {
        color: #999;
        width: 60px;
      }
      
      .value {
        color: #333;
      }
    }
  }
  
  .quantity-selector {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 30px;
    
    .label {
      color: #666;
    }
    
    .quantity-control {
      display: flex;
      align-items: center;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      overflow: hidden;
      
      button {
        width: 40px;
        height: 40px;
        border: none;
        background: #f5f5f5;
        cursor: pointer;
        font-size: 18px;
        color: #333;
        
        &:hover {
          background: #e8e8e8;
        }
      }
      
      input {
        width: 60px;
        height: 40px;
        border: none;
        text-align: center;
        font-size: 16px;
        -moz-appearance: textfield;
        
        &::-webkit-outer-spin-button,
        &::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }
      }
    }
  }
  
  .product-actions {
    display: flex;
    gap: 16px;
    margin-bottom: 30px;
    
    button {
      flex: 1;
      padding: 16px 32px;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    
    .btn-add-cart {
      background: #fff;
      border: 2px solid #1a1a1a;
      color: #1a1a1a;
      
      &:hover {
        background: #1a1a1a;
        color: #fff;
      }
    }
    
    .btn-buy-now {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border: none;
      color: #fff;
      
      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
      }
    }
  }
  
  .product-features {
    display: flex;
    gap: 24px;
    padding-top: 24px;
    border-top: 1px solid #eee;
    
    .feature {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #666;
      font-size: 14px;
      
      svg {
        color: #6366f1;
      }
    }
  }
}

.reviews-section {
  margin-top: 30px;
  background: #fff;
  border-radius: 20px;
  padding: 30px 40px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.loading {
  text-align: center;
  padding: 100px 0;
  color: #666;
}

@media (max-width: 768px) {
  .product-detail {
    grid-template-columns: 1fr;
    gap: 30px;
    padding: 20px;
  }
  
  .product-info {
    .product-name {
      font-size: 22px;
    }
    
    .product-price .current-price {
      font-size: 26px;
    }
    
    .product-actions {
      flex-direction: column;
    }
    
    .product-features {
      flex-wrap: wrap;
    }
  }
}
</style>
