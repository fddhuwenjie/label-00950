<template>
  <div class="login-page">
    <!-- 背景装饰 -->
    <div class="bg-decoration">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="grid-pattern"></div>
    </div>
    
    <div class="login-container">
      <!-- 左侧品牌区域 -->
      <div class="brand-section">
        <div class="brand-content">
          <div class="logo-mark">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <rect width="48" height="48" rx="12" fill="url(#logo-gradient)"/>
              <path d="M14 24C14 18.477 18.477 14 24 14C29.523 14 34 18.477 34 24C34 29.523 29.523 34 24 34" stroke="white" stroke-width="3" stroke-linecap="round"/>
              <circle cx="24" cy="24" r="4" fill="white"/>
              <defs>
                <linearGradient id="logo-gradient" x1="0" y1="0" x2="48" y2="48">
                  <stop stop-color="#6366f1"/>
                  <stop offset="1" stop-color="#8b5cf6"/>
                </linearGradient>
              </defs>
            </svg>
          </div>
          <h1>Cross-Border<br/>Commerce</h1>
          <p>跨境电商管理后台</p>
          
          <div class="feature-list">
            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                  <path d="M9 12l2 2 4-4"/>
                </svg>
              </div>
              <span>企业级安全保障</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="18" height="18" rx="2"/>
                  <path d="M3 9h18M9 21V9"/>
                </svg>
              </div>
              <span>实时数据分析</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
              </div>
              <span>全球业务管理</span>
            </div>
          </div>
        </div>
        
        <div class="brand-footer">
          <p>© 2024 跨境电商. All rights reserved.</p>
        </div>
      </div>
      
      <!-- 右侧登录表单 -->
      <div class="form-section">
        <div class="form-wrapper">
          <div class="form-header">
            <h2>欢迎回来</h2>
            <p>请登录您的管理账户</p>
          </div>
          
          <el-form 
            ref="formRef"
            :model="formData"
            :rules="rules"
            @submit.prevent="handleLogin"
            class="login-form"
          >
            <el-form-item prop="username">
              <div class="input-wrapper">
                <label>用户名</label>
                <el-input
                  v-model="formData.username"
                  placeholder="请输入用户名"
                  size="large"
                >
                  <template #prefix>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </svg>
                  </template>
                </el-input>
              </div>
            </el-form-item>
            
            <el-form-item prop="password">
              <div class="input-wrapper">
                <label>密码</label>
                <el-input
                  v-model="formData.password"
                  type="password"
                  placeholder="请输入密码"
                  size="large"
                  show-password
                  @keyup.enter="handleLogin"
                >
                  <template #prefix>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </template>
                </el-input>
              </div>
            </el-form-item>
            
            <div class="form-options">
              <el-checkbox v-model="formData.remember">保持登录状态</el-checkbox>
            </div>
            
            <el-button
              type="primary"
              size="large"
              :loading="loading"
              class="login-btn"
              @click="handleLogin"
            >
              <span v-if="!loading">登录</span>
              <span v-else>登录中...</span>
            </el-button>
          </el-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessage } from 'element-plus'

const router = useRouter()
const userStore = useUserStore()

const formRef = ref(null)
const loading = ref(false)

const formData = reactive({
  username: '',
  password: '',
  remember: true
})

const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' }
  ]
}

