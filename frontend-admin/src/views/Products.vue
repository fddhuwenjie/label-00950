<template>
  <div class="products-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>商品管理</span>
          <el-button type="primary" @click="showAddDialog">
            <el-icon><Plus /></el-icon>
            添加商品
          </el-button>
        </div>
      </template>
      
      <!-- 搜索和筛选 -->
      <div class="filter-bar">
        <el-input
          v-model="searchQuery"
          placeholder="搜索商品名称"
          :prefix-icon="Search"
          clearable
          style="width: 180px"
        />
        <el-select v-model="categoryFilter" placeholder="选择分类" clearable style="width: 120px">
          <el-option
            v-for="cat in categories"
            :key="cat.id"
            :label="cat.name"
            :value="cat.name"
          />
        </el-select>
        <el-select v-model="statusFilter" placeholder="库存状态" clearable style="width: 110px">
          <el-option label="有货" value="instock" />
          <el-option label="缺货" value="outofstock" />
        </el-select>
      </div>
      
      <!-- 商品列表 -->
      <el-table :data="paginatedProducts" stripe v-loading="loading">
        <el-table-column type="selection" width="50" />
        <el-table-column label="商品" min-width="280">
          <template #default="{ row }">
            <div class="product-info">
              <div class="product-image">
                <img :src="row.image" :alt="row.name" @error="handleImageError" />
              </div>
              <div class="product-details">
                <span class="product-name">{{ row.name }}</span>
                <span class="product-sku">SKU: {{ row.sku }}</span>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="价格" width="120">
          <template #default="{ row }">
            <span class="price">${{ row.price }}</span>
            <span v-if="row.salePrice" class="sale-price">
              ${{ row.salePrice }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="stock" label="库存" width="100">
          <template #default="{ row }">
            <el-tag :type="row.stock > 0 ? 'success' : 'danger'" size="small">
              {{ row.stock > 0 ? row.stock : '缺货' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="category" label="分类" width="100" />
        <el-table-column label="操作" width="140" fixed="right" align="center">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="editProduct(row)">
              编辑
            </el-button>
            <el-button type="danger" link size="small" @click="handleDeleteProduct(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="pagination">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="filteredProducts.length"
          :page-sizes="[10, 20, 50]"
          layout="total, sizes, prev, pager, next"
          small
        />
      </div>
    </el-card>
    
    <!-- 添加/编辑商品弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="editingProduct ? '编辑商品' : '添加商品'"
      width="650px"
    >
      <el-form :model="productForm" :rules="formRules" ref="formRef" label-width="100px">
        <el-form-item label="商品图片">
          <div class="images-uploader">
            <div class="images-list">
              <div 
                v-for="(img, index) in productForm.images" 
                :key="index"
                class="image-item"
                :class="{ cover: index === 0 }"
              >
                <img :src="img" @error="handleImageError" />
                <div class="image-actions">
                  <span v-if="index === 0" class="cover-tag">封面</span>
                  <el-button 
                    v-if="index > 0" 
                    type="primary" 
                    size="small" 
                    circle 
                    @click="setAsCover(index)"
                    title="设为封面"
                  >
                    <el-icon><Star /></el-icon>
                  </el-button>
                  <el-button 
                    type="danger" 
                    size="small" 
                    circle 
                    @click="removeImage(index)"
                    title="删除"
                  >
                    <el-icon><Delete /></el-icon>
                  </el-button>
                </div>
              </div>
              <div class="upload-area" @click="triggerUpload">
                <el-icon size="24"><Plus /></el-icon>
                <span>添加图片</span>
              </div>
            </div>
            <input 
              type="file" 
              ref="fileInput" 
              accept="image/*" 
              multiple
              @change="handleFileChange"
              style="display: none"
            />
            <div class="upload-tips">
              <p>支持多图上传，第一张为封面图。建议尺寸 800x800</p>
              <div class="url-input">
                <el-input 
                  v-model="imageUrlInput" 
                  placeholder="或输入图片URL后点击添加"
                  size="small"
                />
                <el-button size="small" @click="addImageByUrl">添加</el-button>
              </div>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="商品名称" prop="name">
          <el-input v-model="productForm.name" placeholder="请输入商品名称" />
        </el-form-item>
        <el-form-item label="SKU" prop="sku">
          <el-input v-model="productForm.sku" placeholder="商品编码" />
        </el-form-item>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="价格" prop="price">
              <el-input-number v-model="productForm.price" :min="0" :precision="2" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="促销价">
              <el-input-number v-model="productForm.salePrice" :min="0" :precision="2" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="库存" prop="stock">
              <el-input-number v-model="productForm.stock" :min="0" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="分类" prop="category">
              <el-select v-model="productForm.category" placeholder="选择分类" style="width: 100%">
                <el-option
                  v-for="cat in categories"
                  :key="cat.id"
                  :label="cat.name"
                  :value="cat.name"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="商品描述">
          <el-input v-model="productForm.description" type="textarea" :rows="3" placeholder="请输入商品描述" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveProduct" :loading="saving">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search, Delete, Star } from '@element-plus/icons-vue'
import { productApi, categoryApi } from '@/utils/api'

const loading = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const pageSize = ref(10)
const dialogVisible = ref(false)
const editingProduct = ref(null)
const formRef = ref(null)
const fileInput = ref(null)

const categories = ref([])

// 从 WordPress API 加载商品数据
const products = ref([])

const fetchProducts = async () => {
  loading.value = true
  try {
    const [prods, cats] = await Promise.all([
      productApi.getAll(),
      categoryApi.getAll(),
    ])
    products.value = prods
    if (cats.length > 0) {
      categories.value = cats
    } else {
      categories.value = [
        { id: 1, name: '数码电子' },
        { id: 2, name: '时尚服饰' },
        { id: 3, name: '美妆护肤' },
        { id: 4, name: '家居生活' },
      ]
    }
  } catch (e) {
    console.error('加载商品失败:', e)
    ElMessage.error('加载商品失败')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProducts()
})

const productForm = ref({
  name: '',
  sku: '',
  price: 0,
  salePrice: null,
  stock: 0,
  category: '',
  description: '',
  image: '',
  images: []
})

const imageUrlInput = ref('')

const formRules = {
  name: [
    { required: true, message: '请输入商品名称', trigger: 'blur' },
    { min: 2, message: '名称至少2个字符', trigger: 'blur' }
  ],
  sku: [
    { required: true, message: '请输入SKU', trigger: 'blur' }
  ],
  price: [
    { required: true, message: '请输入价格', trigger: 'blur' }
  ],
  stock: [
    { required: true, message: '请输入库存', trigger: 'blur' }
  ],
  category: [
    { required: true, message: '请选择分类', trigger: 'change' }
  ]
}

const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchSearch = !searchQuery.value || 
      p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.sku.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchCategory = !categoryFilter.value || 
      p.category === categoryFilter.value
    const matchStatus = !statusFilter.value || 
      (statusFilter.value === 'instock' ? p.stock > 0 : p.stock === 0)
    return matchSearch && matchCategory && matchStatus
  })
})

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredProducts.value.slice(start, end)
})

