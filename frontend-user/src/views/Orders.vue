<template>
  <div class="orders-page">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">
          我的订单
        </h1>
        <p class="page-subtitle">
          查看和管理您的所有订单
        </p>
      </div>
      
      <div class="orders-tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
          <span
            v-if="getTabCount(tab.id) > 0"
            class="tab-count"
          >{{ getTabCount(tab.id) }}</span>
        </button>
      </div>
      
      <div
        v-if="loading"
        class="loading-state"
      >
        <p>加载中...</p>
      </div>
      <div
        v-else-if="filteredOrders.length > 0"
        class="orders-list"
      >
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="order-card"
        >
          <div class="order-header">
            <div class="order-info">
              <span class="order-number">订单号: {{ order.id }}</span>
              <span class="order-date">{{ order.date }}</span>
            </div>
            <span
              class="order-status"
              :class="order.status"
            >
              {{ getStatusText(order.status) }}
            </span>
          </div>
          
          <div class="order-items">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="order-item"
            >
              <div class="item-image">
                <img
                  :src="item.image"
                  :alt="item.name"
                >
              </div>
              <div class="item-info">
                <span class="item-name">{{ item.name }}</span>
                <span class="item-meta">
                  <span class="item-price">${{ item.price.toFixed(2) }}</span>
                  <span class="item-qty">x{{ item.quantity }}</span>
                </span>
                <div
                  v-if="order.status === 'completed'"
                  class="item-review-action"
                >
                  <button 
                    v-if="!isItemReviewed(order.id, item.id)"
                    class="btn-review" 
                    @click="openReviewModal(order, item)"
                  >
                    去评价
                  </button>
                  <span
                    v-else
                    class="reviewed-tag"
                  >已评价</span>
                </div>
              </div>
              <span class="item-total">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
          
          <div class="order-footer">
            <div class="order-total">
              共 {{ order.items.reduce((sum, i) => sum + i.quantity, 0) }} 件商品，
              合计: <span class="total-price">${{ order.total.toFixed(2) }}</span>
            </div>
            <div class="order-actions">
              <button
                v-if="order.status === 'pending'"
                class="btn-primary"
                @click="handlePay(order)"
              >
                去支付
              </button>
              <button
                class="btn-outline"
                @click="viewOrderDetail(order)"
              >
                订单详情
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div
        v-else
        class="empty-orders"
      >
        <div class="empty-icon">
          <svg
            width="80"
            height="80"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1"
          >
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line
              x1="16"
              y1="13"
              x2="8"
              y2="13"
            />
            <line
              x1="16"
              y1="17"
              x2="8"
              y2="17"
            />
            <polyline points="10 9 9 9 8 9" />
          </svg>
        </div>
        <h2>暂无订单</h2>
        <p>快去选购心仪的商品吧</p>
        <router-link
          to="/products"
          class="shop-btn"
        >
          去购物
        </router-link>
      </div>
    </div>
    
    <!-- 订单详情弹窗 -->
    <div
      v-if="showDetailModal"
      class="modal-overlay"
      @click="closeModal"
    >
      <div
        class="modal-content"
        @click.stop
      >
        <div class="modal-header">
          <h3>订单详情</h3>
          <button
            class="close-btn"
            @click="closeModal"
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
        
        <div
          v-if="selectedOrder"
          class="modal-body"
        >
          <div class="detail-section">
            <h4>订单信息</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="label">订单号</span>
                <span class="value">{{ selectedOrder.id }}</span>
              </div>
              <div class="detail-item">
                <span class="label">下单时间</span>
                <span class="value">{{ selectedOrder.date }}</span>
              </div>
              <div class="detail-item">
                <span class="label">订单状态</span>
                <span class="value">
                  <span
                    class="status-tag"
                    :class="selectedOrder.status"
                  >
                    {{ getStatusText(selectedOrder.status) }}
                  </span>
                </span>
              </div>
              <div class="detail-item">
                <span class="label">订单金额</span>
                <span class="value price">${{ selectedOrder.total.toFixed(2) }}</span>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h4>收货信息</h4>
            <div class="detail-grid">
              <div class="detail-item full">
                <span class="label">收货人</span>
                <span class="value">{{ selectedOrder.shipping?.name || '张三' }}</span>
              </div>
              <div class="detail-item full">
                <span class="label">联系电话</span>
                <span class="value">{{ selectedOrder.shipping?.phone || '138****8888' }}</span>
              </div>
              <div class="detail-item full">
                <span class="label">收货地址</span>
                <span class="value">{{ selectedOrder.shipping?.address || '北京市朝阳区xxx街道xxx号' }}</span>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h4>商品清单</h4>
            <div class="items-list">
              <div
                v-for="item in selectedOrder.items"
                :key="item.id"
                class="item-row"
              >
                <img
                  :src="item.image"
                  :alt="item.name"
                >
                <div class="item-detail">
                  <span class="name">{{ item.name }}</span>
                  <span class="meta">${{ item.price.toFixed(2) }} x {{ item.quantity }}</span>
                </div>
                <span class="subtotal">${{ (item.price * item.quantity).toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 支付弹窗 -->
    <div
      v-if="showPayModal"
      class="modal-overlay"
      @click="closePayModal"
    >
      <div
        class="modal-content pay-modal"
        @click.stop
      >
        <div class="modal-header">
          <h3>确认支付</h3>
          <button
            class="close-btn"
            @click="closePayModal"
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
        
        <div
          v-if="payOrder"
          class="modal-body"
        >
          <div class="pay-amount">
            <span class="pay-label">支付金额</span>
            <span class="pay-price">${{ payOrder.total.toFixed(2) }}</span>
          </div>
          
          <div class="pay-methods">
            <label
              class="pay-method"
              :class="{ active: payMethod === 'alipay' }"
            >
              <input
                v-model="payMethod"
                type="radio"
                value="alipay"
              >
              <span class="method-icon alipay">
                <span class="icon-text">支</span>
              </span>
              <div class="method-info">
                <span class="method-name">支付宝</span>
                <span class="method-desc">Alipay</span>
              </div>
            </label>
            <label
              class="pay-method"
              :class="{ active: payMethod === 'wechat' }"
            >
              <input
                v-model="payMethod"
                type="radio"
                value="wechat"
              >
              <span class="method-icon wechat">
                <span class="icon-text">微</span>
              </span>
              <div class="method-info">
                <span class="method-name">微信支付</span>
                <span class="method-desc">WeChat Pay</span>
              </div>
            </label>
          </div>
          
          <button
            class="pay-submit-btn"
            @click="confirmPay"
          >
            确认支付
          </button>
        </div>
      </div>
    </div>

    <!-- 评价弹窗 -->
    <div
      v-if="showReviewModal"
      class="modal-overlay"
      @click="closeReviewModal"
    >
      <div
        class="modal-content review-modal"
        @click.stop
      >
        <div class="modal-header">
          <h3>发表评价</h3>
          <button
            class="close-btn"
            @click="closeReviewModal"
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
        
        <div
          v-if="reviewItem"
          class="modal-body"
        >
          <div class="review-product-info">
            <img
              :src="reviewItem.image"
              :alt="reviewItem.name"
            >
            <div class="review-product-detail">
              <span class="product-name">{{ reviewItem.name }}</span>
              <span class="product-price">${{ reviewItem.price.toFixed(2) }} x {{ reviewItem.quantity }}</span>
            </div>
          </div>

          <div class="review-form">
            <div class="form-group">
              <label class="form-label">商品评分</label>
              <div class="rating-input">
                <StarRating
                  v-model="reviewForm.rating"
                  :readonly="false"
                  :size="32"
                />
                <span class="rating-text">{{ ratingText }}</span>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">评价内容</label>
              <textarea 
                v-model="reviewForm.comment" 
                class="review-textarea" 
                placeholder="分享您的使用体验，帮助其他买家做出选择~"
                maxlength="500"
                rows="4"
              />
              <span class="char-count">{{ reviewForm.comment.length }}/500</span>
            </div>

            <div class="form-group">
              <label class="form-label">上传图片（最多3张）</label>
              <div class="image-upload-area">
                <div 
                  v-for="(img, idx) in reviewForm.images" 
                  :key="idx" 
                  class="uploaded-image"
                >
                  <img
                    :src="img"
                    alt="评价图片"
                  >
                  <button
                    class="remove-image-btn"
                    @click="removeImage(idx)"
                  >
                    <svg
                      width="16"
                      height="16"
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
                <label 
                  v-if="reviewForm.images.length < 3" 
                  class="upload-btn"
                >
                  <svg
                    width="28"
                    height="28"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <rect
                      x="3"
                      y="3"
                      width="18"
                      height="18"
                      rx="2"
                    />
                    <circle
                      cx="8.5"
                      cy="8.5"
                      r="1.5"
                    />
                    <polyline points="21 15 16 10 5 21" />
                  </svg>
                  <span>上传图片</span>
                  <input 
                    type="file" 
                    accept="image/*" 
                    style="display: none;"
                    @change="handleImageUpload"
                  >
                </label>
              </div>
            </div>
          </div>

          <button 
            class="submit-review-btn" 
            :disabled="submittingReview || reviewForm.rating === 0"
            @click="submitReview"
          >
            <span v-if="submittingReview">提交中...</span>
            <span v-else>提交评价</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import toast from '@/utils/toast'
import { orderApi, reviewApi } from '@/utils/api'
import StarRating from '@/components/StarRating.vue'

const activeTab = ref('all')
const showDetailModal = ref(false)
const showPayModal = ref(false)
const showReviewModal = ref(false)
const selectedOrder = ref(null)
const payOrder = ref(null)
const reviewOrder = ref(null)
const reviewItem = ref(null)
const payMethod = ref('alipay')
const loading = ref(false)
const submittingReview = ref(false)
const reviewForm = ref({
  rating: 0,
  comment: '',
  images: [],
})

const reviewedItemsMap = ref({})

const tabs = [
  { id: 'all', label: '全部订单' },
  { id: 'pending', label: '待付款' },
  { id: 'processing', label: '处理中' },
  { id: 'on-hold', label: '配送中' },
  { id: 'completed', label: '已完成' },
]

const orders = ref([])

onMounted(async () => {
  await fetchOrders()
})

const fetchOrders = async () => {
  loading.value = true
  try {
    const data = await orderApi.getAll()
    orders.value = data.map(o => ({
      id: o.number || o.id,
      rawId: o.id,
      status: o.status,
      date: o.date,
      total: o.total,
      shipping: {
        name: o.billing?.name || '',
        phone: o.billing?.phone || '',
        address: o.billing?.address || '',
      },
      items: (o.items || []).map(item => ({
        id: item.id,
        productId: item.product_id,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        image: item.image || '',
      })),
    }))

    const completedOrders = orders.value.filter(o => o.status === 'completed')
    for (const order of completedOrders) {
      await loadReviewStatus(order.rawId)
    }
  } catch (e) {
    // 未登录或无订单
    orders.value = []
  } finally {
    loading.value = false
  }
}

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') {
    return orders.value
  }
  return orders.value.filter(o => o.status === activeTab.value)
})

