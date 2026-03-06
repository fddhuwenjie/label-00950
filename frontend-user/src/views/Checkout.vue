<template>
  <div class="checkout-page">
    <div class="container">
      <h1 class="page-title">结算</h1>
      
      <div class="checkout-content">
        <div class="checkout-form">
          <!-- 收货地址 -->
          <section class="form-section">
            <h2>收货地址</h2>
            <div class="form-grid">
              <div class="form-group" :class="{ error: errors.name }">
                <label>收货人姓名 *</label>
                <input 
                  type="text" 
                  v-model="form.name" 
                  placeholder="请输入姓名"
                  @blur="validateField('name')"
                />
                <span v-if="errors.name" class="error-msg">{{ errors.name }}</span>
              </div>
              <div class="form-group" :class="{ error: errors.phone }">
                <label>联系电话 *</label>
                <input 
                  type="tel" 
                  v-model="form.phone" 
                  placeholder="请输入电话"
                  @blur="validateField('phone')"
                />
                <span v-if="errors.phone" class="error-msg">{{ errors.phone }}</span>
              </div>
              <div class="form-group" :class="{ error: errors.email }">
                <label>电子邮箱 *</label>
                <input 
                  type="email" 
                  v-model="form.email" 
                  placeholder="请输入邮箱"
                  @blur="validateField('email')"
                />
                <span v-if="errors.email" class="error-msg">{{ errors.email }}</span>
              </div>
              <div class="form-group" :class="{ error: errors.country }">
                <label>国家/地区 *</label>
                <select v-model="form.country" @change="validateField('country')">
                  <option value="">请选择</option>
                  <option value="CN">中国</option>
                  <option value="US">美国</option>
                  <option value="UK">英国</option>
                  <option value="JP">日本</option>
                  <option value="AU">澳大利亚</option>
                </select>
                <span v-if="errors.country" class="error-msg">{{ errors.country }}</span>
              </div>
              <div class="form-group full-width" :class="{ error: errors.address }">
                <label>详细地址 *</label>
                <input 
                  type="text" 
                  v-model="form.address" 
                  placeholder="请输入详细地址"
                  @blur="validateField('address')"
                />
                <span v-if="errors.address" class="error-msg">{{ errors.address }}</span>
              </div>
              <div class="form-group">
                <label>城市</label>
                <input type="text" v-model="form.city" placeholder="请输入城市" />
              </div>
              <div class="form-group">
                <label>邮政编码</label>
                <input type="text" v-model="form.zipCode" placeholder="请输入邮编" />
              </div>
            </div>
          </section>
          
          <!-- 配送方式 -->
          <section class="form-section">
            <h2>配送方式</h2>
            <div class="shipping-options">
              <label class="shipping-option" :class="{ active: form.shippingMethod === 'standard' }">
                <input type="radio" v-model="form.shippingMethod" value="standard" />
                <div class="option-content">
                  <span class="option-name">标准物流</span>
                  <span class="option-time">{{ shippingSettings.estimatedDelivery }}</span>
                </div>
                <span class="option-price">${{ shippingSettings.defaultShippingFee }}</span>
              </label>
              <label class="shipping-option" :class="{ active: form.shippingMethod === 'express' }">
                <input type="radio" v-model="form.shippingMethod" value="express" />
                <div class="option-content">
                  <span class="option-name">国际快递</span>
                  <span class="option-time">3-5 个工作日</span>
                </div>
                <span class="option-price">${{ expressShippingFee }}</span>
              </label>
            </div>
          </section>
          
          <!-- 支付方式 -->
          <section class="form-section">
            <h2>支付方式</h2>
            <div class="payment-options">
              <label class="payment-option" :class="{ active: form.paymentMethod === 'paypal' }">
                <input type="radio" v-model="form.paymentMethod" value="paypal" />
                <span class="option-name">PayPal</span>
              </label>
              <label class="payment-option" :class="{ active: form.paymentMethod === 'card' }">
                <input type="radio" v-model="form.paymentMethod" value="card" />
                <span class="option-name">信用卡/借记卡</span>
              </label>
            </div>
          </section>
        </div>
        
        <!-- 订单摘要 -->
        <div class="order-summary">
          <h2>订单摘要</h2>
          
          <div class="order-items">
            <div v-for="item in cartStore.items" :key="item.id" class="order-item">
              <img :src="item.image" :alt="item.name" />
              <div class="item-info">
                <span class="item-name">{{ item.name }}</span>
                <span class="item-qty">x{{ item.quantity }}</span>
              </div>
              <span class="item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
          
          <div class="summary-details">
            <div class="summary-row">
              <span>商品小计</span>
              <span>${{ cartStore.totalPrice.toFixed(2) }}</span>
            </div>
            <div class="summary-row">
              <span>运费</span>
              <span>${{ shippingFee.toFixed(2) }}</span>
            </div>
            <div class="summary-row total">
              <span>订单总计</span>
              <span>${{ orderTotal.toFixed(2) }}</span>
            </div>
          </div>
          
          <button class="place-order-btn" @click="placeOrder" :disabled="submitting">
            {{ submitting ? '提交中...' : '提交订单' }}
          </button>
          
          <p class="terms">
            点击"提交订单"即表示您同意我们的
            <a href="/terms" target="_blank">服务条款</a> 和 <a href="/privacy" target="_blank">隐私政策</a>
          </p>
        </div>
      </div>
    </div>
  </div>
  
  <!-- 支付弹窗 -->
  <div v-if="showPayModal" class="modal-overlay" @click="closePayModal">
    <div class="modal-content pay-modal" @click.stop>
      <div class="modal-header">
        <h3>确认支付</h3>
        <button class="close-btn" @click="closePayModal">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="pay-amount">
          <span class="pay-label">支付金额</span>
          <span class="pay-price">${{ pendingOrder ? pendingOrder.total.toFixed(2) : '0.00' }}</span>
        </div>
        
        <div class="pay-methods">
          <label class="pay-method" :class="{ active: payMethod === 'alipay' }">
            <input type="radio" v-model="payMethod" value="alipay" />
            <span class="method-icon alipay">
              <span class="icon-text">支</span>
            </span>
            <div class="method-info">
              <span class="method-name">支付宝</span>
              <span class="method-desc">Alipay</span>
            </div>
          </label>
          <label class="pay-method" :class="{ active: payMethod === 'wechat' }">
            <input type="radio" v-model="payMethod" value="wechat" />
            <span class="method-icon wechat">
              <span class="icon-text">微</span>
            </span>
            <div class="method-info">
              <span class="method-name">微信支付</span>
              <span class="method-desc">WeChat Pay</span>
            </div>
          </label>
          <label class="pay-method" :class="{ active: payMethod === 'paypal' }">
            <input type="radio" v-model="payMethod" value="paypal" />
            <span class="method-icon paypal">
              <span class="icon-text">P</span>
            </span>
            <div class="method-info">
              <span class="method-name">PayPal</span>
              <span class="method-desc">International</span>
            </div>
          </label>
        </div>
        
        <button class="pay-submit-btn" @click="confirmPay" :disabled="paying">
          {{ paying ? '支付中...' : '确认支付' }}
        </button>
        
        <button class="pay-later-btn" @click="payLater">
          稍后支付
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import toast from '@/utils/toast'
import { settingsApi, orderApi, crossBorderApi } from '@/utils/api'

