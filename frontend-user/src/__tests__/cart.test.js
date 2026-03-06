import { describe, it, expect } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCartStore } from '@/stores/cart'

describe('CartStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('初始购物车为空', () => {
    const cart = useCartStore()
    expect(cart.items).toEqual([])
    expect(cart.totalItems).toBe(0)
  })

  it('添加商品到购物车', () => {
    const cart = useCartStore()
    cart.addItem({ id: 1, name: '测试商品', price: 99, image: '/test.jpg' })
    expect(cart.items.length).toBe(1)
    expect(cart.items[0].quantity).toBe(1)
  })

  it('重复添加增加数量', () => {
    const cart = useCartStore()
    cart.addItem({ id: 1, name: '测试商品', price: 99, image: '/test.jpg' })
    cart.addItem({ id: 1, name: '测试商品', price: 99, image: '/test.jpg' })
    expect(cart.items.length).toBe(1)
    expect(cart.items[0].quantity).toBe(2)
  })

  it('清空购物车', () => {
    const cart = useCartStore()
    cart.addItem({ id: 1, name: '测试商品', price: 99, image: '/test.jpg' })
    cart.clearCart()
    expect(cart.items).toEqual([])
  })
})
