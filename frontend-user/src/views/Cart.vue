<template>
  <div class="cart-page">
    <div class="container">
      <h1 class="page-title">购物车</h1>
      
      <div v-if="cartStore.items.length > 0" class="cart-content">
        <div class="cart-items">
          <div class="cart-header">
            <span class="col-product">商品信息</span>
            <span class="col-price">单价</span>
            <span class="col-quantity">数量</span>
            <span class="col-total">小计</span>
            <span class="col-action">操作</span>
          </div>
          
          <div 
            v-for="item in cartStore.items" 
            :key="item.id" 
            class="cart-item"
          >
            <div class="col-product">
              <img :src="item.image" :alt="item.name" />
              <div class="product-info">
                <router-link :to="`/product/${item.id}`" class="product-name">
                  {{ item.name }}
                </router-link>
              </div>
            </div>
            <div class="col-price">
              ${{ item.price.toFixed(2) }}
            </div>
            <div class="col-quantity">
              <div class="quantity-input">
                <button @click="updateQuantity(item.id, item.quantity - 1)">-</button>
                <input 
                  type="number" 
                  :value="item.quantity" 
                  @change="updateQuantity(item.id, +$event.target.value)"
                  min="1"
                />
                <button @click="updateQuantity(item.id, item.quantity + 1)">+</button>
              </div>
            </div>
            <div class="col-total">
              ${{ (item.price * item.quantity).toFixed(2) }}
            </div>
            <div class="col-action">
              <button class="remove-btn" @click="removeItem(item.id)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <div class="cart-summary">
          <h3>订单摘要</h3>
          <div class="summary-row">
            <span>商品小计</span>
            <span>${{ cartStore.totalPrice.toFixed(2) }}</span>
          </div>
          <div class="summary-row">
            <span>运费</span>
            <span>{{ shippingFee > 0 ? '$' + shippingFee.toFixed(2) : '免费' }}</span>
          </div>
          <div class="summary-row total">
            <span>订单总计</span>
            <span>${{ (cartStore.totalPrice + shippingFee).toFixed(2) }}</span>
          </div>
          
          <div class="free-shipping-tip" v-if="cartStore.totalPrice < 99">
            <p>再买 ${{ (99 - cartStore.totalPrice).toFixed(2) }} 即可免运费</p>
            <div class="progress-bar">
              <div 
                class="progress" 
                :style="{ width: Math.min(cartStore.totalPrice / 99 * 100, 100) + '%' }"
              ></div>
            </div>
          </div>
          
          <router-link to="/checkout" class="checkout-btn">
            去结算 ({{ cartStore.itemCount }} 件)
          </router-link>
          
          <router-link to="/products" class="continue-shopping">
            继续购物
          </router-link>
        </div>
      </div>
      
      <div v-else class="empty-cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <h2>购物车是空的</h2>
        <p>快去挑选心仪的商品吧</p>
        <router-link to="/products" class="shop-btn">去购物</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCartStore } from '@/stores/cart'

const cartStore = useCartStore()

const shippingFee = computed(() => {
  return cartStore.totalPrice >= 99 ? 0 : 9.99
})

const updateQuantity = (id, quantity) => {
  if (quantity < 1) return
  cartStore.updateQuantity(id, quantity)
}

const removeItem = (id) => {
  cartStore.removeFromCart(id)
}
</script>

<style lang="scss" scoped>
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px;
}

.page-title {
  font-size: 28px;
  margin-bottom: 30px;
  color: #333;
}

.cart-content {
  display: flex;
  gap: 30px;
}

