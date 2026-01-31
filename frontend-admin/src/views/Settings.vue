<template>
  <div class="settings-page">
    <el-row :gutter="20">
      <el-col :span="6">
        <el-card class="menu-card">
          <el-menu
            :default-active="activeMenu"
            @select="handleMenuSelect"
          >
            <el-menu-item index="general">
              <el-icon><Setting /></el-icon>
              <span>基本设置</span>
            </el-menu-item>
            <el-menu-item index="payment">
              <el-icon><CreditCard /></el-icon>
              <span>支付设置</span>
            </el-menu-item>
            <el-menu-item index="shipping">
              <el-icon><Van /></el-icon>
              <span>物流设置</span>
            </el-menu-item>
            <el-menu-item index="currency">
              <el-icon><Money /></el-icon>
              <span>货币设置</span>
            </el-menu-item>
            <el-menu-item index="notification">
              <el-icon><Bell /></el-icon>
              <span>通知设置</span>
            </el-menu-item>
          </el-menu>
        </el-card>
      </el-col>
      
      <el-col :span="18">
        <!-- 基本设置 -->
        <el-card v-show="activeMenu === 'general'">
          <template #header>
            <span>基本设置</span>
          </template>
          <el-form :model="generalSettings" label-width="120px">
            <el-form-item label="商城名称">
              <el-input v-model="generalSettings.siteName" />
            </el-form-item>
            <el-form-item label="商城描述">
              <el-input v-model="generalSettings.siteDescription" type="textarea" :rows="3" />
            </el-form-item>
            <el-form-item label="联系邮箱">
              <el-input v-model="generalSettings.contactEmail" />
            </el-form-item>
            <el-form-item label="联系电话">
              <el-input v-model="generalSettings.contactPhone" />
            </el-form-item>
            <el-form-item label="公司地址">
              <el-input v-model="generalSettings.address" type="textarea" :rows="2" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="saveSettings('general')">保存设置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
        
        <!-- 支付设置 -->
        <el-card v-show="activeMenu === 'payment'">
          <template #header>
            <span>支付设置</span>
          </template>
          <el-form :model="paymentSettings" label-width="120px">
            <el-divider content-position="left">PayPal</el-divider>
            <el-form-item label="启用 PayPal">
              <el-switch v-model="paymentSettings.paypalEnabled" />
            </el-form-item>
            <el-form-item label="Client ID" v-show="paymentSettings.paypalEnabled">
              <el-input v-model="paymentSettings.paypalClientId" />
            </el-form-item>
            <el-form-item label="Client Secret" v-show="paymentSettings.paypalEnabled">
              <el-input v-model="paymentSettings.paypalSecret" type="password" show-password />
            </el-form-item>
            
            <el-divider content-position="left">Stripe</el-divider>
            <el-form-item label="启用 Stripe">
              <el-switch v-model="paymentSettings.stripeEnabled" />
            </el-form-item>
            <el-form-item label="Publishable Key" v-show="paymentSettings.stripeEnabled">
              <el-input v-model="paymentSettings.stripePublishKey" />
            </el-form-item>
            <el-form-item label="Secret Key" v-show="paymentSettings.stripeEnabled">
              <el-input v-model="paymentSettings.stripeSecretKey" type="password" show-password />
            </el-form-item>
            
            <el-form-item>
              <el-button type="primary" @click="saveSettings('payment')">保存设置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
        
        <!-- 物流设置 -->
        <el-card v-show="activeMenu === 'shipping'">
          <template #header>
            <span>物流设置</span>
          </template>
          <el-form :model="shippingSettings" label-width="120px">
            <el-form-item label="免运费门槛">
              <el-input-number v-model="shippingSettings.freeShippingThreshold" :min="0" :precision="2" />
              <span style="margin-left: 10px;">USD</span>
            </el-form-item>
            <el-form-item label="默认运费">
              <el-input-number v-model="shippingSettings.defaultShippingFee" :min="0" :precision="2" />
              <span style="margin-left: 10px;">USD</span>
            </el-form-item>
            <el-form-item label="发货地址">
              <el-input v-model="shippingSettings.warehouseAddress" type="textarea" :rows="2" />
            </el-form-item>
            <el-form-item label="预计配送时间">
              <el-input v-model="shippingSettings.estimatedDelivery" placeholder="例如: 7-14 个工作日" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="saveSettings('shipping')">保存设置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
        
        <!-- 货币设置 -->
        <el-card v-show="activeMenu === 'currency'">
          <template #header>
            <span>货币设置</span>
          </template>
          <el-form :model="currencySettings" label-width="120px">
            <el-form-item label="默认货币">
              <el-select v-model="currencySettings.defaultCurrency">
                <el-option label="美元 (USD)" value="USD" />
                <el-option label="人民币 (CNY)" value="CNY" />
                <el-option label="欧元 (EUR)" value="EUR" />
                <el-option label="英镑 (GBP)" value="GBP" />
                <el-option label="日元 (JPY)" value="JPY" />
              </el-select>
            </el-form-item>
            <el-form-item label="支持的货币">
              <el-checkbox-group v-model="currencySettings.supportedCurrencies">
                <el-checkbox label="USD">美元</el-checkbox>
                <el-checkbox label="CNY">人民币</el-checkbox>
                <el-checkbox label="EUR">欧元</el-checkbox>
                <el-checkbox label="GBP">英镑</el-checkbox>
                <el-checkbox label="JPY">日元</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="自动汇率更新">
              <el-switch v-model="currencySettings.autoUpdateRates" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="saveSettings('currency')">保存设置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
        
        <!-- 通知设置 -->
        <el-card v-show="activeMenu === 'notification'">
          <template #header>
            <span>通知设置</span>
          </template>
          <el-form :model="notificationSettings" label-width="150px">
            <el-form-item label="新订单通知">
              <el-switch v-model="notificationSettings.newOrder" />
            </el-form-item>
            <el-form-item label="订单状态变更通知">
              <el-switch v-model="notificationSettings.orderStatus" />
            </el-form-item>
            <el-form-item label="库存不足提醒">
              <el-switch v-model="notificationSettings.lowStock" />
            </el-form-item>
            <el-form-item label="库存阈值">
              <el-input-number v-model="notificationSettings.stockThreshold" :min="1" />
            </el-form-item>
            <el-form-item label="新用户注册通知">
              <el-switch v-model="notificationSettings.newUser" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="saveSettings('notification')">保存设置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'

