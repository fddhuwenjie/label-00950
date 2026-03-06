<template>
  <div class="dashboard">
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stats-row">
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <el-icon><ShoppingCart /></el-icon>
            </div>
            <div class="stat-info">
              <span class="stat-value">{{ stats.todayOrders }}</span>
              <span class="stat-label">今日订单</span>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
              <el-icon><Money /></el-icon>
            </div>
            <div class="stat-info">
              <span class="stat-value">{{ formatCurrency(stats.monthRevenue) }}</span>
              <span class="stat-label">本月销售额</span>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
              <el-icon><Goods /></el-icon>
            </div>
            <div class="stat-info">
              <span class="stat-value">{{ stats.productsCount }}</span>
              <span class="stat-label">商品数量</span>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
              <el-icon><User /></el-icon>
            </div>
            <div class="stat-info">
              <span class="stat-value">{{ stats.usersCount }}</span>
              <span class="stat-label">注册用户</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 图表区域 -->
    <el-row :gutter="20" class="chart-row">
      <el-col :span="16">
        <el-card class="chart-card">
          <template #header>
            <span>销售趋势</span>
          </template>
          <v-chart class="chart" :option="salesChartOption" autoresize />
        </el-card>
      </el-col>
      
      <el-col :span="8">
        <el-card class="chart-card">
          <template #header>
            <span>订单状态分布</span>
          </template>
          <v-chart class="chart" :option="orderPieOption" autoresize />
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 最近订单 -->
    <el-card class="recent-orders">
      <template #header>
        <div class="card-header">
          <span>最近订单</span>
          <el-button type="primary" link @click="$router.push('/orders')">
            查看全部
          </el-button>
        </div>
      </template>
      
      <el-table :data="recentOrders" stripe>
        <el-table-column prop="id" label="订单号" width="100" />
        <el-table-column prop="customer" label="客户" width="150" />
        <el-table-column prop="total" label="金额">
          <template #default="{ row }">
            {{ formatCurrency(row.total) }}
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="date" label="下单时间" width="180" />
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart, PieChart } from 'echarts/charts'
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
} from 'echarts/components'
import { dashboardApi, orderApi } from '@/utils/api'

use([
  CanvasRenderer,
  LineChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
])

const stats = ref({
  todayOrders: 0,
  monthRevenue: 0,
  productsCount: 0,
  usersCount: 0
})

const recentOrders = ref([])

onMounted(async () => {
  try {
    const dashboard = await dashboardApi.get()
    stats.value = {
      todayOrders: dashboard.orders_count || 0,
      monthRevenue: dashboard.revenue || 0,
      productsCount: dashboard.products_count || 0,
      usersCount: dashboard.users_count || 0,
    }
  } catch (e) {
    console.error('加载仪表盘数据失败:', e)
  }

  try {
    const orders = await orderApi.getAll({ per_page: 5 })
    recentOrders.value = orders.map(o => ({
      id: o.number,
      customer: o.billing?.name || '未知',
      total: o.total,
      status: o.status,
      date: o.date,
    }))
  } catch (e) {
    // 使用空数据
  }
})

const salesChartOption = ref({
  tooltip: {
    trigger: 'axis'
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '3%',
    containLabel: true
  },
  xAxis: {
    type: 'category',
    boundaryGap: false,
    data: ['1月', '2月', '3月', '4月', '5月', '6月', '7月']
  },
  yAxis: {
    type: 'value'
  },
  series: [
    {
      name: '销售额',
      type: 'line',
      smooth: true,
      areaStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 0,
          y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(102, 126, 234, 0.5)' },
            { offset: 1, color: 'rgba(102, 126, 234, 0.1)' }
          ]
        }
      },
      lineStyle: {
        color: '#667eea'
      },
      itemStyle: {
        color: '#667eea'
      },
      data: [82000, 93200, 90100, 93400, 129000, 133000, 128560]
    }
  ]
})

const orderPieOption = ref({
  tooltip: {
    trigger: 'item'
  },
  legend: {
    bottom: '5%',
    left: 'center'
  },
  series: [
    {
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: {
        borderRadius: 10,
        borderColor: '#fff',
        borderWidth: 2
      },
      label: {
        show: false,
        position: 'center'
      },
      emphasis: {
        label: {
          show: true,
          fontSize: 20,
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: [
        { value: 48, name: '已完成', itemStyle: { color: '#67c23a' } },
        { value: 23, name: '处理中', itemStyle: { color: '#409eff' } },
        { value: 18, name: '待发货', itemStyle: { color: '#e6a23c' } },
        { value: 11, name: '待付款', itemStyle: { color: '#909399' } }
      ]
    }
  ]
})

const formatCurrency = (value) => {
  return '$' + value.toLocaleString()
}

const getStatusType = (status) => {
  const types = {
    pending: 'info',
    processing: 'warning',
    shipping: '',
    completed: 'success',
    cancelled: 'danger'
  }
  return types[status] || ''
}

const getStatusText = (status) => {
  const texts = {
    pending: '待付款',
    processing: '处理中',
    shipping: '配送中',
    completed: '已完成',
    cancelled: '已取消'
  }
  return texts[status] || status
}

onMounted(() => {
  // 这里可以调用 API 获取真实数据
})
</script>

<style lang="scss" scoped>
.dashboard {
  .stats-row {
    margin-bottom: 20px;
  }
  
  .stat-card {
    .stat-content {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    
    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      
      .el-icon {
        font-size: 28px;
        color: #fff;
      }
    }
    
    .stat-info {
      display: flex;
      flex-direction: column;
      
      .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
      }
      
      .stat-label {
        font-size: 14px;
        color: #999;
        margin-top: 4px;
      }
    }
  }
  
  .chart-row {
    margin-bottom: 20px;
  }
  
  .chart-card {
    .chart {
      height: 300px;
    }
  }
  
  .recent-orders {
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  }
}
</style>
