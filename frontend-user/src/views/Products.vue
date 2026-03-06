<template>
  <div class="products-page">
    <!-- 页面头部 -->
    <section class="page-hero">
      <div class="container">
        <span class="page-tag">SHOP ALL</span>
        <h1>{{ searchKeyword ? `搜索: "${searchKeyword}"` : '全部商品' }}</h1>
        <p>{{ searchKeyword ? `找到 ${filteredProducts.length} 个相关商品` : '发现全球顶级品牌精选好物' }}</p>
      </div>
    </section>
    
    <div class="container">
      <div class="products-layout">
        <!-- 筛选侧边栏 -->
        <aside class="filters-sidebar">
          <div class="filter-section">
            <h3>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16v2.172a2 2 0 0 1-.586 1.414L15 12v7l-6-2v-5L4.586 7.586A2 2 0 0 1 4 6.172V4z"/>
              </svg>
              商品分类
            </h3>
            <ul class="filter-list">
              <li 
                v-for="cat in categories" 
                :key="cat.id"
                :class="{ active: selectedCategory === cat.slug }"
                @click="selectCategory(cat.slug)"
              >
                <span class="filter-name">{{ cat.name }}</span>
                <span class="filter-count">{{ cat.count }}</span>
              </li>
            </ul>
          </div>
          
          <div class="filter-section">
            <h3>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              价格区间
            </h3>
            <ul class="filter-list">
              <li 
                v-for="range in priceRanges" 
                :key="range.id"
                :class="{ active: selectedPriceRange === range.id }"
                @click="selectPriceRange(range.id)"
              >
                <span class="filter-name">{{ range.label }}</span>
              </li>
            </ul>
          </div>
          
          <button class="clear-filters" @click="clearFilters">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14z"/>
            </svg>
            清除筛选
          </button>
        </aside>
        
        <!-- 商品列表 -->
        <div class="products-main">
          <div class="products-toolbar">
            <span class="results-count">
              共 <strong>{{ filteredProducts.length }}</strong> 件商品
            </span>
            <div class="toolbar-right">
              <button v-if="searchKeyword" class="clear-search-toolbar" @click="clearSearch">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                清除搜索
              </button>
              <div class="sort-select">
                <select v-model="sortBy">
                  <option value="default">默认排序</option>
                  <option value="price_asc">价格从低到高</option>
                  <option value="price_desc">价格从高到低</option>
                  <option value="newest">最新上架</option>
                </select>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </div>
            </div>
          </div>
          
          <div class="products-grid">
            <ProductCard 
              v-for="product in filteredProducts" 
              :key="product.id" 
              :product="product" 
            />
          </div>
          
          <div v-if="filteredProducts.length === 0" class="no-products">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
              <circle cx="11" cy="11" r="8"/>
              <path d="M21 21l-4.35-4.35"/>
            </svg>
            <h3>{{ searchKeyword ? '未找到相关商品' : '暂无商品' }}</h3>
            <p>{{ searchKeyword ? '尝试其他关键词或' : '' }}调整筛选条件</p>
            <button v-if="searchKeyword" class="clear-search-btn" @click="clearSearch">
              清除搜索
            </button>
          </div>
          
          <!-- 分页 -->
          <div v-if="filteredProducts.length > 0" class="pagination">
            <button 
              class="page-btn"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6"/>
              </svg>
              上一页
            </button>
            <div class="page-numbers">
              <button 
                v-for="page in totalPages" 
                :key="page"
                :class="['page-num', { active: currentPage === page }]"
                @click="currentPage = page"
              >
                {{ page }}
              </button>
            </div>
            <button 
              class="page-btn"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              下一页
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProductCard from '@/components/ProductCard.vue'
import { productApi } from '@/utils/api'

const route = useRoute()
const router = useRouter()

const selectedCategory = ref('')
const selectedPriceRange = ref('')
const sortBy = ref('default')
const currentPage = ref(1)
const searchKeyword = ref('')
const pageSize = 12

// 从 API 加载商品数据
const products = ref([])
const loading = ref(false)

// 监听路由参数变化
onMounted(async () => {
  // 加载商品数据
  loading.value = true
  try {
    products.value = await productApi.getAll()
  } catch (e) {
    console.error('加载商品失败:', e)
  } finally {
    loading.value = false
  }
  
  if (route.query.search) {
    searchKeyword.value = route.query.search
  }
  if (route.query.category) {
    selectedCategory.value = route.query.category
  }
})

watch(() => route.query, (query) => {
  if (query.search) {
    searchKeyword.value = query.search
  } else {
    searchKeyword.value = ''
  }
  if (query.category) {
    selectedCategory.value = query.category
  }
  currentPage.value = 1
}, { immediate: true })

// 根据实际商品数量计算分类数
const getCategoryCount = (slug) => {
  let filtered = products.value
  if (searchKeyword.value) {
    const keyword = searchKeyword.value.toLowerCase()
    filtered = filtered.filter(p => p.name.toLowerCase().includes(keyword))
  }
  if (!slug) return filtered.length
  return filtered.filter(p => p.categorySlug === slug).length
}

const categories = computed(() => [
  { id: 1, name: '全部', slug: '', count: getCategoryCount('') },
  { id: 2, name: '数码电子', slug: 'electronics', count: getCategoryCount('electronics') },
  { id: 3, name: '时尚服饰', slug: 'fashion', count: getCategoryCount('fashion') },
  { id: 4, name: '美妆护肤', slug: 'beauty', count: getCategoryCount('beauty') },
  { id: 5, name: '家居生活', slug: 'home', count: getCategoryCount('home') },
])

