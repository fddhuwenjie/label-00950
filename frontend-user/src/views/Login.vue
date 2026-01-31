<template>
  <div class="auth-page">
    <!-- 背景装饰 -->
    <div class="bg-decoration">
      <div class="gradient-blob blob-1"></div>
      <div class="gradient-blob blob-2"></div>
    </div>
    
    <div class="auth-container">
      <div class="auth-card">
        <div class="card-header">
          <div class="logo">
            <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
              <rect width="48" height="48" rx="12" fill="url(#logo-grad)"/>
              <path d="M14 24C14 18.477 18.477 14 24 14C29.523 14 34 18.477 34 24C34 29.523 29.523 34 24 34" stroke="white" stroke-width="3" stroke-linecap="round"/>
              <circle cx="24" cy="24" r="4" fill="white"/>
              <defs>
                <linearGradient id="logo-grad" x1="0" y1="0" x2="48" y2="48">
                  <stop stop-color="#6366f1"/>
                  <stop offset="1" stop-color="#8b5cf6"/>
                </linearGradient>
              </defs>
            </svg>
          </div>
          <h1>欢迎回来</h1>
          <p>登录您的账户，继续购物之旅</p>
        </div>
        
        <form @submit.prevent="handleLogin" class="auth-form">
          <div class="form-group">
            <label class="form-label">用户名或邮箱</label>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </span>
              <input 
                type="text" 
                v-model="form.username" 
                placeholder="请输入用户名或邮箱"
                required
              />
            </div>
          </div>
          
          <div class="form-group">
            <div class="label-row">
              <label class="form-label">密码</label>
              <a href="#" class="forgot-link">忘记密码？</a>
            </div>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              </span>
              <input 
                :type="showPassword ? 'text' : 'password'" 
                v-model="form.password" 
                placeholder="请输入密码"
                required
                @keyup.enter="handleLogin"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>
          
          <div class="remember-row">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="form.remember" />
              <span class="checkbox-box">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </span>
              <span class="checkbox-label">记住我</span>
            </label>
          </div>
          
          <button type="submit" class="submit-btn" :disabled="loading">
            <span v-if="!loading">登录</span>
            <span v-else class="loading-state">
              <svg class="spinner" width="20" height="20" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="30 70"/>
              </svg>
              登录中...
            </span>
          </button>
        </form>
        
        <div class="card-footer">
          <p>还没有账户？ <router-link to="/register">立即注册</router-link></p>
        </div>
        
        <div class="test-account">
          <span class="test-badge">测试账号</span>
          <code>admin</code> / <code>admin123</code>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import toast from '@/utils/toast'

const router = useRouter()
const userStore = useUserStore()

// 检查是否已登录，已登录则跳转首页
onMounted(() => {
  if (userStore.isLoggedIn) {
    toast.info('您已登录')
    router.push('/')
  }
})

const loading = ref(false)
const showPassword = ref(false)

const form = reactive({
  username: '',
  password: '',
  remember: true
})

const handleLogin = async () => {
  if (!form.username || !form.password) return
  
  loading.value = true
  
  try {
    await new Promise(resolve => setTimeout(resolve, 800))
    
    const result = await userStore.login(form.username, form.password)
    
    if (result.success) {
      toast.success('登录成功')
      router.push('/')
    } else {
      toast.error(result.message || '用户名或密码错误')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss" scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0a0a0f;
  position: relative;
  overflow: hidden;
  padding: 40px 20px;
}

.bg-decoration {
  position: absolute;
  inset: 0;
  pointer-events: none;
  
  .gradient-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(120px);
    
    &.blob-1 {
      width: 600px;
      height: 600px;
      background: rgba(99, 102, 241, 0.2);
      top: -200px;
      right: -100px;
    }
    
    &.blob-2 {
      width: 500px;
      height: 500px;
      background: rgba(139, 92, 246, 0.15);
      bottom: -200px;
      left: -100px;
    }
  }
}

.auth-container {
  position: relative;
  width: 100%;
  max-width: 440px;
}

.auth-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  padding: 48px;
  box-shadow: 0 40px 80px rgba(0, 0, 0, 0.3);
}

.card-header {
  text-align: center;
  margin-bottom: 40px;
  
  .logo {
    margin-bottom: 24px;
    display: flex;
    justify-content: center;
  }
  
  h1 {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
  }
  
  p {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
  }
}

.auth-form {
  .form-group {
    margin-bottom: 24px;
  }
  
  .form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 10px;
  }
  
  .label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    
    .form-label {
      margin-bottom: 0;
    }
  }
  
  .forgot-link {
    font-size: 13px;
    color: #818cf8;
    text-decoration: none;
    
    &:hover {
      text-decoration: underline;
    }
  }
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  
  .input-icon {
    position: absolute;
    left: 16px;
    color: rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
  }
  
  input {
    width: 100%;
    padding: 16px 48px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    font-size: 15px;
    color: #fff;
    transition: all 0.3s ease;
    
    &::placeholder {
      color: rgba(255, 255, 255, 0.3);
    }
    
    &:focus {
      outline: none;
      border-color: #6366f1;
      background: rgba(99, 102, 241, 0.1);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
  }
  
  .toggle-password {
    position: absolute;
    right: 16px;
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    
    &:hover {
      color: rgba(255, 255, 255, 0.6);
    }
  }
}

.remember-row {
  margin-bottom: 32px;
}

.custom-checkbox {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  user-select: none;
  
  input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
  }
  
  .checkbox-box {
    width: 22px;
    height: 22px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
    
    svg {
      opacity: 0;
      color: #fff;
      transition: opacity 0.2s ease;
    }
  }
  
  input:checked + .checkbox-box {
    background: #6366f1;
    border-color: #6366f1;
    
    svg {
      opacity: 1;
    }
  }
  
  .checkbox-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
  }
}

.submit-btn {
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
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
  }
  
  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
  
  .loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    
    .spinner {
      animation: spin 1s linear infinite;
    }
  }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.card-footer {
  margin-top: 32px;
  text-align: center;
  
  p {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
    
    a {
      color: #818cf8;
      text-decoration: none;
      font-weight: 600;
      
      &:hover {
        text-decoration: underline;
      }
    }
  }
}

.test-account {
  margin-top: 24px;
  padding: 16px;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 12px;
  text-align: center;
  
  .test-badge {
    display: inline-block;
    padding: 4px 10px;
    background: rgba(99, 102, 241, 0.3);
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    color: #818cf8;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
  }
  
  code {
    padding: 4px 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    color: #c4b5fd;
    font-family: 'SF Mono', Monaco, monospace;
    font-size: 13px;
  }
}
</style>
