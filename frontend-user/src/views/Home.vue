<template>
  <div class="home">
    <!-- Hero Banner - 顶级设计风格 -->
    <section class="hero">
      <div class="hero-bg">
        <div class="gradient-overlay"></div>
        <div class="floating-elements">
          <div class="float-circle c1"></div>
          <div class="float-circle c2"></div>
          <div class="float-circle c3"></div>
          <div class="blur-blob b1"></div>
          <div class="blur-blob b2"></div>
        </div>
      </div>
      <div class="container">
        <div class="hero-content">
          <span class="hero-tag">GLOBAL SHOPPING</span>
          <h1>全球精选<br/><span class="gradient-text">奢品好物</span></h1>
          <p>{{ siteSettings.siteDescription }}</p>
          <div class="hero-actions">
            <router-link to="/products" class="btn-primary">
              <span>立即探索</span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </router-link>
            <router-link to="/register" class="btn-secondary">
              新人礼遇
            </router-link>
          </div>
          <div class="hero-stats">
            <div class="stat">
              <span class="stat-value">200+</span>
              <span class="stat-label">配送国家</span>
            </div>
            <div class="stat">
              <span class="stat-value">50K+</span>
              <span class="stat-label">精选商品</span>
            </div>
            <div class="stat">
              <span class="stat-value">100%</span>
              <span class="stat-label">正品保障</span>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- 品牌展示 -->
    <section class="brands-section">
      <div class="container">
        <div class="brands-scroll">
          <span class="brand-text">APPLE</span>
          <span class="brand-dot">●</span>
          <span class="brand-text">GUCCI</span>
          <span class="brand-dot">●</span>
          <span class="brand-text">DYSON</span>
          <span class="brand-dot">●</span>
          <span class="brand-text">LA MER</span>
          <span class="brand-dot">●</span>
          <span class="brand-text">NIKE</span>
          <span class="brand-dot">●</span>
          <span class="brand-text">SONY</span>
          <span class="brand-dot">●</span>
          <span class="brand-text">SK-II</span>
        </div>
      </div>
    </section>
    
    <!-- 特色服务 -->
    <section class="features">
      <div class="container">
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="1" y="3" width="15" height="13" rx="2"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
              </svg>
            </div>
            <div class="feature-content">
              <h3>全球极速配送</h3>
              <p>覆盖200+国家和地区，7-14天送达</p>
            </div>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M9 12l2 2 4-4"/>
              </svg>
            </div>
            <div class="feature-content">
              <h3>100%正品保障</h3>
              <p>品牌直供，假一赔十</p>
            </div>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M23 4v6h-6"/>
                <path d="M1 20v-6h6"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
              </svg>
            </div>
            <div class="feature-content">
              <h3>无忧退换</h3>
              <p>30天无理由退换货</p>
            </div>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
              </svg>
            </div>
            <div class="feature-content">
              <h3>专属客服</h3>
              <p>7×24小时VIP服务</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- 热门分类 -->
    <section class="categories-section">
      <div class="container">
        <div class="section-header">
          <div class="section-title-group">
            <span class="section-tag">CATEGORIES</span>
            <h2>探索品类</h2>
          </div>
        </div>
        <div class="categories-grid">
          <router-link 
            v-for="category in categories" 
            :key="category.id"
            :to="`/products?category=${category.slug}`"
            class="category-card"
          >
            <div class="category-image">
              <img :src="category.image" :alt="category.name" />
              <div class="category-overlay"></div>
            </div>
            <div class="category-info">
              <h3>{{ category.name }}</h3>
              <span class="category-count">{{ category.count }} 件商品</span>
            </div>
          </router-link>
        </div>
      </div>
    </section>
    
    <!-- 热销商品 -->
    <section class="products-section">
      <div class="container">
        <div class="section-header">
          <div class="section-title-group">
            <span class="section-tag">BEST SELLERS</span>
            <h2>热销爆款</h2>
          </div>
          <router-link to="/products" class="view-all-btn">
            查看全部
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </router-link>
        </div>
        <div class="products-grid">
          <ProductCard 
            v-for="product in hotProducts" 
            :key="product.id" 
            :product="product" 
          />
        </div>
      </div>
    </section>
    
    <!-- 促销横幅 -->
    <section class="promo-section">
      <div class="container">
        <div class="promo-card">
          <div class="promo-content">
            <span class="promo-tag">LIMITED OFFER</span>
            <h2>新用户专享</h2>
            <p>首次注册即享 <strong>9折优惠</strong></p>
            <router-link to="/register" class="promo-btn">
              立即领取
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </router-link>
          </div>
          <div class="promo-decoration">
            <div class="deco-ring r1"></div>
            <div class="deco-ring r2"></div>
            <div class="deco-ring r3"></div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- 新品上架 -->
    <section class="products-section alt-bg">
      <div class="container">
        <div class="section-header">
          <div class="section-title-group">
            <span class="section-tag">NEW ARRIVALS</span>
            <h2>新品上架</h2>
          </div>
          <router-link to="/products?sort=newest" class="view-all-btn">
            查看全部
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </router-link>
        </div>
        <div class="products-grid">
          <ProductCard 
            v-for="product in newProducts" 
            :key="product.id" 
            :product="product" 
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import ProductCard from '@/components/ProductCard.vue'
import { getGeneralSettings } from '@/utils/settings'
import { loadProducts } from '@/utils/productStorage'