const getTabCount = (tabId) => {
  if (tabId === 'all') return orders.value.length
  return orders.value.filter(o => o.status === tabId).length
}

const getStatusText = (status) => {
  const texts = {
    pending: '待付款',
    processing: '处理中',
    'on-hold': '配送中',
    completed: '已完成',
    cancelled: '已取消',
    refunded: '已退款',
    failed: '失败'
  }
  return texts[status] || status
}

const viewOrderDetail = (order) => {
  selectedOrder.value = order
  showDetailModal.value = true
}

const closeModal = () => {
  showDetailModal.value = false
  selectedOrder.value = null
}

const handlePay = (order) => {
  payOrder.value = order
  showPayModal.value = true
}

const closePayModal = () => {
  showPayModal.value = false
  payOrder.value = null
}

const confirmPay = async () => {
  if (payOrder.value) {
    try {
      await orderApi.pay(payOrder.value.rawId)
      payOrder.value.status = 'processing'
      toast.success('支付成功！订单正在处理中')
      closePayModal()
    } catch (e) {
      toast.error(e.message || '支付失败，请重试')
    }
  }
}

const ratingText = computed(() => {
  const rating = reviewForm.value.rating
  const texts = {
    1: '很差',
    2: '较差',
    3: '一般',
    4: '满意',
    5: '非常满意',
  }
  return texts[rating] || '请评分'
})

