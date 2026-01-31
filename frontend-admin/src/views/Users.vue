<template>
  <div class="users-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>用户管理</span>
          <el-button type="primary" @click="showAddDialog">
            <el-icon><Plus /></el-icon>
            添加用户
          </el-button>
        </div>
      </template>
      
      <!-- 搜索 -->
      <div class="filter-bar">
        <el-input
          v-model="searchQuery"
          placeholder="搜索用户名/邮箱"
          :prefix-icon="Search"
          style="width: 180px"
          clearable
        />
        <el-select v-model="roleFilter" placeholder="用户角色" clearable style="width: 110px">
          <el-option label="管理员" value="admin" />
          <el-option label="普通用户" value="customer" />
        </el-select>
      </div>
      
      <!-- 用户列表 -->
      <el-table :data="paginatedUsers" stripe v-loading="loading">
        <el-table-column type="selection" width="50" />
        <el-table-column label="用户" min-width="250">
          <template #default="{ row }">
            <div class="user-info">
              <el-avatar :size="40" :src="row.avatar" icon="UserFilled" />
              <div class="user-details">
                <span class="user-name">{{ row.name }}</span>
                <span class="user-email">{{ row.email }}</span>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="role" label="角色" width="120">
          <template #default="{ row }">
            <el-tag :type="row.role === 'admin' ? 'danger' : ''">
              {{ row.role === 'admin' ? '管理员' : '普通用户' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="orders" label="订单数" width="100" />
        <el-table-column prop="totalSpent" label="消费总额" width="120">
          <template #default="{ row }">
            ${{ row.totalSpent.toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'info'">
              {{ row.status === 'active' ? '活跃' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="registerDate" label="注册时间" width="160" />
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="editUser(row)">
              编辑
            </el-button>
            <el-button 
              :type="row.status === 'active' ? 'warning' : 'success'" 
              link 
              @click="toggleStatus(row)"
            >
              {{ row.status === 'active' ? '禁用' : '启用' }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="pagination">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="filteredUsers.length"
          :page-sizes="[10, 20, 50]"
          layout="total, sizes, prev, pager, next"
        />
      </div>
    </el-card>
    
    <!-- 添加/编辑用户弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="editingUser ? '编辑用户' : '添加用户'"
      width="500px"
    >
      <el-form :model="userForm" label-width="80px">
        <el-form-item label="用户名" required>
          <el-input v-model="userForm.name" />
        </el-form-item>
        <el-form-item label="邮箱" required>
          <el-input v-model="userForm.email" type="email" />
        </el-form-item>
        <el-form-item label="密码" :required="!editingUser">
          <el-input v-model="userForm.password" type="password" show-password />
        </el-form-item>
        <el-form-item label="角色">
          <el-select v-model="userForm.role">
            <el-option label="管理员" value="admin" />
            <el-option label="普通用户" value="customer" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveUser">保存</el-button>
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
const roleFilter = ref('')
const currentPage = ref(1)
const pageSize = ref(10)
const dialogVisible = ref(false)
const editingUser = ref(null)

const users = ref([
  { id: 1, name: 'Admin', email: 'admin@example.com', role: 'admin', orders: 0, totalSpent: 0, status: 'active', registerDate: '2024-01-01', avatar: '' },
  { id: 2, name: 'John Doe', email: 'john@example.com', role: 'customer', orders: 15, totalSpent: 2450.50, status: 'active', registerDate: '2024-01-05', avatar: '' },
  { id: 3, name: 'Jane Smith', email: 'jane@example.com', role: 'customer', orders: 8, totalSpent: 1299.00, status: 'active', registerDate: '2024-01-08', avatar: '' },
  { id: 4, name: '王小明', email: 'xiaoming@example.com', role: 'customer', orders: 3, totalSpent: 267.00, status: 'active', registerDate: '2024-01-10', avatar: '' },
  { id: 5, name: 'Bob Wilson', email: 'bob@example.com', role: 'customer', orders: 0, totalSpent: 0, status: 'disabled', registerDate: '2024-01-12', avatar: '' },
])

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'customer'
})

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchSearch = !searchQuery.value ||
      u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchRole = !roleFilter.value || u.role === roleFilter.value
    return matchSearch && matchRole
  })
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredUsers.value.slice(start, end)
})

const showAddDialog = () => {
  editingUser.value = null
  userForm.value = {
    name: '',
    email: '',
    password: '',
    role: 'customer'
  }
  dialogVisible.value = true
}

const editUser = (user) => {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role
  }
  dialogVisible.value = true
}

const saveUser = () => {
  ElMessage.success(editingUser.value ? '用户已更新' : '用户已添加')
  dialogVisible.value = false
}

const toggleStatus = (user) => {
  user.status = user.status === 'active' ? 'disabled' : 'active'
  ElMessage.success(`用户已${user.status === 'active' ? '启用' : '禁用'}`)
}
</script>

<style lang="scss" scoped>
.users-page {
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
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
  
  .user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    
    .user-details {
      display: flex;
      flex-direction: column;
      
      .user-name {
        font-weight: 500;
        color: #333;
      }
      
      .user-email {
        font-size: 12px;
        color: #999;
        margin-top: 4px;
      }
    }
  }
  
  .pagination {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
  }
}
</style>
