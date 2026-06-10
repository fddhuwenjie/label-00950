<template>
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>关于我们</h3>
          <p>{{ siteSettings.siteDescription }}</p>
          <div class="social-links">
            <a
              href="https://facebook.com"
              target="_blank"
              rel="noopener noreferrer"
              class="social-link"
              aria-label="Facebook"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
              </svg>
            </a>
            <a
              href="https://twitter.com"
              target="_blank"
              rel="noopener noreferrer"
              class="social-link"
              aria-label="Twitter"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
              </svg>
            </a>
            <a
              href="https://instagram.com"
              target="_blank"
              rel="noopener noreferrer"
              class="social-link"
              aria-label="Instagram"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
              </svg>
            </a>
          </div>
        </div>
        
        <div class="footer-section">
          <h3>客户服务</h3>
          <ul>
            <li>
              <router-link to="/help">
                帮助中心
              </router-link>
            </li>
            <li>
              <router-link to="/help/returns">
                退换货政策
              </router-link>
            </li>
            <li>
              <router-link to="/help/shipping">
                配送说明
              </router-link>
            </li>
            <li>
              <router-link to="/help/payment">
                支付方式
              </router-link>
            </li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h3>购物指南</h3>
          <ul>
            <li>
              <router-link to="/help/guide">
                新手指南
              </router-link>
            </li>
            <li>
              <router-link to="/help/duty">
                关税说明
              </router-link>
            </li>
            <li>
              <router-link to="/help/currency">
                货币转换
              </router-link>
            </li>
            <li>
              <router-link to="/help/size">
                尺码对照
              </router-link>
            </li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h3>联系我们</h3>
          <ul class="contact-info">
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
              </svg>
              <a :href="'mailto:' + siteSettings.contactEmail">{{ siteSettings.contactEmail }}</a>
            </li>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
              </svg>
              <a :href="'tel:' + siteSettings.contactPhone">{{ siteSettings.contactPhone }}</a>
            </li>
            <li>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle
                  cx="12"
                  cy="10"
                  r="3"
                />
              </svg>
              <span>{{ siteSettings.address }}</span>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; 2024 {{ siteSettings.siteName }}. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import { settingsApi } from '@/utils/api'

const siteSettings = reactive({
  siteName: '跨境电商商城',
  siteDescription: '汇聚全球顶级品牌，为您精选来自世界各地的优质好物',
  contactEmail: 'support@example.com',
  contactPhone: '+86 400-888-8888',
  address: '中国上海市浦东新区陆家嘴金融贸易区世纪大道100号',
})

onMounted(async () => {
  try {
    const settings = await settingsApi.get()
    Object.assign(siteSettings, settings)
  } catch (e) {
    // 使用默认值
  }
})
</script>

<style lang="scss" scoped>
.footer {
  background: #1a1a2e;
  color: #fff;
  padding: 60px 0 20px;
  margin-top: 60px;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-content {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 40px;
  padding-bottom: 40px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-section {
  h3 {
    font-size: 16px;
    margin-bottom: 20px;
    color: #fff;
  }
  
  p {
    color: #999;
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 20px;
  }
  
  ul {
    list-style: none;
    padding: 0;
    margin: 0;
    
    li {
      margin-bottom: 12px;
      
      a {
        color: #999;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.2s;
        
        &:hover {
          color: #667eea;
        }
      }
    }
  }
  
  .contact-info li {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #999;
    font-size: 14px;
    
    svg {
      color: #667eea;
      flex-shrink: 0;
    }
    
    a {
      color: #999;
      text-decoration: none;
      transition: color 0.2s;
      
      &:hover {
        color: #667eea;
      }
    }
  }
}

.social-links {
  display: flex;
  gap: 12px;
}

.social-link {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #999;
  transition: all 0.2s;
  
  &:hover {
    background: #667eea;
    color: #fff;
  }
}

.footer-bottom {
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 20px;
  
  p {
    color: #666;
    font-size: 14px;
    margin: 0;
  }
}

@media (max-width: 768px) {
  .footer {
    padding: 40px 0 20px;
    margin-top: 40px;
  }
  
  .container {
    padding: 0 16px;
  }
  
  .footer-content {
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }
  
  .footer-section {
    h3 {
      font-size: 14px;
      margin-bottom: 14px;
    }
    
    p {
      font-size: 13px;
    }
    
    ul li {
      margin-bottom: 10px;
      
      a {
        font-size: 13px;
      }
    }
    
    .contact-info li {
      font-size: 13px;
    }
  }
  
  .social-links {
    gap: 8px;
  }
  
  .social-link {
    width: 36px;
    height: 36px;
  }
  
  .footer-bottom {
    flex-direction: column;
    gap: 15px;
    text-align: center;
    
    p {
      font-size: 12px;
    }
  }
}

@media (max-width: 480px) {
  .footer-content {
    grid-template-columns: 1fr;
    gap: 20px;
    text-align: center;
  }
  
  .footer-section {
    &:first-child {
      order: 4;
    }
  }
  
  .social-links {
    justify-content: center;
  }
  
  .contact-info li {
    justify-content: center;
  }
}
</style>
