<template>
  <div
    :class="['star-rating', { interactive }]"
    :style="{ fontSize: starSize + 'px' }"
  >
    <div class="stars">
      <span
        v-for="star in maxStars"
        :key="star"
        class="star"
        @click="handleClick(star)"
        @mouseenter="hoverValue = star"
        @mouseleave="hoverValue = 0"
      >
        <svg
          :width="starSize"
          :height="starSize"
          viewBox="0 0 24 24"
        >
          <defs>
            <linearGradient :id="'half-' + star">
              <stop
                offset="50%"
                stop-color="#f59e0b"
              />
              <stop
                offset="50%"
                stop-color="#d1d5db"
              />
            </linearGradient>
          </defs>
          <path
            v-if="getStarType(star) === 'full'"
            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
            fill="#f59e0b"
          />
          <path
            v-else-if="getStarType(star) === 'half'"
            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
            :fill="'url(#half-' + star + ')'"
          />
          <path
            v-else
            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
            fill="#d1d5db"
          />
        </svg>
      </span>
    </div>
    <span
      v-if="showValue"
      class="rating-value"
    >{{ displayValue }}</span>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Number,
    default: 0
  },
  maxStars: {
    type: Number,
    default: 5
  },
  interactive: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (val) => ['sm', 'md', 'lg'].includes(val)
  },
  showValue: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const hoverValue = ref(0)

const sizeMap = { sm: 16, md: 20, lg: 28 }

const starSize = computed(() => sizeMap[props.size] || 20)

const activeValue = computed(() => {
  if (props.interactive && hoverValue.value > 0) return hoverValue.value
  return props.modelValue
})

const displayValue = computed(() => props.modelValue)

const getStarType = (star) => {
  const value = activeValue.value
  if (value >= star) return 'full'
  if (value >= star - 0.5) return 'half'
  return 'empty'
}

const handleClick = (star) => {
  if (!props.interactive) return
  emit('update:modelValue', star)
}
</script>

<style lang="scss" scoped>
.star-rating {
  display: inline-flex;
  align-items: center;
  gap: 8px;

  &.interactive {
    .stars {
      cursor: pointer;
    }

    .star {
      &:hover svg path {
        transition: fill 0.15s ease;
      }
    }
  }
}

.stars {
  display: inline-flex;
  align-items: center;
  gap: 2px;
}

.star {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;

  svg {
    display: block;
  }
}

.star-rating:not(.interactive) {
  .stars {
    pointer-events: none;
  }
}

.rating-value {
  font-weight: 600;
  color: #f59e0b;
  font-size: inherit;
  line-height: 1;
}
</style>
