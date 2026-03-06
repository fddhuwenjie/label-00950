<template>
  <div class="legal-page">
    <div class="container">
      <h1>服务条款</h1>
      <p class="update-date">最后更新日期：2025年1月1日</p>
      <section>
        <h2>1. 服务概述</h2>
        <p>欢迎使用跨境电商商城（以下简称"本平台"）。本平台为用户提供跨境商品浏览、购买、支付及物流追踪等服务。使用本平台即表示您同意遵守以下条款。</p>
      </section>
      <section>
        <h2>2. 用户注册与账户</h2>
        <p>用户需提供真实、准确的个人信息进行注册。您有责任保管好自己的账户密码，因密码泄露导致的损失由用户自行承担。本平台有权在发现违规行为时暂停或终止用户账户。</p>
      </section>
      <section>
        <h2>3. 商品与价格</h2>
        <p>本平台展示的商品信息（包括但不限于价格、库存、描述）可能随时变动。所有价格均以结算时显示的价格为准。跨境商品价格可能因汇率波动而有所调整。</p>
      </section>
      <section>
        <h2>4. 订单与支付</h2>
        <p>用户提交订单后，需在规定时间内完成支付。未支付的订单将在24小时后自动取消。支付完成后，订单状态将更新为"处理中"。本平台支持支付宝、微信支付和PayPal等支付方式。</p>
      </section>
      <section>
        <h2>5. 物流与配送</h2>
        <p>跨境商品的配送时间因目的地和物流方式而异，通常为7-30个工作日。物流费用在结算时明确显示。因海关检查等不可控因素导致的延迟，本平台不承担责任。</p>
      </section>
      <section>
        <h2>6. 退换货政策</h2>
        <p>收到商品后7天内，如商品存在质量问题，可申请退换货。退换货运费由责任方承担。定制商品、个人护理用品等特殊商品不支持无理由退货。</p>
      </section>
      <section>
        <h2>7. 知识产权</h2>
        <p>本平台所有内容（包括但不限于文字、图片、标识、软件）均受知识产权法保护。未经授权，不得复制、修改或分发本平台的任何内容。</p>
      </section>
      <section>
        <h2>8. 免责声明</h2>
        <p>本平台不对因网络故障、系统维护、不可抗力等原因导致的服务中断承担责任。用户因违反本条款而产生的一切后果由用户自行承担。</p>
      </section>
      <section>
        <h2>9. 条款修改</h2>
        <p>本平台保留随时修改本条款的权利。修改后的条款将在平台上公布，继续使用本平台即视为接受修改后的条款。</p>
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
