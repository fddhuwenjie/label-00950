<template>
  <div class="account-page">
    <div class="container">
      <h1 class="page-title">我的账户</h1>
      
      <div class="account-content">
        <aside class="account-sidebar">
          <div class="user-info">
            <div class="avatar">
              {{ userStore.userInfo.name?.charAt(0) || 'U' }}
            </div>
            <div class="user-details">
              <h3>{{ userStore.userInfo.name || '用户' }}</h3>
              <p>{{ userStore.userInfo.email }}</p>
            </div>
          </div>
          
          <nav class="account-nav">
            <a 
              v-for="item in navItems" 
              :key="item.id"
              :class="{ active: activeSection === item.id }"
              @click="activeSection = item.id"
            >
              {{ item.label }}
            </a>
          </nav>
        </aside>
        
        <main class="account-main">
          <!-- 个人信息 -->
          <section v-show="activeSection === 'profile'" class="account-section">
            <h2>个人信息</h2>
            <form @submit.prevent="saveProfile">
              <div class="form-row">
                <div class="form-group" :class="{ error: profileErrors.username }">
                  <label>用户名 *</label>
                  <input 
                    type="text" 
                    v-model="profile.username"
                    @blur="validateProfileField('username')"
                  />
                  <span v-if="profileErrors.username" class="error-msg">{{ profileErrors.username }}</span>
                </div>
                <div class="form-group">
                  <label>昵称</label>
                  <input type="text" v-model="profile.nickname" />
                </div>
              </div>
              <div class="form-group" :class="{ error: profileErrors.email }">
                <label>电子邮箱 *</label>
                <input 
                  type="email" 
                  v-model="profile.email"
                  @blur="validateProfileField('email')"
                />
                <span v-if="profileErrors.email" class="error-msg">{{ profileErrors.email }}</span>
              </div>
              <div class="form-group" :class="{ error: profileErrors.phone }">
                <label>手机号码</label>
                <input 
                  type="tel" 
                  v-model="profile.phone"
                  placeholder="请输入手机号码"
                  @blur="validateProfileField('phone')"
                />
                <span v-if="profileErrors.phone" class="error-msg">{{ profileErrors.phone }}</span>
              </div>
              <button type="submit" class="save-btn" :disabled="profileSaving">
                {{ profileSaving ? '保存中...' : '保存修改' }}
              </button>
            </form>
          </section>
          
          <!-- 修改密码 -->
          <section v-show="activeSection === 'password'" class="account-section">
            <h2>修改密码</h2>
            <form @submit.prevent="changePassword">
              <div class="form-group" :class="{ error: passwordErrors.current }">
                <label>当前密码 *</label>
                <input 
                  type="password" 
                  v-model="password.current"
                  @blur="validatePasswordField('current')"
                />
                <span v-if="passwordErrors.current" class="error-msg">{{ passwordErrors.current }}</span>
              </div>
              <div class="form-group" :class="{ error: passwordErrors.new }">
                <label>新密码 *</label>
                <input 
                  type="password" 
                  v-model="password.new"
                  @blur="validatePasswordField('new')"
                />
                <span v-if="passwordErrors.new" class="error-msg">{{ passwordErrors.new }}</span>
              </div>
              <div class="form-group" :class="{ error: passwordErrors.confirm }">
                <label>确认新密码 *</label>
                <input 
                  type="password" 
                  v-model="password.confirm"
                  @blur="validatePasswordField('confirm')"
                />
                <span v-if="passwordErrors.confirm" class="error-msg">{{ passwordErrors.confirm }}</span>
              </div>
              <button type="submit" class="save-btn" :disabled="passwordSaving">
                {{ passwordSaving ? '更新中...' : '更新密码' }}
              </button>
            </form>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import toast from '@/utils/toast'
import { authApi } from '@/utils/api'

const router = useRouter()
const userStore = useUserStore()
const activeSection = ref('profile')
const profileSaving = ref(false)
const passwordSaving = ref(false)

const navItems = [
  { id: 'profile', label: '个人信息' },
  { id: 'password', label: '修改密码' },
]

const profile = reactive({
  username: '',
  nickname: '',
  email: '',
  phone: ''
})

const profileErrors = reactive({ username: '', email: '', phone: '' })
const password = reactive({ current: '', new: '', confirm: '' })
const passwordErrors = reactive({ current: '', new: '', confirm: '' })