const handleLogin = async () => {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return
  
  loading.value = true
  
  try {
    // 模拟登录验证
    await new Promise(resolve => setTimeout(resolve, 800))
    
    // 验证账号（实际应调用后端 API 验证）
    // 此处为演示，接受 admin 用户名配合任意非空密码
    if (formData.username === 'admin' && formData.password) {
      // 保存登录状态
      const token = 'mock_token_' + Date.now()
      const userInfo = {
        name: 'Admin',
        email: 'admin@example.com',
        role: 'administrator'
      }
      
      localStorage.setItem('token', token)
      localStorage.setItem('userInfo', JSON.stringify(userInfo))
      
      // 更新 store
      userStore.token = token
      userStore.userInfo = userInfo
      
      ElMessage.success('登录成功')
      router.push('/')
    } else {
      ElMessage.error('用户名或密码错误')
    }
  } catch (error) {
    ElMessage.error('登录失败，请稍后重试')
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss" scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0a0a0f;
  position: relative;
  overflow: hidden;
}

// 背景装饰
.bg-decoration {
  position: absolute;
  inset: 0;
  pointer-events: none;
  
  .gradient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    
    &.orb-1 {
      width: 600px;
      height: 600px;
      background: rgba(99, 102, 241, 0.15);
      top: -200px;
      right: -100px;
    }
    
    &.orb-2 {
      width: 500px;
      height: 500px;
      background: rgba(139, 92, 246, 0.1);
      bottom: -200px;
      left: -100px;
    }
    
    &.orb-3 {
      width: 400px;
      height: 400px;
      background: rgba(236, 72, 153, 0.08);
      top: 50%;
      left: 30%;
    }
  }
  
  .grid-pattern {
    position: absolute;
    inset: 0;
    background-image: 
      linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
    background-size: 60px 60px;
  }
}

.login-container {
  position: relative;
  display: flex;
  width: 1000px;
  min-height: 640px;
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border-radius: 32px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 40px 80px rgba(0, 0, 0, 0.4);
  overflow: hidden;
}

// 左侧品牌区域
.brand-section {
  flex: 1;
  padding: 60px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.brand-content {
  .logo-mark {
    margin-bottom: 32px;
  }
  
  h1 {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 12px;
    letter-spacing: -1px;
  }
  
  p {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 48px;
  }
}

.feature-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 16px;
  
  .feature-icon {
    width: 44px;
    height: 44px;
    background: rgba(99, 102, 241, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #818cf8;
  }
  
  span {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.7);
  }
}

.brand-footer {
  p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.3);
    margin: 0;
  }
}

// 右侧表单区域
.form-section {
  flex: 1;
  padding: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.02);
}

.form-wrapper {
  width: 100%;
  max-width: 360px;
}

.form-header {
  margin-bottom: 40px;
  
  h2 {
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

.login-form {
  :deep(.el-form-item) {
    margin-bottom: 24px;
  }
  
  :deep(.el-form-item__error) {
    padding-top: 6px;
  }
}

.input-wrapper {
  width: 100%;
  
  label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 10px;
  }
  
  :deep(.el-input__wrapper) {
    padding: 4px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: none;
    transition: all 0.3s ease;
    
    &:hover {
      border-color: rgba(99, 102, 241, 0.5);
    }
    
    &.is-focus {
      border-color: #6366f1;
      background: rgba(99, 102, 241, 0.1);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
  }
  
  :deep(.el-input__inner) {
    color: #fff;
    height: 48px;
    
    &::placeholder {
      color: rgba(255, 255, 255, 0.3);
    }
  }
  
  :deep(.el-input__prefix) {
    color: rgba(255, 255, 255, 0.4);
  }
  
  :deep(.el-input__suffix) {
    color: rgba(255, 255, 255, 0.4);
  }
}

.form-options {
  margin-bottom: 32px;
  
  :deep(.el-checkbox__label) {
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
  }
  
  :deep(.el-checkbox__inner) {
    background: transparent;
    border-color: rgba(255, 255, 255, 0.3);
  }
  
  :deep(.el-checkbox__input.is-checked .el-checkbox__inner) {
    background: #6366f1;
    border-color: #6366f1;
  }
}

.login-btn {
  width: 100%;
  height: 52px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
  transition: all 0.3s ease;
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
  }
  
  &:active {
    transform: translateY(0);
  }
}

// 响应式
@media (max-width: 1024px) {
  .login-container {
    width: 95%;
    max-width: 480px;
    flex-direction: column;
  }
  
  .brand-section {
    display: none;
  }
  
  .form-section {
    padding: 48px 32px;
  }
}
</style>