const router = useRouter()
const cartStore = useCartStore()
const submitting = ref(false)
const showPayModal = ref(false)
const pendingOrder = ref(null)
const payMethod = ref('alipay')
const paying = ref(false)
const shippingSettings = reactive({
  defaultShippingFee: 15,
  estimatedDelivery: '7-15个工作日',
})
const expressShippingFee = computed(() => shippingSettings.defaultShippingFee * 2.5)

onMounted(async () => {
  try {
    const settings = await settingsApi.get()
    if (settings.defaultShippingFee) shippingSettings.defaultShippingFee = settings.defaultShippingFee
    if (settings.estimatedDelivery) shippingSettings.estimatedDelivery = settings.estimatedDelivery
  } catch (e) {
    // 使用默认值
  }
})

const form = reactive({
  name: '',
  phone: '',
  email: '',
  country: '',
  address: '',
  city: '',
  zipCode: '',
  shippingMethod: 'standard',
  paymentMethod: 'paypal'
})

const errors = reactive({
  name: '',
  phone: '',
  email: '',
  country: '',
  address: ''
})

const shippingFee = computed(() => {
  return form.shippingMethod === 'express' ? expressShippingFee.value : shippingSettings.defaultShippingFee
})

const orderTotal = computed(() => {
  return cartStore.totalPrice + shippingFee.value
})