.cart-items {
  flex: 1;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.cart-header {
  display: flex;
  padding: 16px 20px;
  background: #f8f9fa;
  font-weight: 500;
  color: #666;
  font-size: 14px;
}

.cart-item {
  display: flex;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
  
  &:last-child {
    border-bottom: none;
  }
}

.col-product {
  flex: 2;
  display: flex;
  align-items: center;
  gap: 16px;
  
  img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .product-name {
    color: #333;
    text-decoration: none;
    
    &:hover {
      color: #667eea;
    }
  }
}

.col-price {
  flex: 1;
  text-align: center;
  color: #333;
}

.col-quantity {
  flex: 1;
  display: flex;
  justify-content: center;
}

.quantity-input {
  display: flex;
  border: 1px solid #e8e8e8;
  border-radius: 6px;
  overflow: hidden;
  
  button {
    width: 32px;
    height: 32px;
    background: #f5f5f5;
    border: none;
    cursor: pointer;
    
    &:hover {
      background: #eee;
    }
  }
  
  input {
    width: 50px;
    height: 32px;
    text-align: center;
    border: none;
    
    &::-webkit-inner-spin-button {
      -webkit-appearance: none;
    }
  }
}

.col-total {
  flex: 1;
  text-align: center;
  font-weight: 600;
  color: #f56c6c;
}

.col-action {
  width: 60px;
  text-align: center;
  
  .remove-btn {
    padding: 8px;
    background: none;
    border: none;
    cursor: pointer;
    color: #999;
    
    &:hover {
      color: #f56c6c;
    }
  }
}

.cart-summary {
  width: 350px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  padding: 24px;
  height: fit-content;
  
  h3 {
    font-size: 18px;
    margin-bottom: 20px;
    color: #333;
  }
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  color: #666;
  
  &.total {
    border-top: 1px solid #eee;
    margin-top: 12px;
    padding-top: 20px;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    
    span:last-child {
      color: #f56c6c;
    }
  }
}

.free-shipping-tip {
  margin: 20px 0;
  padding: 16px;
  background: #fef6e6;
  border-radius: 8px;
  
  p {
    font-size: 14px;
    color: #e6a23c;
    margin-bottom: 8px;
  }
  
  .progress-bar {
    height: 6px;
    background: #eee;
    border-radius: 3px;
    overflow: hidden;
    
    .progress {
      height: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 3px;
      transition: width 0.3s;
    }
  }
}

.checkout-btn {
  display: block;
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  text-align: center;
  text-decoration: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 12px;
  
  &:hover {
    opacity: 0.9;
  }
}

.continue-shopping {
  display: block;
  text-align: center;
  color: #667eea;
  text-decoration: none;
  font-size: 14px;
  
  &:hover {
    text-decoration: underline;
  }
}

.empty-cart {
  text-align: center;
  padding: 80px 0;
  
  svg {
    color: #ddd;
    margin-bottom: 24px;
  }
  
  h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 8px;
  }
  
  p {
    color: #999;
    margin-bottom: 24px;
  }
  
  .shop-btn {
    display: inline-block;
    padding: 14px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    text-decoration: none;
    border-radius: 30px;
    font-size: 16px;
    
    &:hover {
      opacity: 0.9;
    }
  }
}

@media (max-width: 992px) {
  .cart-content {
    flex-direction: column;
  }
  
  .cart-summary {
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
  
  .cart-header {
    display: none;
  }
  
  .cart-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    
    .col-product {
      width: 100%;
      
      img {
        width: 70px;
        height: 70px;
      }
      
      .product-name {
        font-size: 14px;
      }
    }
    
    .col-price,
    .col-total {
      text-align: left;
      
      &::before {
        content: attr(data-label);
        color: #999;
        font-size: 12px;
        display: block;
        margin-bottom: 4px;
      }
    }
  }
  
  .cart-item-mobile-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;
  }
  
  .quantity-input {
    button {
      width: 36px;
      height: 36px;
    }
    
    input {
      width: 44px;
      height: 36px;
    }
  }
  
  .cart-summary {
    padding: 20px;
    
    h3 {
      font-size: 16px;
    }
  }
  
  .checkout-btn {
    padding: 14px;
    font-size: 15px;
  }
  
  .empty-cart {
    padding: 60px 20px;
    
    svg {
      width: 60px;
      height: 60px;
    }
    
    h2 {
      font-size: 20px;
    }
    
    .shop-btn {
      padding: 12px 32px;
      font-size: 15px;
    }
  }
}
</style>
