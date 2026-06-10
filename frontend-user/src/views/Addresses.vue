<template>
  <div class="addresses-page">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">
          我的地址
        </h1>
        <button
          class="add-btn"
          @click="showAddForm = true"
        >
          + 新增地址
        </button>
      </div>

      <div
        v-if="addresses.length === 0 && !showAddForm"
        class="empty"
      >
        <p>暂无收货地址</p>
        <button
          class="add-btn"
          @click="showAddForm = true"
        >
          添加收货地址
        </button>
      </div>

      <div
        v-if="showAddForm"
        class="address-form-card"
      >
        <h3>{{ editingIndex >= 0 ? '编辑地址' : '新增地址' }}</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>收货人 *</label>
            <input
              v-model="form.name"
              placeholder="请输入收货人姓名"
            >
          </div>
          <div class="form-group">
            <label>联系电话 *</label>
            <input
              v-model="form.phone"
              placeholder="请输入手机号"
            >
          </div>
          <div class="form-group">
            <label>国家/地区 *</label>
            <select v-model="form.country">
              <option value="CN">
                中国
              </option>
              <option value="US">
                美国
              </option>
              <option value="GB">
                英国
              </option>
              <option value="JP">
                日本
              </option>
              <option value="KR">
                韩国
              </option>
              <option value="AU">
                澳大利亚
              </option>
              <option value="CA">
                加拿大
              </option>
              <option value="DE">
                德国
              </option>
              <option value="FR">
                法国
              </option>
              <option value="SG">
                新加坡
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>城市 *</label>
            <input
              v-model="form.city"
              placeholder="请输入城市"
            >
          </div>
          <div class="form-group full-width">
            <label>详细地址 *</label>
            <input
              v-model="form.address"
              placeholder="请输入详细地址"
            >
          </div>
          <div class="form-group">
            <label>邮编</label>
            <input
              v-model="form.zipCode"
              placeholder="请输入邮编"
            >
          </div>
          <div class="form-group">
            <label>邮箱</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="请输入邮箱"
            >
          </div>
        </div>
        <label class="default-check">
          <input
            v-model="form.isDefault"
            type="checkbox"
          > 设为默认地址
        </label>
        <div class="form-actions">
          <button
            class="save-btn"
            @click="saveAddress"
          >
            保存
          </button>
          <button
            class="cancel-btn"
            @click="cancelEdit"
          >
            取消
          </button>
        </div>
      </div>

      <div class="address-list">
        <div
          v-for="(addr, idx) in addresses"
          :key="idx"
          class="address-card"
          :class="{ default: addr.isDefault }"
        >
          <div
            v-if="addr.isDefault"
            class="default-tag"
          >
            默认
          </div>
          <div class="addr-info">
            <div class="addr-name">
              {{ addr.name }} <span class="addr-phone">{{ addr.phone }}</span>
            </div>
            <div class="addr-detail">
              {{ getCountryName(addr.country) }} {{ addr.city }} {{ addr.address }}
            </div>
            <div
              v-if="addr.zipCode"
              class="addr-zip"
            >
              邮编: {{ addr.zipCode }}
            </div>
          </div>
          <div class="addr-actions">
            <button @click="editAddress(idx)">
              编辑
            </button>
            <button @click="deleteAddress(idx)">
              删除
            </button>
            <button
              v-if="!addr.isDefault"
              @click="setDefault(idx)"
            >
              设为默认
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import toast from '@/utils/toast'

const addresses = ref([])
const showAddForm = ref(false)
const editingIndex = ref(-1)
const form = ref({ name: '', phone: '', country: 'CN', city: '', address: '', zipCode: '', email: '', isDefault: false })

const STORAGE_KEY = 'user_addresses'

const countries = { CN: '中国', US: '美国', GB: '英国', JP: '日本', KR: '韩国', AU: '澳大利亚', CA: '加拿大', DE: '德国', FR: '法国', SG: '新加坡' }
const getCountryName = (code) => countries[code] || code

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved) addresses.value = JSON.parse(saved)
})