// 验证单个字段
const validateField = (field) => {
  switch (field) {
    case 'name':
      if (!form.name.trim()) {
        errors.name = '请输入收货人姓名'
      } else if (form.name.length < 2) {
        errors.name = '姓名至少2个字符'
      } else {
        errors.name = ''
      }
      break
    case 'phone':
      if (!form.phone.trim()) {
        errors.phone = '请输入联系电话'
      } else {
        // 支持中国大陆手机号、国际号码格式
        const phoneClean = form.phone.replace(/[\s\-()]/g, '')
        const chinaPhone = /^1[3-9]\d{9}$/.test(phoneClean)
        const intlPhone = /^\+?\d{8,15}$/.test(phoneClean)
        if (!chinaPhone && !intlPhone) {
          errors.phone = '请输入有效的手机号码（如：13812345678）'
        } else {
          errors.phone = ''
        }
      }
      break
    case 'email':
      if (!form.email.trim()) {
        errors.email = '请输入电子邮箱'
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = '请输入有效的邮箱地址'
      } else {
        errors.email = ''
      }
      break
    case 'country':
      if (!form.country) {
        errors.country = '请选择国家/地区'
      } else {
        errors.country = ''
      }
      break
    case 'address':
      if (!form.address.trim()) {
        errors.address = '请输入详细地址'
      } else if (form.address.length < 5) {
        errors.address = '地址至少5个字符'
      } else {
        errors.address = ''
      }
      break
  }
  return !errors[field]
}

// 验证所有必填字段
const validateForm = () => {
  const fields = ['name', 'phone', 'email', 'country', 'address']
  let isValid = true
  fields.forEach(field => {
    if (!validateField(field)) {
      isValid = false
    }
  })
  return isValid
}

const placeOrder = async () => {
  if (!validateForm()) {
    toast.error('请完整填写收货信息')
    return
  }
  
  if (cartStore.items.length === 0) {
    toast.error('购物车为空')
    return
  }
  
  submitting.value = true
  
  try {
    const orderData = {
      items: cartStore.items.map(item => ({
        id: item.id,
        quantity: item.quantity,
      })),
      billing: {
        firstName: form.name,
        lastName: '',
        email: form.email,
        phone: form.phone,
        address: form.address,
        city: form.city,
        postcode: form.zipCode,
        country: form.country,
      },
      shipping_fee: shippingFee.value,
    }
    
    const result = await orderApi.create(orderData)
    cartStore.clearCart()
    pendingOrder.value = result
    showPayModal.value = true
    toast.success(`订单创建成功！订单号: ${result.number || result.id}`)
  } catch (error) {
    toast.error(error.message || '订单提交失败，请重试')
  } finally {
    submitting.value = false
  }
}

const closePayModal = () => {
  showPayModal.value = false
}

const confirmPay = async () => {
  if (!pendingOrder.value) return
  paying.value = true
  try {
    await orderApi.pay(pendingOrder.value.id)
    toast.success('支付成功！订单正在处理中')
    showPayModal.value = false
    router.push('/orders')
  } catch (error) {
    toast.error(error.message || '支付失败，请重试')
  } finally {
    paying.value = false
  }
}

const payLater = () => {
  showPayModal.value = false
  toast.info('订单已创建，您可以稍后在"我的订单"中支付')
  router.push('/orders')
}
</script>

<style lang="scss" scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

.page-title {
  font-size: 28px;
  margin-bottom: 30px;
  color: #333;
}

.checkout-content {
  display: flex;
  gap: 30px;
}

.checkout-form {
  flex: 1;
}

.form-section {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  padding: 24px;
  margin-bottom: 20px;
  
  h2 {
    font-size: 18px;
    margin-bottom: 20px;
    color: #333;
  }
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  
  .full-width {
    grid-column: 1 / -1;
  }
}

.form-group {
  label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
  }
  
  input, select {
    width: 100%;
    padding: 12px;
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
    
    &:focus {
      outline: none;
      border-color: #6366f1;
    }
  }
  
  &.error {
    input, select {
      border-color: #ef4444;
    }
  }
  
  .error-msg {
    display: block;
    color: #ef4444;
    font-size: 12px;
    margin-top: 6px;
  }
}

