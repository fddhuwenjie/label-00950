// 网站设置工具 - 从 cookie 读取后台配置

// 获取 cookie 值
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

// 默认设置
const defaultSettings = {
  general: {
    siteName: '跨境电商商城',
    siteDescription: '跨越国界，甄选全球顶级品牌，品质保证，极速配送',
    contactEmail: 'support@example.com',
    contactPhone: '+1-800-123-4567',
    address: '123 Commerce St, New York, NY 10001, USA'
  },
  shipping: {
    freeShippingThreshold: 99,
    defaultShippingFee: 9.99,
    warehouseAddress: 'Warehouse A, Logistics Center',
    estimatedDelivery: '7-14 个工作日'
  },
  currency: {
    defaultCurrency: 'USD',
    supportedCurrencies: ['USD', 'CNY', 'EUR']
  }
}

// 获取所有设置
export const getSettings = () => {
  const saved = getCookie('siteSettings')
  if (saved) {
    try {
      const parsed = JSON.parse(saved)
      return {
        general: { ...defaultSettings.general, ...parsed.general },
        shipping: { ...defaultSettings.shipping, ...parsed.shipping },
        currency: { ...defaultSettings.currency, ...parsed.currency }
      }
    } catch (e) {
      return defaultSettings
    }
  }
  return defaultSettings
}

// 获取基本设置
export const getGeneralSettings = () => {
  return getSettings().general
}

// 获取物流设置
export const getShippingSettings = () => {
  return getSettings().shipping
}

// 获取货币设置
export const getCurrencySettings = () => {
  return getSettings().currency
}

export default {
  getSettings,
  getGeneralSettings,
  getShippingSettings,
  getCurrencySettings
}