const activeMenu = ref('general')

// 使用 cookie 存储设置（可跨端口共享）
const setCookie = (name, value, days = 365) => {
  const expires = new Date()
  expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000)
  document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires.toUTCString()};path=/`
}

const getCookie = (name) => {
  const nameEQ = name + '='
  const ca = document.cookie.split(';')
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i]
    while (c.charAt(0) === ' ') c = c.substring(1, c.length)
    if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length))
  }
  return null
}

// 从 cookie 加载设置
const loadSettings = () => {
  const saved = getCookie('siteSettings')
  if (saved) {
    try {
      return JSON.parse(saved)
    } catch (e) {
      return null
    }
  }
  return null
}

const savedSettings = loadSettings()

const generalSettings = reactive(savedSettings?.general || {
  siteName: '跨境电商商城',
  siteDescription: '全球精选商品，品质保证，快速配送',
  contactEmail: 'support@example.com',
  contactPhone: '+1-800-123-4567',
  address: '123 Commerce St, New York, NY 10001, USA'
})

const paymentSettings = reactive(savedSettings?.payment || {
  paypalEnabled: true,
  paypalClientId: '',
  paypalSecret: '',
  stripeEnabled: false,
  stripePublishKey: '',
  stripeSecretKey: ''
})

const shippingSettings = reactive(savedSettings?.shipping || {
  freeShippingThreshold: 99,
  defaultShippingFee: 9.99,
  warehouseAddress: 'Warehouse A, Logistics Center',
  estimatedDelivery: '7-14 个工作日'
})

const currencySettings = reactive(savedSettings?.currency || {
  defaultCurrency: 'USD',
  supportedCurrencies: ['USD', 'CNY', 'EUR'],
  autoUpdateRates: true
})

const notificationSettings = reactive(savedSettings?.notification || {
  newOrder: true,
  orderStatus: true,
  lowStock: true,
  stockThreshold: 10,
  newUser: false
})

const handleMenuSelect = (index) => {
  activeMenu.value = index
}

const saveSettings = (type) => {
  // 保存所有设置到 cookie（可跨端口共享）
  const allSettings = {
    general: { ...generalSettings },
    payment: { ...paymentSettings },
    shipping: { ...shippingSettings },
    currency: { ...currencySettings },
    notification: { ...notificationSettings }
  }
  setCookie('siteSettings', JSON.stringify(allSettings))
  ElMessage.success(`${getSettingsName(type)} 已保存`)
}

const getSettingsName = (type) => {
  const names = {
    general: '基本设置',
    payment: '支付设置',
    shipping: '物流设置',
    currency: '货币设置',
    notification: '通知设置'
  }
  return names[type] || '设置'
}
</script>

<style lang="scss" scoped>
.settings-page {
  .menu-card {
    :deep(.el-card__body) {
      padding: 0;
    }
    
    .el-menu {
      border-right: none;
    }
  }
}
</style>