const isItemReviewed = (orderId, itemId) => {
  const key = `${orderId}_${itemId}`
  return !!reviewedItemsMap.value[key]
}

const openReviewModal = (order, item) => {
  reviewOrder.value = order
  reviewItem.value = item
  reviewForm.value = {
    rating: 5,
    comment: '',
    images: [],
  }
  showReviewModal.value = true
}

const closeReviewModal = () => {
  showReviewModal.value = false
  reviewOrder.value = null
  reviewItem.value = null
}

const handleImageUpload = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    toast.error('请选择图片文件')
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    toast.error('图片大小不能超过5MB')
    return
  }

  if (reviewForm.value.images.length >= 3) {
    toast.error('最多只能上传3张图片')
    return
  }

  try {
    const result = await reviewApi.uploadReviewImage(file)
    reviewForm.value.images.push(result.url)
  } catch (e) {
    toast.error(e.message || '图片上传失败')
  }

  e.target.value = ''
}

const removeImage = (index) => {
  reviewForm.value.images.splice(index, 1)
}

const submitReview = async () => {
  if (!reviewForm.value.rating) {
    toast.warning('请先评分')
    return
  }

  submittingReview.value = true
  try {
    await reviewApi.createReview({
      product_id: reviewItem.value.productId || reviewItem.value.id,
      order_id: reviewOrder.value.rawId,
      order_item_id: reviewItem.value.id,
      rating: reviewForm.value.rating,
      comment: reviewForm.value.comment,
      images: reviewForm.value.images,
    })

    toast.success('评价提交成功！')
    
    const key = `${reviewOrder.value.id}_${reviewItem.value.id}`
    reviewedItemsMap.value[key] = true

    closeReviewModal()
  } catch (e) {
    toast.error(e.message || '评价提交失败，请重试')
  } finally {
    submittingReview.value = false
  }
}

