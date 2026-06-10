<template>
  <div class="help-page">
    <div class="container">
      <div class="help-header">
        <h1>{{ pageTitle }}</h1>
        <p class="subtitle">
          {{ pageSubtitle }}
        </p>
      </div>
      
      <div class="help-content">
        <!-- 帮助中心首页 -->
        <div
          v-if="!topic"
          class="help-grid"
        >
          <router-link
            v-for="item in helpTopics"
            :key="item.path"
            :to="item.path"
            class="help-card"
          >
            <div
              class="help-icon"
              v-html="item.icon"
            />
            <h3>{{ item.title }}</h3>
            <p>{{ item.description }}</p>
          </router-link>
        </div>
        
        <!-- 退换货政策 -->
        <div
          v-else-if="topic === 'returns'"
          class="help-article"
        >
          <h2>退换货政策</h2>
          <div class="article-section">
            <h3>退货条件</h3>
            <ul>
              <li>商品签收后30天内可申请退货</li>
              <li>商品需保持原包装完好，未使用、未损坏</li>
              <li>需提供有效购买凭证</li>
            </ul>
          </div>
          <div class="article-section">
            <h3>换货流程</h3>
            <ol>
              <li>登录账户，进入"我的订单"</li>
              <li>选择需要换货的订单，点击"申请换货"</li>
              <li>填写换货原因并提交</li>
              <li>等待客服审核（1-2个工作日）</li>
              <li>审核通过后，按指引寄回商品</li>
            </ol>
          </div>
          <div class="article-section">
            <h3>不支持退换的商品</h3>
            <ul>
              <li>个人护理类商品（已拆封）</li>
              <li>定制类商品</li>
              <li>食品类商品</li>
              <li>虚拟商品</li>
            </ul>
          </div>
        </div>
        
        <!-- 配送说明 -->
        <div
          v-else-if="topic === 'shipping'"
          class="help-article"
        >
          <h2>配送说明</h2>
          <div class="article-section">
            <h3>配送范围</h3>
            <p>我们支持全球200+国家和地区的配送服务。</p>
          </div>
          <div class="article-section">
            <h3>配送时效</h3>
            <table class="info-table">
              <thead>
                <tr><th>地区</th><th>标准配送</th><th>加急配送</th></tr>
              </thead>
              <tbody>
                <tr><td>中国大陆</td><td>3-5天</td><td>1-2天</td></tr>
                <tr><td>港澳台</td><td>5-7天</td><td>3-4天</td></tr>
                <tr><td>亚洲其他</td><td>7-10天</td><td>4-6天</td></tr>
                <tr><td>欧美地区</td><td>10-15天</td><td>5-8天</td></tr>
              </tbody>
            </table>
          </div>
          <div class="article-section">
            <h3>运费说明</h3>
            <ul>
              <li>订单满 $99 免运费（中国大陆）</li>
              <li>订单满 $199 免运费（国际配送）</li>
              <li>加急配送需额外支付运费</li>
            </ul>
          </div>
        </div>
        
        <!-- 支付方式 -->
        <div
          v-else-if="topic === 'payment'"
          class="help-article"
        >
          <h2>支付方式</h2>
          <div class="article-section">
            <h3>支持的支付方式</h3>
            <ul>
              <li>信用卡/借记卡（Visa、MasterCard、American Express）</li>
              <li>PayPal</li>
              <li>支付宝</li>
              <li>微信支付</li>
              <li>Apple Pay</li>
              <li>Google Pay</li>
            </ul>
          </div>
          <div class="article-section">
            <h3>支付安全</h3>
            <p>所有支付信息均通过SSL加密传输，我们不会存储您的完整支付卡信息。</p>
          </div>
        </div>
        
        <!-- 新手指南 -->
        <div
          v-else-if="topic === 'guide'"
          class="help-article"
        >
          <h2>新手指南</h2>
          <div class="article-section">
            <h3>如何购物</h3>
            <ol>
              <li>浏览商品或使用搜索功能找到心仪商品</li>
              <li>选择规格后点击"加入购物车"</li>
              <li>进入购物车确认商品信息</li>
              <li>填写收货地址并选择支付方式</li>
              <li>完成支付，等待收货</li>
            </ol>
          </div>
          <div class="article-section">
            <h3>注册会员优势</h3>
            <ul>
              <li>新用户首单9折优惠</li>
              <li>积分返利，可抵扣现金</li>
              <li>专属会员活动</li>
              <li>订单追踪更便捷</li>
            </ul>
          </div>
        </div>
        
        <!-- 关税说明 -->
        <div
          v-else-if="topic === 'duty'"
          class="help-article"
        >
          <h2>关税说明</h2>
          <div class="article-section">
            <h3>关税政策</h3>
            <p>跨境购物可能产生进口关税，具体税率根据商品类别和目的地国家/地区的海关政策而定。</p>
          </div>
          <div class="article-section">
            <h3>常见税率参考</h3>
            <table class="info-table">
              <thead>
                <tr><th>商品类别</th><th>参考税率</th></tr>
              </thead>
              <tbody>
                <tr><td>电子产品</td><td>0-15%</td></tr>
                <tr><td>服装鞋包</td><td>10-25%</td></tr>
                <tr><td>化妆品</td><td>15-50%</td></tr>
                <tr><td>食品保健</td><td>10-30%</td></tr>
              </tbody>
            </table>
            <p class="note">
              * 以上税率仅供参考，实际以海关核定为准
            </p>
          </div>
        </div>
        
        <!-- 货币转换 -->
        <div
          v-else-if="topic === 'currency'"
          class="help-article"
        >
          <h2>货币转换</h2>
          <div class="article-section">
            <h3>支持的货币</h3>
            <p>我们支持多种货币显示和结算：</p>
            <ul>
              <li>美元 (USD)</li>
              <li>人民币 (CNY)</li>
              <li>欧元 (EUR)</li>
              <li>英镑 (GBP)</li>
              <li>日元 (JPY)</li>
              <li>港币 (HKD)</li>
            </ul>
          </div>
          <div class="article-section">
            <h3>汇率说明</h3>
            <p>商品价格会根据实时汇率自动转换。最终结算金额以支付时的汇率为准。</p>
          </div>
        </div>
        
        <!-- 尺码对照 -->
        <div
          v-else-if="topic === 'size'"
          class="help-article"
        >
          <h2>尺码对照表</h2>
          <div class="article-section">
            <h3>服装尺码</h3>
            <table class="info-table">
              <thead>
                <tr><th>国际码</th><th>中国码</th><th>胸围(cm)</th><th>腰围(cm)</th></tr>
              </thead>
              <tbody>
                <tr><td>XS</td><td>155/80A</td><td>76-80</td><td>60-64</td></tr>
                <tr><td>S</td><td>160/84A</td><td>80-84</td><td>64-68</td></tr>
                <tr><td>M</td><td>165/88A</td><td>84-88</td><td>68-72</td></tr>
                <tr><td>L</td><td>170/92A</td><td>88-92</td><td>72-76</td></tr>
                <tr><td>XL</td><td>175/96A</td><td>92-96</td><td>76-80</td></tr>
              </tbody>
            </table>
          </div>
          <div class="article-section">
            <h3>鞋码对照</h3>
            <table class="info-table">
              <thead>
                <tr><th>中国码</th><th>欧码</th><th>美码</th><th>脚长(cm)</th></tr>
              </thead>
              <tbody>
                <tr><td>36</td><td>36</td><td>5.5</td><td>23</td></tr>
                <tr><td>37</td><td>37</td><td>6</td><td>23.5</td></tr>
                <tr><td>38</td><td>38</td><td>7</td><td>24</td></tr>
                <tr><td>39</td><td>39</td><td>7.5</td><td>24.5</td></tr>
                <tr><td>40</td><td>40</td><td>8</td><td>25</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <div
        v-if="topic"
        class="help-contact"
      >
        <p>
          还有疑问？<router-link to="/help">
            返回帮助中心
          </router-link> 或联系客服
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const topic = computed(() => route.params.topic)

