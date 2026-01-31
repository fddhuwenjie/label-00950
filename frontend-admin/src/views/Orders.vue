<template>
  <div class="orders-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>订单管理</span>
        </div>
      </template>
      
      <!-- 搜索和筛选 -->
      <div class="filter-bar">
        <el-input
          v-model="searchQuery"
          placeholder="搜索订单号/客户名"
          :prefix-icon="Search"
          clearable
          style="width: 180px"
        />
        <el-select v-model="statusFilter" placeholder="订单状态" clearable style="width: 110px">
          <el-option label="待付款" value="pending" />
          <el-option label="处理中" value="processing" />
          <el-option label="配送中" value="shipping" />
          <el-option label="已完成" value="completed" />
          <el-option label="已取消" value="cancelled" />
        </el-select>
      </div>
      
      <!-- 订单列表 -->
      <el-table :data="paginatedOrders" stripe v-loading="loading">
        <el-table-column prop="id" label="订单号" width="120" />
        <el-table-column label="客户信息" min-width="180">
          <template #default="{ row }">
            <div class="customer-info">
              <span class="customer-name">{{ row.customer.name }}</span>
              <span class="customer-email">{{ row.customer.email }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="商品" min-width="200">
          <template #default="{ row }">
            <div class="order-items">
              <span v-for="(item, index) in row.items.slice(0, 2)" :key="index">
                {{ item.name }} x{{ item.qty }}
              </span>
              <span v-if="row.items.length > 2" class="more-items">
                +{{ row.items.length - 2 }} 件商品
              </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="total" label="订单金额" width="120">
          <template #default="{ row }">
            <span class="order-total">${{ row.total.toFixed(2) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)" size="small">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="date" label="下单时间" width="170">
          <template #default="{ row }">
            <span class="order-date">{{ row.date }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="160" fixed="right" align="center">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button type="primary" link size="small" @click="viewOrder(row)">
                详情
              </el-button>
              <el-dropdown trigger="click" @command="(cmd) => handleCommand(cmd, row)">
                <el-button type="primary" link size="small">
                  更多<el-icon class="el-icon--right"><ArrowDown /></el-icon>
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="processing">
                      标记处理中
                    </el-dropdown-item>
                    <el-dropdown-item command="shipping">
                      标记已发货
                    </el-dropdown-item>
                    <el-dropdown-item command="completed">
                      标记已完成
                    </el-dropdown-item>
                    <el-dropdown-item command="cancelled" divided>
                      取消订单
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="pagination">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="filteredOrders.length"
          :page-sizes="[10, 20, 50]"
          layout="total, sizes, prev, pager, next"
        />
      </div>
    </el-card>
    
    <!-- 订单详情弹窗 -->
    <el-dialog v-model="detailVisible" title="订单详情" width="700px">
      <template v-if="selectedOrder">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="订单号">{{ selectedOrder.id }}</el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="getStatusType(selectedOrder.status)">
              {{ getStatusText(selectedOrder.status) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="客户姓名">{{ selectedOrder.customer.name }}</el-descriptions-item>
          <el-descriptions-item label="联系邮箱">{{ selectedOrder.customer.email }}</el-descriptions-item>
          <el-descriptions-item label="收货地址" :span="2">
            {{ selectedOrder.shipping.address }}
          </el-descriptions-item>
          <el-descriptions-item label="下单时间">{{ selectedOrder.date }}</el-descriptions-item>
          <el-descriptions-item label="订单金额">
            <span class="order-total">${{ selectedOrder.total.toFixed(2) }}</span>
          </el-descriptions-item>
        </el-descriptions>
        
        <h4 style="margin: 20px 0 10px;">商品清单</h4>
        <el-table :data="selectedOrder.items" border>
          <el-table-column prop="name" label="商品名称" />
          <el-table-column prop="price" label="单价" width="100">
            <template #default="{ row }">
              ${{ row.price.toFixed(2) }}
            </template>
          </el-table-column>
          <el-table-column prop="qty" label="数量" width="80" />
          <el-table-column label="小计" width="100">
            <template #default="{ row }">
              ${{ (row.price * row.qty).toFixed(2) }}
            </template>
          </el-table-column>
        </el-table>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { Search } from '@element-plus/icons-vue'

const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const pageSize = ref(10)
const detailVisible = ref(false)
const selectedOrder = ref(null)

const orders = ref([
  {
    id: 'ORD-10086',
    customer: { name: 'John Doe', email: 'john@example.com' },
    items: [
      { name: 'iPhone 15 Pro', price: 999, qty: 1 },
      { name: '保护壳', price: 29.99, qty: 2 }
    ],
    total: 1058.98,
    status: 'processing',
    date: '2024-01-15 14:30:00',
    shipping: { address: '123 Main St, New York, NY 10001, USA' }
  },
  {
    id: 'ORD-10085',
    customer: { name: 'Jane Smith', email: 'jane@example.com' },
    items: [
      { name: 'MacBook Air', price: 1299, qty: 1 }
    ],
    total: 1299.00,
    status: 'completed',
    date: '2024-01-15 13:20:00',
    shipping: { address: '456 Oak Ave, Los Angeles, CA 90001, USA' }
  },
  {
    id: 'ORD-10084',
    customer: { name: '王小明', email: 'xiaoming@example.com' },
    items: [
      { name: '护肤套装', price: 89, qty: 1 }
    ],
    total: 89.00,
    status: 'pending',
    date: '2024-01-15 12:15:00',
    shipping: { address: '北京市朝阳区xxx街道xxx号' }
  },
  {
    id: 'ORD-10083',
    customer: { name: 'Bob Wilson', email: 'bob@example.com' },
    items: [
      { name: 'AirPods Pro', price: 249, qty: 1 },
      { name: 'Apple Watch', price: 399, qty: 1 }
    ],
    total: 648.00,
    status: 'shipping',
    date: '2024-01-15 11:00:00',
    shipping: { address: '789 Pine St, Chicago, IL 60601, USA' }
  },
  {
    id: 'ORD-10082',
    customer: { name: '李华', email: 'lihua@example.com' },
    items: [
      { name: '智能手表', price: 199.99, qty: 1 }
    ],
    total: 199.99,
    status: 'cancelled',
    date: '2024-01-15 10:30:00',
    shipping: { address: '上海市浦东新区xxx路xxx号' }
  }
])

const filteredOrders = computed(() => {
  return orders.value.filter(o => {
    const matchSearch = !searchQuery.value ||
      o.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      o.customer.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchStatus = !statusFilter.value || o.status === statusFilter.value
    return matchSearch && matchStatus
  })
})

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredOrders.value.slice(start, end)
})

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

const viewOrder = (order) => {
  selectedOrder.value = order
  detailVisible.value = true
}

const handleCommand = (command, order) => {
  order.status = command
  ElMessage.success(`订单状态已更新为: ${getStatusText(command)}`)
}

const exportOrders = () => {
  ElMessage.success('订单导出功能开发中...')
}
</script>

<style lang="scss" scoped>
.orders-page {
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 18px;
    font-weight: 600;
  }
  
  .filter-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    align-items: center;
  }
  
  :deep(.el-table) {
    .el-table__header-wrapper {
      th.el-table__cell {
        background-color: #f5f7fa;
      }
    }
  }
  
  .order-date {
    white-space: nowrap;
    font-size: 13px;
  }
  
  .customer-info {
    display: flex;
    flex-direction: column;
    
    .customer-name {
      font-weight: 500;
      color: #333;
    }
    
    .customer-email {
      font-size: 12px;
      color: #999;
      margin-top: 4px;
    }
  }
  
  .order-items {
    display: flex;
    flex-direction: column;
    font-size: 13px;
    
    .more-items {
      color: #409eff;
      font-size: 12px;
    }
  }
  
  .order-total {
    font-weight: 600;
    color: #f56c6c;
  }
  
  .action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    
    :deep(.el-button) {
      margin: 0;
      padding: 4px 8px;
    }
  }
  
  .pagination {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
  }
}
</style>