const handleImageError = (e) => {
  e.target.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="%23ccc" stroke-width="1"%3E%3Crect x="3" y="3" width="18" height="18" rx="2" ry="2"/%3E%3Ccircle cx="8.5" cy="8.5" r="1.5"/%3E%3Cpolyline points="21 15 16 10 5 21"/%3E%3C/svg%3E'
}

const showAddDialog = () => {
  editingProduct.value = null
  productForm.value = {
    name: '',
    sku: '',
    price: 0,
    salePrice: null,
    stock: 0,
    category: '',
    description: '',
    image: '',
    images: []
  }
  imageUrlInput.value = ''
  dialogVisible.value = true
}

const editProduct = (product) => {
  editingProduct.value = product
  productForm.value = { 
    ...product,
    images: product.images || (product.image ? [product.image] : [])
  }
  imageUrlInput.value = ''
  dialogVisible.value = true
}

const triggerUpload = () => {
  fileInput.value?.click()
}

const handleFileChange = (event) => {
  const files = event.target.files
  if (!files || files.length === 0) return
  
  Array.from(files).forEach(file => {
    if (!file.type.startsWith('image/')) {
      ElMessage.error(`${file.name} 不是图片文件`)
      return
    }
    
    if (file.size > 5 * 1024 * 1024) {
      ElMessage.error(`${file.name} 大小超过 5MB`)
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      productForm.value.images.push(e.target?.result)
      // 设置第一张为封面
      if (productForm.value.images.length === 1) {
        productForm.value.image = e.target?.result
      }
    }
    reader.onerror = () => {
      ElMessage.error('图片读取失败')
    }
    reader.readAsDataURL(file)
  })
  
  event.target.value = ''
  ElMessage.success('图片上传成功')
}

