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
            <a href="#">服务条款</a> 和 <a href="#">隐私政策</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import toast from '@/utils/toast'
import { getShippingSettings } from '@/utils/settings'

const router = useRouter()
const cartStore = useCartStore()
const submitting = ref(false)
const shippingSettings = reactive(getShippingSettings())
const expressShippingFee = computed(() => shippingSettings.defaultShippingFee * 2.5) // 快递费为标准运费的2.5倍

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
    // 模拟订单提交
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    const orderNo = 'ORD-' + Date.now()
    cartStore.clearCart()
    toast.success(`订单提交成功！订单号: ${orderNo}`)
    router.push('/orders')
  } catch (error) {
    toast.error('订单提交失败，请重试')
  } finally {
    submitting.value = false
  }
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
</style>
