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
import { settingsApi } from '@/utils/api'

const activeMenu = ref('general')
const loadingSettings = ref(false)

const generalSettings = reactive({
  siteName: '跨境电商商城',
  siteDescription: '全球精选商品，品质保证，快速配送',
  contactEmail: 'support@example.com',
  contactPhone: '+86 400-888-8888',
  address: ''
})

const paymentSettings = reactive({
  paypalEnabled: true,
  paypalClientId: '',
  paypalSecret: '',
  stripeEnabled: false,
  stripePublishKey: '',
  stripeSecretKey: ''
})

const shippingSettings = reactive({
  freeShippingThreshold: 99,
  defaultShippingFee: 15,
  warehouseAddress: 'Warehouse A, Logistics Center',
  estimatedDelivery: '7-15个工作日'
})

const currencySettings = reactive({
  defaultCurrency: 'USD',
  supportedCurrencies: ['USD', 'CNY', 'EUR'],
  autoUpdateRates: true
})

const notificationSettings = reactive({
  newOrder: true,
  orderStatus: true,
  lowStock: true,
  stockThreshold: 10,
  newUser: false
})

// 从 WordPress API 加载设置
onMounted(async () => {
  loadingSettings.value = true
  try {
    const settings = await settingsApi.get()
    if (settings.siteName) generalSettings.siteName = settings.siteName
    if (settings.siteDescription) generalSettings.siteDescription = settings.siteDescription
    if (settings.contactEmail) generalSettings.contactEmail = settings.contactEmail
    if (settings.contactPhone) generalSettings.contactPhone = settings.contactPhone
    if (settings.address) generalSettings.address = settings.address
    if (settings.defaultShippingFee) shippingSettings.defaultShippingFee = settings.defaultShippingFee
    if (settings.estimatedDelivery) shippingSettings.estimatedDelivery = settings.estimatedDelivery
  } catch (e) {
    console.error('加载设置失败:', e)
  } finally {
    loadingSettings.value = false
  }
  // 从 localStorage 加载支付、货币、通知设置
  try {
    const local = JSON.parse(localStorage.getItem('localSettings') || '{}')
    if (local.payment) Object.assign(paymentSettings, local.payment)
    if (local.currency) Object.assign(currencySettings, local.currency)
    if (local.notification) Object.assign(notificationSettings, local.notification)
  } catch (e) {}
})

const handleMenuSelect = (index) => {
  activeMenu.value = index
}

const saveSettings = async (type) => {
  try {
    if (type === 'general') {
      await settingsApi.update({
        siteName: generalSettings.siteName,
        siteDescription: generalSettings.siteDescription,
        contactEmail: generalSettings.contactEmail,
        contactPhone: generalSettings.contactPhone,
        address: generalSettings.address,
      })
    } else if (type === 'shipping') {
      await settingsApi.update({
        defaultShippingFee: shippingSettings.defaultShippingFee,
        estimatedDelivery: shippingSettings.estimatedDelivery,
      })
    }
    // 支付、货币、通知设置暂存 localStorage
    if (['payment', 'currency', 'notification'].includes(type)) {
      const localSettings = JSON.parse(localStorage.getItem('localSettings') || '{}')
      localSettings[type] = type === 'payment' ? { ...paymentSettings }
        : type === 'currency' ? { ...currencySettings }
        : { ...notificationSettings }
      localStorage.setItem('localSettings', JSON.stringify(localSettings))
    }
    ElMessage.success(`${getSettingsName(type)} 已保存`)
  } catch (e) {
    ElMessage.error('保存失败: ' + e.message)
  }
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