const addImageByUrl = () => {
  const url = imageUrlInput.value.trim()
  if (!url) {
    ElMessage.warning('请输入图片URL')
    return
  }
  
  productForm.value.images.push(url)
  if (productForm.value.images.length === 1) {
    productForm.value.image = url
  }
  imageUrlInput.value = ''
  ElMessage.success('图片已添加')
}

const removeImage = (index) => {
  productForm.value.images.splice(index, 1)
  // 如果删除的是封面，更新封面为新的第一张
  if (index === 0 && productForm.value.images.length > 0) {
    productForm.value.image = productForm.value.images[0]
  } else if (productForm.value.images.length === 0) {
    productForm.value.image = ''
  }
}

const setAsCover = (index) => {
  const img = productForm.value.images.splice(index, 1)[0]
  productForm.value.images.unshift(img)
  productForm.value.image = img
  ElMessage.success('已设为封面')
}

const saveProduct = async () => {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return
  
  saving.value = true
  
  try {
    // 确保 image 字段是第一张图
    const formData = {
      ...productForm.value,
      image: productForm.value.images[0] || productForm.value.image || 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=200&h=200&fit=crop'
    }
    
    if (editingProduct.value) {
      // 更新商品
      try {
        await productApi.update(editingProduct.value.id, formData)
        ElMessage.success('商品已更新')
        await fetchProducts()
      } catch (e) {
        ElMessage.error('更新失败: ' + e.message)
      }
    } else {
      // 添加商品
      try {
        await productApi.create(formData)
        ElMessage.success('商品已添加')
        await fetchProducts()
      } catch (e) {
        ElMessage.error('添加失败: ' + e.message)
      }
    }
    
    dialogVisible.value = false
  } catch (error) {
    ElMessage.error('保存失败')
  } finally {
    saving.value = false
  }
}

const handleDeleteProduct = async (product) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除商品 "${product.name}" 吗？`,
      '删除确认',
      { type: 'warning' }
    )
    const success = await productApi.delete(product.id)
    if (success) {
      ElMessage.success('商品已删除')
      await fetchProducts()
    } else {
      ElMessage.error('删除失败')
    }
  } catch {
    // 取消操作
  }
}
</script>

<style lang="scss" scoped>
.products-page {
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
  
  .product-info {
    display: flex;
    align-items: center;
    gap: 12px;
    
    .product-image {
      width: 50px;
      height: 50px;
      border-radius: 6px;
      overflow: hidden;
      flex-shrink: 0;
      background: #f5f7fa;
      
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }
    
    .product-details {
      display: flex;
      flex-direction: column;
      min-width: 0;
      
      .product-name {
        font-weight: 500;
        color: #333;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      
      .product-sku {
        font-size: 11px;
        color: #999;
        margin-top: 2px;
      }
    }
  }
  
  .price {
    font-weight: 600;
    color: #333;
    font-size: 13px;
  }
  
  .sale-price {
    display: block;
    color: #f56c6c;
    font-size: 11px;
    text-decoration: line-through;
  }
  
  .pagination {
    margin-top: 16px;
    display: flex;
    justify-content: flex-end;
  }
  
  .images-uploader {
    .images-list {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 12px;
    }
    
    .image-item {
      position: relative;
      width: 100px;
      height: 100px;
      border-radius: 8px;
      overflow: hidden;
      border: 2px solid #eee;
      
      &.cover {
        border-color: #409eff;
      }
      
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      
      .image-actions {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        opacity: 0;
        transition: opacity 0.2s;
        
        .cover-tag {
          position: absolute;
          top: 4px;
          left: 4px;
          background: #409eff;
          color: #fff;
          font-size: 10px;
          padding: 2px 6px;
          border-radius: 4px;
        }
      }
      
      &:hover .image-actions {
        opacity: 1;
      }
    }
    
    .upload-area {
      width: 100px;
      height: 100px;
      border: 2px dashed #dcdfe6;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      color: #909399;
      transition: all 0.2s ease;
      
      &:hover {
        border-color: #409eff;
        color: #409eff;
      }
      
      span {
        font-size: 12px;
      }
    }
    
    .upload-tips {
      p {
        font-size: 12px;
        color: #909399;
        margin: 0 0 8px 0;
      }
      
      .url-input {
        display: flex;
        gap: 8px;
      }
    }
  }
}
</style>
