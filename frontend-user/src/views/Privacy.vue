<template>
  <div class="legal-page">
    <div class="container">
      <h1>隐私政策</h1>
      <p class="update-date">最后更新日期：2025年1月1日</p>
      <section>
        <h2>1. 信息收集</h2>
        <p>我们收集的信息包括：注册信息（用户名、邮箱、手机号）、订单信息（收货地址、支付信息）、浏览行为数据（访问页面、搜索记录）。我们仅收集提供服务所必需的最少信息。</p>
      </section>
      <section>
        <h2>2. 信息使用</h2>
        <p>收集的信息用于：处理订单和支付、提供客户服务、改善用户体验、发送订单状态通知。未经您的同意，我们不会将您的个人信息用于营销目的。</p>
      </section>
      <section>
        <h2>3. 信息保护</h2>
        <p>我们采用行业标准的安全措施保护您的个人信息，包括：数据传输加密（HTTPS/TLS）、密码哈希存储、访问权限控制、定期安全审计。</p>
      </section>
      <section>
        <h2>4. 信息共享</h2>
        <p>我们不会出售您的个人信息。仅在以下情况下共享：与物流合作伙伴共享配送所需信息、与支付服务商共享支付所需信息、法律法规要求的情况。</p>
      </section>
      <section>
        <h2>5. Cookie 使用</h2>
        <p>本平台使用 Cookie 和类似技术来记住您的登录状态、购物车内容和偏好设置。您可以通过浏览器设置管理 Cookie，但禁用 Cookie 可能影响部分功能的使用。</p>
      </section>
      <section>
        <h2>6. 用户权利</h2>
        <p>您有权：访问和更正您的个人信息、删除您的账户和相关数据、撤回对信息处理的同意、获取您的数据副本。如需行使上述权利，请联系我们的客服团队。</p>
      </section>
      <section>
        <h2>7. 未成年人保护</h2>
        <p>本平台不面向16岁以下的未成年人。如果我们发现收集了未成年人的信息，将立即删除相关数据。</p>
      </section>
      <section>
        <h2>8. 政策更新</h2>
        <p>我们可能会不时更新本隐私政策。重大变更将通过平台通知或邮件告知。继续使用本平台即视为接受更新后的政策。</p>
      </section>
      <section>
        <h2>9. 联系我们</h2>
        <p>如对本隐私政策有任何疑问，请通过以下方式联系我们：邮箱 privacy@example.com，电话 +86 400-888-8888。</p>
      </section>
      <div class="back-link">
        <router-link to="/">返回首页</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>

<style lang="scss" scoped>
.legal-page {
  min-height: 100vh; background: #f8f9fa; padding: 40px 20px;
  .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 48px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
  h1 { font-size: 28px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
  .update-date { font-size: 14px; color: #999; margin-bottom: 32px; }
  section { margin-bottom: 24px; }
  h2 { font-size: 18px; font-weight: 600; color: #333; margin-bottom: 8px; }
  p { font-size: 15px; color: #555; line-height: 1.8; }
  .back-link { margin-top: 32px; text-align: center; a { color: #6366f1; text-decoration: none; font-weight: 500; &:hover { text-decoration: underline; } } }
}
</style>