const loadReviewStatus = async (orderId) => {
  try {
    const result = await reviewApi.getOrderReviewStatus(orderId)
    result.reviewed_items?.forEach(item => {
      const key = `${orderId}_${item.order_item_id}`
      reviewedItemsMap.value[key] = true
    })
  } catch (e) {
    console.error('获取评价状态失败:', e)
  }
}
</script>

<style lang="scss" scoped>
.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 40px 20px 80px;
}

.page-header {
  margin-bottom: 32px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 8px;
}

.page-subtitle {
  font-size: 16px;
  color: #666;
}

.orders-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  background: #fff;
  padding: 8px;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  
  button {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 16px;
    background: none;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &:hover {
      background: #f5f5f5;
      color: #1a1a1a;
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
      background: #f0f0f0;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 600;
    }
  }
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.order-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid #f0f0f0;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
  
  .order-info {
    display: flex;
    gap: 20px;
    
    .order-number {
      font-weight: 600;
      color: #1a1a1a;
    }
    
    .order-date {
      color: #999;
      font-size: 14px;
    }
  }
  
  .order-status {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    
    &.pending {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #b45309;
    }
    
    &.processing {
      background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
      color: #1d4ed8;
    }
    
    &.on-hold {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      color: #047857;
    }
    
    &.completed {
      background: #f3f4f6;
      color: #6b7280;
    }
    
    &.cancelled {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #b91c1c;
    }
  }
}

.order-items {
  padding: 20px 24px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 16px 0;
  border-bottom: 1px solid #f5f5f5;
  
  &:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  
  &:first-child {
    padding-top: 0;
  }
  
  .item-image {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    background: #f8f8f8;
    flex-shrink: 0;
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }
  
  .item-info {
    flex: 1;
    
    .item-name {
      display: block;
      font-size: 15px;
      font-weight: 500;
      color: #1a1a1a;
      margin-bottom: 8px;
      line-height: 1.4;
    }
    
    .item-meta {
      display: flex;
      align-items: center;
      gap: 12px;
      
      .item-price {
        font-size: 14px;
        color: #666;
      }
      
      .item-qty {
        color: #999;
        font-size: 14px;
      }
    }
  }
  
  .item-total {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
  }
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-top: 1px solid #f0f0f0;
  background: #fafafa;
  
  .order-total {
    font-size: 15px;
    color: #666;
    
    .total-price {
      font-size: 22px;
      font-weight: 700;
      color: #ef4444;
    }
  }
  
  .order-actions {
    display: flex;
    gap: 12px;
    
    .btn-primary {
      padding: 10px 24px;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      
      &:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      }
    }
    
    .btn-outline {
      padding: 10px 24px;
      background: #fff;
      color: #6366f1;
      border: 1.5px solid #6366f1;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      
      &:hover {
        background: #f5f3ff;
      }
    }
  }
}

