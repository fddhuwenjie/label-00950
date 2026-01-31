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

const API_URL = '/api/products'

// 从 API 加载商品
export const loadProducts = async () => {
  try {
    const response = await fetch(API_URL)
    if (response.ok) {
      return await response.json()
    }
    console.error('加载商品失败:', response.status)
    return []
  } catch (e) {
    console.error('加载商品数据失败:', e)
    return []
  }
}

// 添加商品
export const addProduct = async (product) => {
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(product)
    })
    if (response.ok) {
      return await response.json()
    }
    return null
  } catch (e) {
    console.error('添加商品失败:', e)
    return null
  }
}

// 更新商品
export const updateProduct = async (id, product) => {
  try {
    const response = await fetch(`${API_URL}/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(product)
    })
    if (response.ok) {
      return await response.json()
    }
    return null
  } catch (e) {
    console.error('更新商品失败:', e)
    return null
  }
}

// 删除商品
export const deleteProduct = async (id) => {
  try {
    const response = await fetch(`${API_URL}/${id}`, {
      method: 'DELETE'
    })
    return response.ok
  } catch (e) {
    console.error('删除商品失败:', e)
    return false
  }
}

export default {
  loadProducts,
  addProduct,
  updateProduct,
  deleteProduct
}