onMounted(() => {
  // 从 userStore 加载个人信息
  profile.username = userStore.userInfo.name || ''
  profile.email = userStore.userInfo.email || ''
  // 从 localStorage 加载额外信息
  const saved = localStorage.getItem('user_profile')
  if (saved) {
    const data = JSON.parse(saved)
    profile.nickname = data.nickname || ''
    profile.phone = data.phone || ''
  }
})

const validateProfileField = (field) => {
  switch (field) {
    case 'username':
      profileErrors.username = !profile.username.trim() ? '请输入用户名' : profile.username.length < 2 ? '用户名至少2个字符' : ''
      break
    case 'email':
      profileErrors.email = !profile.email.trim() ? '请输入电子邮箱' : !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profile.email) ? '请输入有效的邮箱地址' : ''
      break
    case 'phone':
      if (profile.phone && profile.phone.trim()) {
        const p = profile.phone.replace(/[\s\-()]/g, '')
        profileErrors.phone = (/^1[3-9]\d{9}$/.test(p) || /^\+?\d{8,15}$/.test(p)) ? '' : '请输入有效的手机号码'
      } else { profileErrors.phone = '' }
      break
  }
  return !profileErrors[field]
}

const validatePasswordField = (field) => {
  switch (field) {
    case 'current': passwordErrors.current = !password.current ? '请输入当前密码' : ''; break
    case 'new': passwordErrors.new = !password.new ? '请输入新密码' : password.new.length < 6 ? '密码至少6个字符' : ''; break
    case 'confirm': passwordErrors.confirm = !password.confirm ? '请确认新密码' : password.new !== password.confirm ? '两次输入的密码不一致' : ''; break
  }
  return !passwordErrors[field]
}

const saveProfile = async () => {
  if (!validateProfileField('username') || !validateProfileField('email')) {
    toast.error('请完整填写个人信息'); return
  }
  profileSaving.value = true
  try {
    // 持久化到 localStorage
    localStorage.setItem('user_profile', JSON.stringify({
      nickname: profile.nickname,
      phone: profile.phone,
    }))
    // 更新 store
    userStore.userInfo.name = profile.username
    userStore.userInfo.email = profile.email
    localStorage.setItem('userInfo', JSON.stringify(userStore.userInfo))
    toast.success('个人信息已更新')
  } catch (error) {
    toast.error('保存失败，请重试')
  } finally {
    profileSaving.value = false
  }
}

const changePassword = async () => {
  const fields = ['current', 'new', 'confirm']
  let isValid = true
  fields.forEach(f => { if (!validatePasswordField(f)) isValid = false })
  if (!isValid) return

  passwordSaving.value = true
  try {
    const token = localStorage.getItem('token')
    const resp = await fetch('/wp-json/cbc/v1/auth/change-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
      body: JSON.stringify({ current_password: password.current, new_password: password.new })
    })
    const data = await resp.json()
    if (!resp.ok) throw new Error(data.message || '密码更新失败')
    toast.success('密码已更新')
    password.current = ''; password.new = ''; password.confirm = ''
  } catch (error) {
    toast.error(error.message || '密码更新失败')
  } finally {
    passwordSaving.value = false
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

.account-content {
  display: flex;
  gap: 30px;
}

.account-sidebar {
  width: 280px;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  margin-bottom: 20px;
  
  .avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    font-weight: bold;
  }
  
  .user-details {
    h3 {
      font-size: 18px;
      color: #333;
      margin-bottom: 4px;
    }
    
    p {
      font-size: 14px;
      color: #999;
      margin: 0;
    }
  }
}

.account-nav {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  
  a {
    display: block;
    padding: 16px 24px;
    color: #666;
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all 0.2s;
    
    &:hover {
      background: #f8f9fa;
    }
    
    &.active {
      background: #f8f8ff;
      color: #6366f1;
      border-left-color: #6366f1;
    }
  }
}

.account-main {
  flex: 1;
}

.account-section {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  padding: 30px;
  
  h2 {
    font-size: 20px;
    color: #333;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #eee;
  }
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  margin-bottom: 20px;
  
  label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
  }
  
  input {
    width: 100%;
    padding: 12px 16px;
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
    input {
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

.save-btn {
  padding: 12px 32px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &:hover:not(:disabled) {
    opacity: 0.9;
  }
  
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}

@media (max-width: 768px) {
  .account-content {
    flex-direction: column;
  }
  
  .account-sidebar {
    width: 100%;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