.loading-state {
  text-align: center;
  padding: 60px 0;
  color: #999;
  font-size: 16px;
}

.empty-orders {
  text-align: center;
  padding: 100px 0;
  
  .empty-icon {
    margin-bottom: 24px;
    
    svg {
      color: #ddd;
    }
  }
  
  h2 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
  }
  
  p {
    color: #999;
    margin-bottom: 32px;
    font-size: 16px;
  }
  
  .shop-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 40px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    text-decoration: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
    }
  }
}

// Modal Styles
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: #fff;
  border-radius: 24px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #f0f0f0;
  
  h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
  }
  
  .close-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
    
    &:hover {
      background: #eee;
      color: #1a1a1a;
    }
  }
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
}

.detail-section {
  margin-bottom: 28px;
  
  &:last-child {
    margin-bottom: 0;
  }
  
  h4 {
    font-size: 14px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0f0f0;
  }
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
  
  &.full {
    grid-column: span 2;
  }
  
  .label {
    font-size: 13px;
    color: #999;
  }
  
  .value {
    font-size: 15px;
    font-weight: 500;
    color: #1a1a1a;
    
    &.price {
      font-size: 20px;
      font-weight: 700;
      color: #ef4444;
    }
  }
  
  .status-tag {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    
    &.pending { background: #fef3c7; color: #b45309; }
    &.processing { background: #dbeafe; color: #1d4ed8; }
    &.shipping { background: #d1fae5; color: #047857; }
    &.completed { background: #f3f4f6; color: #6b7280; }
  }
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.item-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #fafafa;
  border-radius: 12px;
  
  img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
  }
  
  .item-detail {
    flex: 1;
    
    .name {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #1a1a1a;
      margin-bottom: 4px;
    }
    
    .meta {
      font-size: 13px;
      color: #999;
    }
  }
  
  .subtotal {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
  }
}

// Pay Modal
.pay-modal {
  max-width: 420px;
}

.pay-amount {
  text-align: center;
  padding: 24px;
  background: #fafafa;
  border-radius: 16px;
  margin-bottom: 24px;
  
  .pay-label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
  }
  
  .pay-price {
    font-size: 36px;
    font-weight: 800;
    color: #1a1a1a;
  }
}

.pay-methods {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.pay-method {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 20px;
  border: 2px solid #f0f0f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  
  input {
    display: none;
  }
  
  &.active {
    border-color: #6366f1;
    background: #fafaff;
  }
  
  .method-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    
    .icon-text {
      font-size: 22px;
      font-weight: 800;
      color: #fff;
    }
    
    &.alipay {
      background: linear-gradient(135deg, #1677ff 0%, #0052d9 100%);
      box-shadow: 0 4px 12px rgba(22, 119, 255, 0.3);
    }
    
    &.wechat {
      background: linear-gradient(135deg, #07c160 0%, #06a94d 100%);
      box-shadow: 0 4px 12px rgba(7, 193, 96, 0.3);
    }
  }
  
  .method-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  
  .method-name {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
  }
  
  .method-desc {
    font-size: 12px;
    color: #999;
  }
}

.pay-submit-btn {
  width: 100%;
  padding: 18px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
  }
}

// Review Modal
.review-modal {
  max-width: 560px;
}

.item-review-action {
  margin-top: 8px;
}

.btn-review {
  padding: 6px 14px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &:hover {
    opacity: 0.9;
  }
}

.reviewed-tag {
  display: inline-block;
  padding: 4px 10px;
  background: #f0f0f0;
  border-radius: 4px;
  font-size: 12px;
  color: #999;
}

.review-product-info {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #fafafa;
  border-radius: 12px;
  margin-bottom: 24px;
  
  img {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    object-fit: cover;
  }
}

.review-product-detail {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
  
  .product-name {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
    line-height: 1.4;
  }
  
  .product-price {
    font-size: 14px;
    color: #ef4444;
    font-weight: 500;
  }
}

.review-form {
  margin-bottom: 24px;
}

.form-group {
  margin-bottom: 24px;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 12px;
}

.rating-input {
  display: flex;
  align-items: center;
  gap: 16px;
}

.rating-text {
  font-size: 14px;
  color: #6366f1;
  font-weight: 600;
}

.review-textarea {
  width: 100%;
  padding: 14px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  color: #1a1a1a;
  resize: vertical;
  font-family: inherit;
  transition: all 0.2s ease;
  
  &:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  }
  
  &::placeholder {
    color: #9ca3af;
  }
}

.char-count {
  display: block;
  text-align: right;
  font-size: 12px;
  color: #999;
  margin-top: 8px;
}

.image-upload-area {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.uploaded-image {
  position: relative;
  width: 100px;
  height: 100px;
  border-radius: 8px;
  overflow: hidden;
  
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.remove-image-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 22px;
  height: 22px;
  background: rgba(0, 0, 0, 0.6);
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &:hover {
    background: rgba(0, 0, 0, 0.8);
  }
}

.upload-btn {
  width: 100px;
  height: 100px;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #9ca3af;
  
  &:hover {
    border-color: #6366f1;
    color: #6366f1;
    background: #fafaff;
  }
  
  span {
    font-size: 12px;
  }
}

.submit-review-btn {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
  }
}

@media (max-width: 768px) {
  .container {
    padding: 20px 16px;
  }
  
  .page-header {
    margin-bottom: 20px;
    
    h1 {
      font-size: 22px;
    }
  }
  
  .orders-tabs {
    flex-wrap: wrap;
    gap: 8px;
    padding: 16px;
    
    button {
      flex: 0 0 calc(50% - 4px);
      padding: 10px 12px;
      font-size: 13px;
    }
  }
  
  .order-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    
    .order-info {
      flex-direction: column;
      gap: 4px;
      
      .order-number {
        font-size: 13px;
      }
      
      .order-date {
        font-size: 12px;
      }
    }
  }
  
  .order-items {
    padding: 16px;
  }
  
  .order-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    
    .item-image {
      width: 70px;
      height: 70px;
    }
    
    .item-info {
      width: 100%;
    }
    
    .item-total {
      align-self: flex-end;
    }
  }
  
  .order-footer {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    
    .order-total .total-price {
      font-size: 20px;
    }
    
    .order-actions {
      width: 100%;
      
      button {
        flex: 1;
        padding: 10px 16px;
        font-size: 13px;
      }
    }
  }
  
  .modal-overlay {
    padding: 16px;
  }
  
  .modal-content {
    border-radius: 20px;
    max-height: 85vh;
  }
  
  .modal-header {
    padding: 20px;
    
    h3 {
      font-size: 18px;
    }
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .detail-item.full {
    grid-column: span 1;
  }
  
  .item-row {
    padding: 12px;
    
    img {
      width: 50px;
      height: 50px;
    }
    
    .item-detail .name {
      font-size: 13px;
    }
  }
  
  .empty-orders {
    padding: 60px 20px;
    
    h2 {
      font-size: 20px;
    }
    
    .shop-btn {
      padding: 14px 32px;
      font-size: 15px;
    }
  }
  
  .pay-amount .pay-price {
    font-size: 28px;
  }
  
  .pay-method {
    padding: 14px 16px;
    
    .method-icon {
      width: 44px;
      height: 44px;
      
      .icon-text {
        font-size: 18px;
      }
    }
    
    .method-name {
      font-size: 15px;
    }
  }
  
  .pay-submit-btn {
    padding: 16px;
    font-size: 15px;
  }
  
  .review-product-info {
    padding: 12px;
    gap: 12px;
    
    img {
      width: 60px;
      height: 60px;
    }
  }
  
  .review-product-detail .product-name {
    font-size: 14px;
  }
  
  .uploaded-image, .upload-btn {
    width: 80px;
    height: 80px;
  }
  
  .submit-review-btn {
    padding: 14px;
    font-size: 15px;
  }
}
</style>
