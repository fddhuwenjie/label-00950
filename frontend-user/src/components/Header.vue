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
        
        <!-- 搜索框 - 桌面端 -->
        <div class="search-bar desktop-only">
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
        
        <!-- 移动端右侧操作区 -->
        <div class="mobile-actions">
          <!-- 搜索按钮 - 移动端 -->
          <button class="mobile-search-btn" @click="showMobileSearch = !showMobileSearch">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </button>
          
          <!-- 购物车 - 移动端 -->
          <router-link to="/cart" class="mobile-cart-btn">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="9" cy="21" r="1"/>
              <circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span v-if="cartStore.itemCount > 0" class="cart-badge">
              {{ cartStore.itemCount }}
            </span>
          </router-link>
          
          <!-- 汉堡菜单按钮 -->
          <button class="hamburger-btn" @click="showMobileMenu = !showMobileMenu" :class="{ active: showMobileMenu }">
            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>
        
        <!-- 导航 - 桌面端 -->
        <nav class="nav desktop-only">
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
      
      <!-- 移动端搜索框 -->
      <div class="mobile-search" :class="{ show: showMobileSearch }">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="搜索商品..."
          @keyup.enter="handleMobileSearch"
        />
        <button class="search-btn" @click="handleMobileSearch">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- 移动端侧边菜单 -->
    <div class="mobile-menu-overlay" :class="{ show: showMobileMenu }" @click="showMobileMenu = false"></div>
    <nav class="mobile-menu" :class="{ show: showMobileMenu }">
      <div class="mobile-menu-header">
        <span class="menu-title">菜单</span>
        <button class="close-btn" @click="showMobileMenu = false">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      
      <div class="mobile-menu-content">
        <router-link to="/" class="mobile-nav-link" @click="showMobileMenu = false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          首页
        </router-link>
        <router-link to="/products" class="mobile-nav-link" @click="showMobileMenu = false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          全部商品
        </router-link>
        <router-link to="/cart" class="mobile-nav-link" @click="showMobileMenu = false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="21" r="1"/>
            <circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          购物车
          <span v-if="cartStore.itemCount > 0" class="mobile-badge">{{ cartStore.itemCount }}</span>
        </router-link>
        
        <div class="mobile-menu-divider"></div>
        
        <template v-if="userStore.isLoggedIn">
          <div class="mobile-user-info">
            <div class="user-avatar">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <span>{{ userStore.userInfo.name }}</span>
          </div>
          <router-link to="/account" class="mobile-nav-link" @click="showMobileMenu = false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            我的账户
          </router-link>
          <router-link to="/orders" class="mobile-nav-link" @click="showMobileMenu = false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
            我的订单
          </router-link>
          <button class="mobile-nav-link logout-btn" @click="handleMobileLogout">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            退出登录
          </button>
        </template>
        
        <template v-else>
          <router-link to="/login" class="mobile-nav-link" @click="showMobileMenu = false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
              <polyline points="10 17 15 12 10 7"/>
              <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            登录
          </router-link>
          <router-link to="/register" class="mobile-nav-link register-link" @click="showMobileMenu = false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="8.5" cy="7" r="4"/>
              <line x1="20" y1="8" x2="20" y2="14"/>
              <line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
            注册
          </router-link>
        </template>
      </div>
    </nav>
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
const showMobileMenu = ref(false)
const showMobileSearch = ref(false)

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

const handleMobileSearch = () => {
  if (searchQuery.value.trim()) {
    router.push(`/products?search=${encodeURIComponent(searchQuery.value.trim())}`)
    searchQuery.value = ''
    showMobileSearch.value = false
  }
}

const handleMobileLogout = () => {
  userStore.logout()
  cartStore.clearCart()
  showMobileMenu.value = false
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
  flex-shrink: 0;
  
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

// 移动端操作区
.mobile-actions {
  display: none;
  align-items: center;
  gap: 8px;
}

.mobile-search-btn,
.mobile-cart-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  color: #333;
  position: relative;
  transition: all 0.2s ease;
  
  &:hover {
    background: #eee;
  }
  
  .cart-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    min-width: 16px;
    height: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

// 汉堡菜单按钮
.hamburger-btn {
  width: 44px;
  height: 44px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  background: #f5f5f5;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  padding: 12px;
  transition: all 0.3s ease;
  
  span {
    width: 20px;
    height: 2px;
    background: #333;
    border-radius: 2px;
    transition: all 0.3s ease;
  }
  
  &.active {
    background: #6366f1;
    
    span {
      background: #fff;
      
      &:nth-child(1) {
        transform: translateY(7px) rotate(45deg);
      }
      
      &:nth-child(2) {
        opacity: 0;
      }
      
      &:nth-child(3) {
        transform: translateY(-7px) rotate(-45deg);
      }
    }
  }
}

// 移动端搜索框
.mobile-search {
  display: none;
  padding: 0 0 16px;
  
  &.show {
    display: flex;
  }
  
  input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #e8e8e8;
    border-right: none;
    border-radius: 12px 0 0 12px;
    font-size: 15px;
    outline: none;
    
    &:focus {
      border-color: #6366f1;
    }
  }
  
  .search-btn {
    padding: 12px 18px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    border: none;
    border-radius: 0 12px 12px 0;
    cursor: pointer;
  }
}

// 移动端遮罩层
.mobile-menu-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
  opacity: 0;
  transition: opacity 0.3s ease;
  
  &.show {
    display: block;
    opacity: 1;
  }
}

// 移动端侧边菜单
.mobile-menu {
  display: none;
  position: fixed;
  top: 0;
  right: -300px;
  width: 300px;
  height: 100vh;
  background: #fff;
  z-index: 999;
  transition: right 0.3s ease;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
  
  &.show {
    right: 0;
  }
}

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  border-bottom: 1px solid #f0f0f0;
  
  .menu-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
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
    
    &:hover {
      background: #eee;
    }
  }
}

.mobile-menu-content {
  padding: 16px;
  overflow-y: auto;
  height: calc(100vh - 80px);
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  color: #333;
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  border-radius: 12px;
  margin-bottom: 4px;
  transition: all 0.2s ease;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  
  svg {
    color: #666;
    flex-shrink: 0;
  }
  
  &:hover {
    background: #f8f8fc;
    color: #6366f1;
    
    svg {
      color: #6366f1;
    }
  }
  
  &.register-link {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    margin-top: 8px;
    
    svg {
      color: #fff;
    }
    
    &:hover {
      opacity: 0.9;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }
  }
  
  &.logout-btn {
    color: #ef4444;
    
    svg {
      color: #ef4444;
    }
    
    &:hover {
      background: #fef2f2;
    }
  }
  
  .mobile-badge {
    margin-left: auto;
    padding: 4px 10px;
    background: #ef4444;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
  }
}

.mobile-menu-divider {
  height: 1px;
  background: #f0f0f0;
  margin: 12px 0;
}

.mobile-user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #f8f8fc;
  border-radius: 12px;
  margin-bottom: 12px;
  
  .user-avatar {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 12px;
    color: #fff;
  }
  
  span {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
  }
}

// 响应式
@media (max-width: 768px) {
  .header-content {
    height: 60px;
    gap: 12px;
  }
  
  .logo {
    svg {
      width: 32px;
      height: 32px;
    }
    
    span {
      font-size: 18px;
    }
  }
  
  .desktop-only {
    display: none !important;
  }
  
  .mobile-actions {
    display: flex;
  }
  
  .mobile-menu {
    display: block;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 16px;
  }
  
  .logo span {
    display: none;
  }
  
  .mobile-menu {
    width: 100%;
    right: -100%;
  }
}
</style>
