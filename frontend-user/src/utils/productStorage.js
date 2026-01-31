// 商品数据存储工具 - 使用后端 API

// 清除旧的 cookie 数据（解决 400 错误）
const clearOldCookies = () => {
  const cookies = document.cookie.split(';')
  cookies.forEach(cookie => {
    const name = cookie.split('=')[0].trim()
    if (name.startsWith('products_chunk') || name === 'products_chunks_count') {
      document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/`
    }
  })
}

// 页面加载时清除旧 cookie
clearOldCookies()

// 分类映射（中文 -> 英文 slug）
const categoryMap = {
  '数码电子': 'electronics',
  '时尚服饰': 'fashion',
  '美妆护肤': 'beauty',
  '家居生活': 'home'
}

const API_URL = '/api/products'

// 从 API 加载商品
export const loadProducts = async () => {
  try {
    const response = await fetch(API_URL)
    if (response.ok) {
      const products = await response.json()
      // 转换分类名称为 slug
      return products.map(p => ({
        ...p,
        category: categoryMap[p.category] || p.category
      }))
    }
    console.error('加载商品失败:', response.status)
    return []
  } catch (e) {
    console.error('加载商品数据失败:', e)
    return []
  }
}

// 获取单个商品
export const getProductById = async (id) => {
  try {
    const response = await fetch(`${API_URL}/${id}`)
    if (response.ok) {
      const product = await response.json()
      return {
        ...product,
        category: categoryMap[product.category] || product.category
      }
    }
    return null
  } catch (e) {
    console.error('获取商品失败:', e)
    return null
  }
}

export default {
  loadProducts,
  getProductById
}