const siteSettings = reactive(getGeneralSettings())

// 从 API 加载商品
const allProducts = ref([])

onMounted(async () => {
  allProducts.value = await loadProducts()
})

// 分类信息 - 动态计算数量
const categories = computed(() => {
  const getCategoryCount = (slug) => {
    return allProducts.value.filter(p => p.category === slug).length
  }
  
  return [
    { 
      id: 1, 
      name: '数码电子', 
      slug: 'electronics', 
      count: getCategoryCount('electronics'),
      image: 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=400&h=300&fit=crop'
    },
    { 
      id: 2, 
      name: '时尚服饰', 
      slug: 'fashion', 
      count: getCategoryCount('fashion'),
      image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=400&h=300&fit=crop'
    },
    { 
      id: 3, 
      name: '美妆护肤', 
      slug: 'beauty', 
      count: getCategoryCount('beauty'),
      image: 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=400&h=300&fit=crop'
    },
    { 
      id: 4, 
      name: '家居生活', 
      slug: 'home', 
      count: getCategoryCount('home'),
      image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=300&fit=crop'
    },
  ]
})

// 热门商品 - 取前4个
const hotProducts = computed(() => {
  return allProducts.value.slice(0, 4)
})

// 新品 - 取第5-8个
const newProducts = computed(() => {
  return allProducts.value.slice(4, 8)
})
</script>

<style lang="scss" scoped>
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

// Hero Section - 顶级设计
.hero {
  position: relative;
  min-height: 90vh;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: #0a0a0f;
}

.hero-bg {
  position: absolute;
  inset: 0;
  
  .gradient-overlay {
    position: absolute;
    inset: 0;
    background: 
      radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
      radial-gradient(ellipse at 80% 50%, rgba(168, 85, 247, 0.15) 0%, transparent 50%),
      radial-gradient(ellipse at 50% 100%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
  }
  
  .floating-elements {
    position: absolute;
    inset: 0;
  }
  
  .float-circle {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: float 20s infinite ease-in-out;
    
    &.c1 {
      width: 400px;
      height: 400px;
      top: -100px;
      right: -100px;
      animation-delay: 0s;
    }
    
    &.c2 {
      width: 300px;
      height: 300px;
      bottom: -50px;
      left: 5%;
      animation-delay: -5s;
    }
    
    &.c3 {
      width: 200px;
      height: 200px;
      top: 30%;
      left: 15%;
      animation-delay: -10s;
    }
  }
  
  .blur-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    
    &.b1 {
      width: 500px;
      height: 500px;
      background: rgba(99, 102, 241, 0.3);
      top: -200px;
      right: -100px;
    }
    
    &.b2 {
      width: 400px;
      height: 400px;
      background: rgba(168, 85, 247, 0.3);
      bottom: -150px;
      left: -100px;
    }
  }
}

@keyframes float {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-30px) rotate(5deg); }
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 700px;
  padding: 40px 0;
}

.hero-tag {
  display: inline-block;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 2px;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 24px;
}

.hero-content h1 {
  font-size: 72px;
  font-weight: 800;
  line-height: 1.1;
  color: #fff;
  margin-bottom: 24px;
  letter-spacing: -2px;
}

.gradient-text {
  background: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #f472b6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-content p {
  font-size: 20px;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.6;
  margin-bottom: 40px;
}

.hero-actions {
  display: flex;
  gap: 16px;
  margin-bottom: 60px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 18px 36px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5);
  }
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  padding: 18px 36px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s ease;
  
  &:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
  }
}

.hero-stats {
  display: flex;
  gap: 48px;
}

.stat {
  .stat-value {
    display: block;
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
  }
  
  .stat-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
    letter-spacing: 1px;
  }
}