.shipping-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.shipping-option {
  display: flex;
  align-items: center;
  padding: 16px;
  border: 2px solid #e8e8e8;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &.active {
    border-color: #6366f1;
    background: #f8f8ff;
  }
  
  input {
    display: none;
  }
  
  .option-content {
    flex: 1;
    margin-left: 12px;
    
    .option-name {
      display: block;
      font-weight: 500;
      color: #333;
    }
    
    .option-time {
      font-size: 13px;
      color: #999;
    }
  }
  
  .option-price {
    font-weight: 600;
    color: #333;
  }
}

.payment-options {
  display: flex;
  gap: 12px;
}

.payment-option {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  border: 2px solid #e8e8e8;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &.active {
    border-color: #6366f1;
    background: #f8f8ff;
  }
  
  input {
    display: none;
  }
  
  .option-name {
    font-weight: 500;
    color: #333;
  }
}

.order-summary {
  width: 400px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  padding: 24px;
  height: fit-content;
  
  h2 {
    font-size: 18px;
    margin-bottom: 20px;
    color: #333;
  }
}

.order-items {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #eee;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  
  &:last-child {
    margin-bottom: 0;
  }
  
  img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .item-info {
    flex: 1;
    
    .item-name {
      display: block;
      font-size: 14px;
      color: #333;
      line-height: 1.4;
    }
    
    .item-qty {
      font-size: 13px;
      color: #999;
    }
  }
  
  .item-price {
    font-weight: 500;
    color: #333;
  }
}

.summary-details {
  margin-bottom: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  color: #666;
  
  &.total {
    border-top: 1px solid #eee;
    margin-top: 12px;
    padding-top: 16px;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    
    span:last-child {
      color: #f56c6c;
    }
  }
}

.place-order-btn {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &:hover:not(:disabled) {
    opacity: 0.9;
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.terms {
  margin-top: 16px;
  font-size: 12px;
  color: #999;
  text-align: center;
  
  a {
    color: #6366f1;
    text-decoration: none;
    
    &:hover {
      text-decoration: underline;
    }
  }
}

@media (max-width: 992px) {
  .checkout-content {
    flex-direction: column;
  }
  
  .order-summary {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 20px 16px;
  }
  
  .page-title {
    font-size: 22px;
    margin-bottom: 20px;
  }
  
  .checkout-form,
  .order-summary {
    padding: 20px;
  }
  
  .form-section {
    padding: 20px;
    margin-bottom: 16px;
    
    h3 {
      font-size: 16px;
    }
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .shipping-options {
    flex-direction: column;
  }
  
  .shipping-option {
    padding: 14px;
    
    .option-content .option-name {
      font-size: 14px;
    }
  }
  
  .payment-options {
    flex-direction: column;
  }
  
  .payment-option {
    padding: 14px;
  }
  
  .order-summary h2 {
    font-size: 16px;
  }
  
  .order-item {
    img {
      width: 50px;
      height: 50px;
    }
    
    .item-info .item-name {
      font-size: 13px;
    }
  }
  
  .place-order-btn {
    padding: 14px;
    font-size: 15px;
  }
}

// 支付弹窗样式
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

.pay-modal {
  background: #fff;
  border-radius: 24px;
  width: 100%;
  max-width: 420px;
  overflow: hidden;
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
  
  input { display: none; }
  
  &.active {
    border-color: #6366f1;
    background: #fafaff;
  }
  
  .method-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    
    .icon-text {
      font-size: 20px;
      font-weight: 800;
      color: #fff;
    }
    
    &.alipay {
      background: linear-gradient(135deg, #1677ff 0%, #0052d9 100%);
    }
    
    &.wechat {
      background: linear-gradient(135deg, #07c160 0%, #06a94d 100%);
    }
    
    &.paypal {
      background: linear-gradient(135deg, #003087 0%, #009cde 100%);
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
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.pay-later-btn {
  width: 100%;
  padding: 14px;
  background: none;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  color: #666;
  cursor: pointer;
  margin-top: 12px;
  transition: all 0.2s ease;
  
  &:hover {
    border-color: #999;
    color: #333;
  }
}
</style>