const helpTopics = [
  { path: '/help/returns', title: '退换货政策', description: '了解退换货流程和条件', icon: '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>' },
  { path: '/help/shipping', title: '配送说明', description: '配送范围、时效和运费', icon: '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>' },
  { path: '/help/payment', title: '支付方式', description: '支持的支付方式和安全说明', icon: '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>' },
  { path: '/help/guide', title: '新手指南', description: '快速上手购物流程', icon: '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' },
  { path: '/help/duty', title: '关税说明', description: '跨境购物关税政策', icon: '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>' },
  { path: '/help/currency', title: '货币转换', description: '支持的货币和汇率说明', icon: '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>' },
  { path: '/help/size', title: '尺码对照', description: '服装鞋码对照表', icon: '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>' },
]

const pageTitle = computed(() => {
  if (!topic.value) return '帮助中心'
  const found = helpTopics.find(t => t.path === `/help/${topic.value}`)
  return found?.title || '帮助中心'
})

const pageSubtitle = computed(() => {
  if (!topic.value) return '我们随时为您提供帮助'
  return ''
})
</script>

<style lang="scss" scoped>
.help-page {
  min-height: 60vh;
  padding: 60px 0;
  background: #fafafa;
}

.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 20px;
}

.help-header {
  text-align: center;
  margin-bottom: 48px;
  
  h1 {
    font-size: 36px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
  }
  
  .subtitle {
    font-size: 18px;
    color: #666;
  }
}

