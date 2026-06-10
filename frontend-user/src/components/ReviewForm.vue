<template>
  <div v-if="visible" class="review-form-modal" @click.self="handleClose">
    <div class="modal-content">
      <div class="modal-header">
        <h3>发表评价</h3>
        <button class="close-btn" @click="handleClose">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <div class="product-info">
          <img :src="product.image" :alt="product.name" class="product-image" />
          <div class="product-meta">
            <span class="product-name">{{ product.name }}</span>
          </div>
        </div>

        <div class="rating-section">
          <label class="section-label">商品评分</label>
          <div class="rating-input">
            <StarRating v-model="rating" interactive size="large" />
            <span class="rating-text">{{ ratingText }}</span>
          </div>
        </div>

        <div class="content-section">
          <label class="section-label">评价内容</label>
          <textarea
            v-model="content"
            placeholder="分享您对这件商品的真实感受吧~"
            rows="4"
            maxlength="500"
          ></textarea>
          <span class="char-count">{{ content.length }}/500</span>
        </div>

        <div class="images-section">
          <label class="section-label">晒图（最多3张）</label>
          <div class="image-uploader">
            <div 
              v-for="(img, idx) in uploadedImages" 
              :key="idx"
              class="uploaded-image"
            >
              <img :src="img.url || img" :alt="`晒图 ${idx + 1}`" />
              <button class="remove-image" @click="removeImage(idx)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <label 
              v-if="uploadedImages.length < 3" 
              class="upload-btn"
            >
              <input 
                type="file" 
                accept="image/*" 
                @change="handleFileSelect"
                multiple
                hidden
              />
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              <span>添加图片</span>
            </label>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-cancel" @click="handleClose">取消</button>
        <button class="btn-submit" :disabled="submitting || !canSubmit" @click="handleSubmit">
          {{ submitting ? '提交中...' : '提交评价' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import StarRating from './StarRating.vue'
import { useReviewStore } from '@/stores/reviews'
import toast from '@/utils/toast'

const props = defineProps({
  visible: Boolean,
  product: {
    type: Object,
    default: () => ({ id: 0, name: '', image: '' })
  },
  orderId: Number,
  orderItemId: Number
})

const emit = defineEmits(['close', 'submitted'])

const reviewStore = useReviewStore()

const rating = ref(5)
const content = ref('')
const uploadedImages = ref([])
const submitting = ref(false)

const ratingTexts = ['', '非常差', '差', '一般', '好', '非常好']
const ratingText = computed(() => ratingTexts[rating.value])

const canSubmit = computed(() => {
  return rating.value >= 1 && content.value.trim().length >= 5
})

const handleFileSelect = async (e) => {
  const files = Array.from(e.target.files)
  const remaining = 3 - uploadedImages.value.length
  const filesToUpload = files.slice(0, remaining)

  for (const file of filesToUpload) {
    if (file.size > 5 * 1024 * 1024) {
      toast.error('图片大小不能超过5MB')
      continue
    }
    try {
      const result = await reviewStore.uploadImage(file)
      uploadedImages.value.push({ url: result.url, id: result.id })
    } catch (err) {
      toast.error('图片上传失败')
    }
  }
  e.target.value = ''
}

const removeImage = (idx) => {
  uploadedImages.value.splice(idx, 1)
}

const handleClose = () => {
  if (!submitting.value) {
    resetForm()
    emit('close')
  }
}

const resetForm = () => {
  rating.value = 5
  content.value = ''
  uploadedImages.value = []
}

const handleSubmit = async () => {
  if (!canSubmit.value) {
    if (content.value.trim().length < 5) {
      toast.error('评价内容至少5个字')
    }
    return
  }

  submitting.value = true
  try {
    await reviewStore.createReview({
      product_id: props.product.id,
      order_id: props.orderId,
      order_item_id: props.orderItemId,
      rating: rating.value,
      content: content.value.trim(),
      images: uploadedImages.value.map(img => img.url)
    })
    toast.success('评价提交成功！')
    emit('submitted')
    resetForm()
    emit('close')
  } catch (err) {
    toast.error(err.message || '提交失败，请重试')
  } finally {
    submitting.value = false
  }
}

watch(() => props.visible, (val) => {
  if (val) {
    resetForm()
  }
})
</script>

<style lang="scss" scoped>
.review-form-modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1500;
  padding: 20px;
}

.modal-content {
  background: #fff;
  border-radius: 24px;
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #f0f0f0;

  h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
  }

  .close-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    color: #666;
    transition: all 0.2s;

    &:hover {
      background: #eee;
      color: #1a1a1a;
    }
  }
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
}

.product-info {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 12px;
  margin-bottom: 24px;

  .product-image {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    object-fit: cover;
  }

  .product-meta {
    flex: 1;

    .product-name {
      display: block;
      font-size: 15px;
      font-weight: 600;
      color: #1a1a1a;
      line-height: 1.4;
    }
  }
}

.section-label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 12px;
}

.rating-section {
  margin-bottom: 24px;

  .rating-input {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: #fafafa;
    border-radius: 12px;

    .rating-text {
      font-size: 16px;
      font-weight: 600;
      color: #f59e0b;
    }
  }
}

.content-section {
  margin-bottom: 24px;

  textarea {
    width: 100%;
    padding: 16px;
    border: 1.5px solid #e0e0e0;
    border-radius: 12px;
    font-size: 15px;
    line-height: 1.6;
    resize: none;
    font-family: inherit;
    transition: border-color 0.2s;

    &:focus {
      outline: none;
      border-color: #6366f1;
    }

    &::placeholder {
      color: #aaa;
    }
  }

  .char-count {
    display: block;
    text-align: right;
    font-size: 12px;
    color: #999;
    margin-top: 6px;
  }
}

.images-section {
  .image-uploader {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .uploaded-image {
    position: relative;
    width: 90px;
    height: 90px;
    border-radius: 10px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .remove-image {
      position: absolute;
      top: 4px;
      right: 4px;
      width: 24px;
      height: 24px;
      background: rgba(0, 0, 0, 0.6);
      border: none;
      border-radius: 50%;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;

      &:hover {
        background: rgba(0, 0, 0, 0.8);
      }
    }
  }

  .upload-btn {
    width: 90px;
    height: 90px;
    border: 2px dashed #d0d0d0;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    cursor: pointer;
    color: #999;
    transition: all 0.2s;

    &:hover {
      border-color: #6366f1;
      color: #6366f1;
    }

    span {
      font-size: 12px;
    }
  }
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #f0f0f0;

  button {
    flex: 1;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
  }

  .btn-cancel {
    background: #f5f5f5;
    border: none;
    color: #666;

    &:hover {
      background: #e8e8e8;
    }
  }

  .btn-submit {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    color: #fff;

    &:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
}

@media (max-width: 768px) {
  .modal-content {
    border-radius: 20px;
    max-height: 85vh;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 16px 20px;
  }

  .images-section .uploaded-image,
  .images-section .upload-btn {
    width: 75px;
    height: 75px;
  }
}
</style>
