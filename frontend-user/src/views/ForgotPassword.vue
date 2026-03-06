<template>
  <div class="auth-page">
    <div class="bg-decoration">
      <div class="gradient-blob blob-1"></div>
      <div class="gradient-blob blob-2"></div>
    </div>
    <div class="auth-container">
      <div class="auth-card">
        <div class="card-header">
          <h1>找回密码</h1>
          <p>输入您的注册邮箱，我们将发送密码重置链接</p>
        </div>
        <form @submit.prevent="handleSubmit" class="auth-form" v-if="!submitted">
          <div class="form-group">
            <label class="form-label">注册邮箱</label>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
              </span>
              <input type="email" v-model="email" placeholder="请输入注册邮箱" required />
            </div>
          </div>
          <button type="submit" class="submit-btn" :disabled="loading">
            {{ loading ? '发送中...' : '发送重置链接' }}
          </button>
        </form>
        <div v-else class="success-msg">
          <div class="success-icon">✓</div>
          <h2>邮件已发送</h2>
          <p>密码重置链接已发送至 <strong>{{ email }}</strong>，请查收邮件并按照提示操作。</p>
          <p class="hint">如未收到邮件，请检查垃圾邮件文件夹。</p>
        </div>
        <div class="form-footer">
          <router-link to="/login">返回登录</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import toast from '@/utils/toast'

const email = ref('')
const loading = ref(false)
const submitted = ref(false)

const handleSubmit = async () => {
  if (!email.value) { toast.error('请输入邮箱'); return }
  loading.value = true
  try {
    const resp = await fetch('/wp-json/cbc/v1/auth/forgot-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value })
    })
    const data = await resp.json()
    if (!resp.ok) throw new Error(data.message || '发送失败')
    submitted.value = true
    toast.success('重置链接已发送到您的邮箱')
  } catch (e) {
    // 即使失败也显示成功（安全考虑，不暴露邮箱是否存在）
    submitted.value = true
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
  background: #f8f9fa;
  position: relative;
  overflow: hidden;
}
.bg-decoration { position: absolute; inset: 0; pointer-events: none; }
.gradient-blob {
  position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.15;
  &.blob-1 { width: 400px; height: 400px; background: #6366f1; top: -100px; right: -100px; }
  &.blob-2 { width: 300px; height: 300px; background: #8b5cf6; bottom: -50px; left: -50px; }
}
.auth-container { position: relative; z-index: 1; width: 100%; max-width: 440px; padding: 20px; }
.auth-card {
  background: #fff; border-radius: 24px; padding: 40px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.08);
}
.card-header {
  text-align: center; margin-bottom: 32px;
  h1 { font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
  p { font-size: 14px; color: #666; }
}
.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 8px; }
.input-wrapper {
  display: flex; align-items: center; border: 1.5px solid #e0e0e0; border-radius: 12px;
  padding: 0 16px; transition: border-color 0.2s;
  &:focus-within { border-color: #6366f1; }
  input { flex: 1; border: none; outline: none; padding: 14px 0 14px 12px; font-size: 15px; background: transparent; }
}
.input-icon { color: #999; display: flex; }
.submit-btn {
  width: 100%; padding: 14px; background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border: none; border-radius: 12px; color: #fff; font-size: 16px; font-weight: 600;
  cursor: pointer; transition: all 0.3s;
  &:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,0.3); }
  &:disabled { opacity: 0.5; cursor: not-allowed; }
}
.success-msg {
  text-align: center; padding: 20px 0;
  .success-icon { width: 64px; height: 64px; background: #10b981; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px; }
  h2 { font-size: 20px; color: #1a1a1a; margin-bottom: 12px; }
  p { font-size: 14px; color: #666; line-height: 1.6; }
  .hint { margin-top: 12px; font-size: 13px; color: #999; }
}
.form-footer { text-align: center; margin-top: 24px; a { color: #6366f1; text-decoration: none; font-size: 14px; &:hover { text-decoration: underline; } } }
</style>