.help-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.help-card {
  background: #fff;
  border-radius: 16px;
  padding: 32px;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid #eee;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    border-color: #6366f1;
  }
  
  .help-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: #fff;
  }
  
  h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
  }
  
  p {
    font-size: 14px;
    color: #666;
    margin: 0;
  }
}

.help-article {
  background: #fff;
  border-radius: 16px;
  padding: 48px;
  
  h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 32px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f0f0;
  }
  
  .article-section {
    margin-bottom: 32px;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    h3 {
      font-size: 18px;
      font-weight: 600;
      color: #1a1a1a;
      margin-bottom: 16px;
    }
    
    p {
      font-size: 15px;
      color: #444;
      line-height: 1.8;
      margin-bottom: 12px;
    }
    
    ul, ol {
      padding-left: 24px;
      margin: 0;
      
      li {
        font-size: 15px;
        color: #444;
        line-height: 2;
      }
    }
    
    .note {
      font-size: 13px;
      color: #888;
      font-style: italic;
    }
  }
}

.info-table {
  width: 100%;
  border-collapse: collapse;
  margin: 16px 0;
  
  th, td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 14px;
  }
  
  th {
    background: #f8f8f8;
    font-weight: 600;
    color: #1a1a1a;
  }
  
  td {
    color: #444;
  }
  
  tr:hover td {
    background: #fafafa;
  }
}

.help-contact {
  text-align: center;
  margin-top: 32px;
  
  p {
    font-size: 15px;
    color: #666;
    
    a {
      color: #6366f1;
      text-decoration: none;
      font-weight: 500;
      
      &:hover {
        text-decoration: underline;
      }
    }
  }
}

@media (max-width: 768px) {
  .help-page {
    padding: 40px 0;
  }
  
  .help-header h1 {
    font-size: 28px;
  }
  
  .help-article {
    padding: 24px;
    
    h2 {
      font-size: 22px;
    }
  }
  
  .info-table {
    font-size: 13px;
    
    th, td {
      padding: 8px 12px;
    }
  }
}
</style>