const priceRanges = ref([
  { id: '', label: '全部价格' },
  { id: '0-200', label: '$0 - $200' },
  { id: '200-500', label: '$200 - $500' },
  { id: '500-1000', label: '$500 - $1000' },
  { id: '1000+', label: '$1000 以上' },
])

const filteredProducts = computed(() => {
  let result = [...products.value]
  
  // 搜索筛选
  if (searchKeyword.value) {
    const keyword = searchKeyword.value.toLowerCase()
    result = result.filter(p => p.name.toLowerCase().includes(keyword))
  }
  
  // 分类筛选
  if (selectedCategory.value) {
    result = result.filter(p => p.categorySlug === selectedCategory.value)
  }
  
  // 价格筛选
  if (selectedPriceRange.value) {
    const parts = selectedPriceRange.value.split('-')
    const min = Number(parts[0]) || 0
    const max = parts[1] === '+' ? Infinity : Number(parts[1]) || Infinity
    result = result.filter(p => {
      const price = p.salePrice || p.price
      return price >= min && price <= max
    })
  }
  
  // 排序
  switch (sortBy.value) {
    case 'price_asc':
      result.sort((a, b) => (a.salePrice || a.price) - (b.salePrice || b.price))
      break
    case 'price_desc':
      result.sort((a, b) => (b.salePrice || b.price) - (a.salePrice || a.price))
      break
    case 'newest':
      result.sort((a, b) => b.id - a.id)
      break
  }
  
  return result
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / pageSize)))

const selectCategory = (slug) => {
  selectedCategory.value = slug
  currentPage.value = 1
}

const selectPriceRange = (id) => {
  selectedPriceRange.value = id
  currentPage.value = 1
}

const clearFilters = () => {
  selectedCategory.value = ''
  selectedPriceRange.value = ''
  sortBy.value = 'default'
  currentPage.value = 1
}

const clearSearch = () => {
  searchKeyword.value = ''
  router.push('/products')
}
</script>

<style lang="scss" scoped>
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

// Page Hero
.page-hero {
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 100%);
  padding: 80px 0;
  text-align: center;
  
  .page-tag {
    display: inline-block;
    padding: 8px 16px;
    background: rgba(99, 102, 241, 0.2);
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 3px;
    color: #818cf8;
    margin-bottom: 16px;
  }
  
  h1 {
    font-size: 48px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 12px;
    letter-spacing: -1px;
  }
  
  p {
    font-size: 18px;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
  }
}

.products-layout {
  display: flex;
  gap: 40px;
  padding: 60px 0;
}

// Filters Sidebar
.filters-sidebar {
  width: 280px;
  flex-shrink: 0;
}

.filter-section {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
  border: 1px solid #f0f0f0;
  
  h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 20px;
    
    svg {
      color: #6366f1;
    }
  }
}

.filter-list {
  list-style: none;
  padding: 0;
  margin: 0;
  
  li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    border-radius: 10px;
    font-size: 14px;
    color: #666;
    transition: all 0.2s ease;
    margin-bottom: 4px;
    
    &:hover {
      background: #f8f8fc;
      color: #1a1a1a;
    }
    
    &.active {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      color: #fff;
      
      .filter-count {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
      }
    }
    
    .filter-count {
      padding: 4px 10px;
      background: #f0f0f5;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
  }
}

.clear-filters {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px;
  background: #fff;
  border: 1px solid #e5e5e5;
  border-radius: 12px;
  color: #666;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
  
  &:hover {
    border-color: #6366f1;
    color: #6366f1;
    background: #fafaff;
  }
}

// Products Main
.products-main {
  flex: 1;
}

.products-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 20px 24px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
  border: 1px solid #f0f0f0;
  
  .results-count {
    font-size: 15px;
    color: #666;
    
    strong {
      color: #1a1a1a;
      font-weight: 700;
    }
  }
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.clear-search-toolbar {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  color: #ef4444;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &:hover {
    background: #fee2e2;
    border-color: #fca5a5;
  }
}

.sort-select {
  position: relative;
  
  select {
    appearance: none;
    padding: 12px 40px 12px 16px;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #1a1a1a;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &:hover {
      border-color: #6366f1;
    }
    
    &:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
  }
  
  svg {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
  }
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.no-products {
  text-align: center;
  padding: 80px 0;
  
  svg {
    color: #ddd;
    margin-bottom: 24px;
  }
  
  h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 8px;
  }
  
  p {
    color: #999;
    font-size: 15px;
    margin-bottom: 24px;
  }
  
  .clear-search-btn {
    padding: 12px 28px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    }
  }
}

// Pagination
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 48px;
  padding-top: 48px;
  border-top: 1px solid #f0f0f0;
}

.page-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: #fff;
  border: 1px solid #e5e5e5;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #1a1a1a;
  transition: all 0.2s ease;
  
  &:hover:not(:disabled) {
    border-color: #6366f1;
    color: #6366f1;
  }
  
  &:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }
}

.page-numbers {
  display: flex;
  gap: 8px;
}

.page-num {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border: 1px solid #e5e5e5;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  color: #666;
  transition: all 0.2s ease;
  
  &:hover {
    border-color: #6366f1;
    color: #6366f1;
  }
  
  &.active {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-color: transparent;
    color: #fff;
  }
}

// Responsive
@media (max-width: 1200px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .page-hero {
    padding: 60px 0;
    
    h1 {
      font-size: 32px;
    }
  }
  
  .products-layout {
    flex-direction: column;
    padding: 40px 0;
  }
  
  .filters-sidebar {
    width: 100%;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .pagination {
    flex-wrap: wrap;
  }
}
</style>