// Brands Section
.brands-section {
  background: #0a0a0f;
  padding: 40px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  overflow: hidden;
}

.brands-scroll {
  display: flex;
  align-items: center;
  gap: 40px;
  justify-content: center;
}

.brand-text {
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 4px;
  color: rgba(255, 255, 255, 0.3);
}

.brand-dot {
  color: rgba(255, 255, 255, 0.2);
  font-size: 8px;
}

// Features Section
.features {
  padding: 100px 0;
  background: #fafafa;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.feature-card {
  display: flex;
  align-items: flex-start;
  gap: 20px;
  padding: 32px;
  background: #fff;
  border-radius: 20px;
  transition: all 0.3s ease;
  border: 1px solid #f0f0f0;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    border-color: transparent;
  }
  
  .feature-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
  }
  
  .feature-content {
    h3 {
      font-size: 18px;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 8px;
    }
    
    p {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
      margin: 0;
    }
  }
}

// Section Header
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 48px;
}

.section-title-group {
  .section-tag {
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 3px;
    color: #6366f1;
    margin-bottom: 12px;
  }
  
  h2 {
    font-size: 40px;
    font-weight: 800;
    color: #1a1a1a;
    letter-spacing: -1px;
    margin: 0;
  }
}

.view-all-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 600;
  color: #6366f1;
  text-decoration: none;
  padding: 12px 24px;
  border-radius: 10px;
  transition: all 0.2s ease;
  
  &:hover {
    background: rgba(99, 102, 241, 0.1);
  }
}

// Categories Section
.categories-section {
  padding: 100px 0;
  background: #fff;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.category-card {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  text-decoration: none;
  aspect-ratio: 4/3;
  
  &:hover {
    .category-image img {
      transform: scale(1.1);
    }
    
    .category-overlay {
      opacity: 0.6;
    }
  }
  
  .category-image {
    position: absolute;
    inset: 0;
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
  }
  
  .category-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.8) 100%);
    opacity: 0.7;
    transition: opacity 0.3s ease;
  }
  
  .category-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 24px;
    color: #fff;
    
    h3 {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 4px;
    }
    
    .category-count {
      font-size: 14px;
      opacity: 0.8;
    }
  }
}

// Products Section
.products-section {
  padding: 100px 0;
  background: #fff;
  
  &.alt-bg {
    background: #fafafa;
  }
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

// Promo Section
.promo-section {
  padding: 60px 0;
  background: #fff;
}

.promo-card {
  position: relative;
  padding: 80px;
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 100%);
  border-radius: 32px;
  overflow: hidden;
  
  .promo-content {
    position: relative;
    z-index: 2;
    max-width: 500px;
  }
  
  .promo-tag {
    display: inline-block;
    padding: 8px 16px;
    background: rgba(236, 72, 153, 0.2);
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #f472b6;
    margin-bottom: 20px;
  }
  
  h2 {
    font-size: 48px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 16px;
    letter-spacing: -1px;
  }
  
  p {
    font-size: 20px;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 32px;
    
    strong {
      color: #f472b6;
    }
  }
  
  .promo-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 18px 36px;
    background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%);
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(236, 72, 153, 0.4);
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(236, 72, 153, 0.5);
    }
  }
  
  .promo-decoration {
    position: absolute;
    right: 100px;
    top: 50%;
    transform: translateY(-50%);
    
    .deco-ring {
      position: absolute;
      border-radius: 50%;
      border: 1px solid rgba(244, 114, 182, 0.2);
      
      &.r1 {
        width: 300px;
        height: 300px;
        top: -150px;
        left: -150px;
      }
      
      &.r2 {
        width: 400px;
        height: 400px;
        top: -200px;
        left: -200px;
      }
      
      &.r3 {
        width: 500px;
        height: 500px;
        top: -250px;
        left: -250px;
      }
    }
  }
}

// Responsive
@media (max-width: 1200px) {
  .hero-content h1 {
    font-size: 56px;
  }
  
  .features-grid,
  .categories-grid,
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .hero {
    min-height: 80vh;
  }
  
  .hero-content h1 {
    font-size: 40px;
  }
  
  .hero-stats {
    gap: 24px;
  }
  
  .stat .stat-value {
    font-size: 28px;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .features-grid,
  .categories-grid,
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .brands-scroll {
    gap: 20px;
    
    .brand-text {
      font-size: 14px;
    }
  }
  
  .section-title-group h2 {
    font-size: 28px;
  }
  
  .promo-card {
    padding: 40px 24px;
    
    h2 {
      font-size: 32px;
    }
    
    .promo-decoration {
      display: none;
    }
  }
}
</style>
