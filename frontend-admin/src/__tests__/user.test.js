import { describe, it, expect } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useUserStore } from '@/stores/user'

describe('UserStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
  })

  it('初始未登录', () => {
    const user = useUserStore()
    expect(user.isLoggedIn).toBe(false)
  })

  it('登出清除状态', () => {
    const user = useUserStore()
    user.logout()
    expect(user.isLoggedIn).toBe(false)
    expect(user.token).toBe('')
  })
})