const persist = () => localStorage.setItem(STORAGE_KEY, JSON.stringify(addresses.value))

const saveAddress = () => {
  if (!form.value.name || !form.value.phone || !form.value.address || !form.value.city) {
    toast.error('请填写必填项'); return
  }
  const addr = { ...form.value }
  if (addr.isDefault) addresses.value.forEach(a => a.isDefault = false)
  if (editingIndex.value >= 0) {
    addresses.value[editingIndex.value] = addr
  } else {
    if (addresses.value.length === 0) addr.isDefault = true
    addresses.value.push(addr)
  }
  persist()
  toast.success('地址保存成功')
  cancelEdit()
}

const editAddress = (idx) => {
  editingIndex.value = idx
  form.value = { ...addresses.value[idx] }
  showAddForm.value = true
}

const deleteAddress = (idx) => {
  const wasDefault = addresses.value[idx].isDefault
  addresses.value.splice(idx, 1)
  if (wasDefault && addresses.value.length > 0) addresses.value[0].isDefault = true
  persist()
  toast.success('地址已删除')
}

const setDefault = (idx) => {
  addresses.value.forEach(a => a.isDefault = false)
  addresses.value[idx].isDefault = true
  persist()
  toast.success('已设为默认地址')
}

const cancelEdit = () => {
  showAddForm.value = false
  editingIndex.value = -1
  form.value = { name: '', phone: '', country: 'CN', city: '', address: '', zipCode: '', email: '', isDefault: false }
}
</script>

<style lang="scss" scoped>
.addresses-page { min-height: 100vh; background: #f8f9fa; padding: 40px 20px; }
.container { max-width: 800px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 700; color: #1a1a1a; }
.add-btn { padding: 10px 20px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
.empty { text-align: center; padding: 60px 0; color: #999; p { margin-bottom: 16px; } }
.address-form-card {
  background: #fff; border-radius: 16px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  h3 { font-size: 18px; font-weight: 600; margin-bottom: 20px; }
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group {
  &.full-width { grid-column: 1 / -1; }
  label { display: block; font-size: 13px; font-weight: 500; color: #555; margin-bottom: 6px; }
  input, select { width: 100%; padding: 10px 14px; border: 1.5px solid #e0e0e0; border-radius: 10px; font-size: 14px; outline: none; transition: border-color 0.2s; box-sizing: border-box;
    &:focus { border-color: #6366f1; }
  }
}
.default-check { display: flex; align-items: center; gap: 8px; margin-top: 16px; font-size: 14px; color: #555; cursor: pointer; input { width: 16px; height: 16px; } }
.form-actions { display: flex; gap: 12px; margin-top: 20px; }
.save-btn { padding: 10px 24px; background: #6366f1; color: #fff; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
.cancel-btn { padding: 10px 24px; background: #f0f0f0; color: #666; border: none; border-radius: 10px; font-weight: 500; cursor: pointer; }
.address-list { display: flex; flex-direction: column; gap: 12px; }
.address-card {
  background: #fff; border-radius: 14px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); position: relative; border: 2px solid transparent;
  &.default { border-color: #6366f1; }
}
.default-tag { position: absolute; top: 12px; right: 12px; background: #6366f1; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 6px; }
.addr-info { flex: 1; }
.addr-name { font-size: 16px; font-weight: 600; color: #1a1a1a; .addr-phone { font-weight: 400; color: #666; margin-left: 12px; } }
.addr-detail { font-size: 14px; color: #555; margin-top: 4px; }
.addr-zip { font-size: 13px; color: #999; margin-top: 2px; }
.addr-actions { display: flex; gap: 8px; button { padding: 6px 14px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; font-size: 13px; color: #555; cursor: pointer; &:hover { border-color: #6366f1; color: #6366f1; } } }
@media (max-width: 768px) {
  .form-grid { grid-template-columns: 1fr; }
  .address-card { flex-direction: column; align-items: flex-start; }
}
</style>
