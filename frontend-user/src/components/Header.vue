<template>
  <header class="header">
    <div class="container">
      <div class="header-content">
        <!-- Logo -->
        <router-link to="/" class="logo">
          <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
            <rect width="48" height="48" rx="10" fill="url(#header-logo-grad)"/>
            <path d="M14 24C14 18.477 18.477 14 24 14C29.523 14 34 18.477 34 24C34 29.523 29.523 34 24 34" stroke="white" stroke-width="3" stroke-linecap="round"/>
            <circle cx="24" cy="24" r="4" fill="white"/>
            <defs>
              <linearGradient id="header-logo-grad" x1="0" y1="0" x2="48" y2="48">
                <stop stop-color="#6366f1"/>
                <stop offset="1" stop-color="#8b5cf6"/>
              </linearGradient>
            </defs>
          </svg>
          <span>{{ siteName }}</span>
        </router-link>
        
        <!-- 搜索框 -->
        <div class="search-bar">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="搜索商品..."
            @keyup.enter="handleSearch"
          />
          <button class="search-btn" @click="handleSearch">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </button>
        </div>
        
        <!-- 导航 -->
        <nav class="nav">
          <router-link to="/products" class="nav-link">全部商品</router-link>
          
          <!-- 购物车 -->
          <router-link to="/cart" class="nav-link cart-link">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="9" cy="21" r="1"/>
              <circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span v-if="cartStore.itemCount > 0" class="cart-badge">
              {{ cartStore.itemCount }}
            </span>
          </router-link>
          
          <!-- 用户菜单 -->
          <div v-if="userStore.isLoggedIn" class="user-menu">
            <button class="user-btn" @click="showDropdown = !showDropdown">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <span>{{ userStore.userInfo.name }}</span>
            </button>
            <div v-show="showDropdown" class="dropdown">
              <router-link to="/account" class="dropdown-item" @click="showDropdown = false">
                我的账户
              </router-link>
              <router-link to="/orders" class="dropdown-item" @click="showDropdown = false">
                我的订单
              </router-link>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item" @click="handleLogout">退出登录</button>
            </div>
          </div>
          
          <template v-else>
            <router-link to="/login" class="nav-link">登录</router-link>
            <router-link to="/register" class="nav-link btn-primary">注册</router-link>
          </template>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { useCartStore } from '@/stores/cart'
import toast from '@/utils/toast'
import { getGeneralSettings } from '@/utils/settings'

const router = useRouter()
const userStore = useUserStore()
const cartStore = useCartStore()

const searchQuery = ref('')
const showDropdown = ref(false)

// 从设置获取商城名称
const siteName = computed(() => {
  const settings = getGeneralSettings()
  return settings.siteName || '跨境电商'
})

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push(`/products?search=${encodeURIComponent(searchQuery.value.trim())}`)
    searchQuery.value = ''
  }
}

const handleLogout = () => {
  userStore.logout()
  cartStore.clearCart() // 退出登录时清空购物车
  showDropdown.value = false
  toast.success('已退出登录')
  router.push('/')
}
</script>

<style lang="scss" scoped>
.header {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 100;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 70px;
  gap: 30px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  
  span {
    font-size: 20px;
    font-weight: bold;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
}

.search-bar {
  display: flex;
  width: 280px;
  
  input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #e8e8e8;
    border-right: none;
    border-radius: 6px 0 0 6px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s ease;
    
    &:focus {
      border-color: #6366f1;
    }
    
    &::placeholder {
      color: #aaa;
    }
  }
  
  .search-btn {
    padding: 8px 12px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    border: none;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
    transition: opacity 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    
    svg {
      width: 16px;
      height: 16px;
    }
    
    &:hover {
      opacity: 0.9;
    }
  }
}

.nav {
  display: flex;
  align-items: center;
  gap: 20px;
}

.nav-link {
  color: #333;
  text-decoration: none;
  font-size: 14px;
  padding: 8px 14px;
  border-radius: 8px;
  transition: all 0.2s;
  font-weight: 500;
  
  &:hover {
    color: #6366f1;
    background: #f5f5f5;
  }
  
  &.btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    
    &:hover {
      opacity: 0.9;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }
  }
}

.cart-link {
  position: relative;
  
  .cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: #f5f5f5;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
  
  span {
    font-size: 14px;
    color: #333;
    font-weight: 500;
  }
  
  &:hover {
    background: #eee;
  }
}

.dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  min-width: 160px;
  overflow: hidden;
  border: 1px solid #f0f0f0;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 12px 16px;
  text-align: left;
  background: none;
  border: none;
  color: #333;
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &:hover {
    background: #f5f5f5;
    color: #6366f1;
  }
}

.dropdown-divider {
  height: 1px;
  background: #f0f0f0;
}
</style>
