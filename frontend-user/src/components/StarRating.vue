<template>
  <div class="star-rating" :class="{ interactive, readonly: !interactive, [`size-${size}`]: true }">
    <span
      v-for="star in 5"
      :key="star"
      class="star"
      :class="{ filled: star <= modelValue, 'half-filled': showHalf && star === Math.ceil(modelValue) && modelValue % 1 !== 0 }"
      @click="interactive && $emit('update:modelValue', star)"
      @mouseenter="interactive && (hoverValue = star)"
      @mouseleave="interactive && (hoverValue = 0)"
    >
      <svg v-if="!(showHalf && star === Math.ceil(modelValue) && modelValue % 1 !== 0)" width="100%" height="100%" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
      </svg>
      <svg v-else width="100%" height="100%" viewBox="0 0 24 24">
        <defs>
          <linearGradient :id="`half-star-${uid}`">
            <stop offset="50%" stop-color="currentColor"/>
            <stop offset="50%" stop-color="#e0e0e0"/>
          </linearGradient>
        </defs>
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" :fill="`url(#half-star-${uid})`"/>
      </svg>
    </span>
    <span v-if="showValue" class="rating-value">{{ displayValue.toFixed(1) }}</span>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Number,
    default: 0
  },
  interactive: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'medium',
    validator: (v) => ['small', 'medium', 'large'].includes(v)
  },
  showValue: {
    type: Boolean,
    default: false
  },
  showHalf: {
    type: Boolean,
    default: true
  }
})

defineEmits(['update:modelValue'])

const uid = ref(Math.random().toString(36).slice(2, 9))
const hoverValue = ref(0)

const displayValue = computed(() => {
  return hoverValue.value > 0 ? hoverValue.value : props.modelValue
})
</script>

<style lang="scss" scoped>
.star-rating {
  display: inline-flex;
  align-items: center;
  gap: 2px;

  &.interactive .star {
    cursor: pointer;
  }

  .star {
    color: #e0e0e0;
    transition: color 0.15s ease, transform 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;

    &.filled {
      color: #fbbf24;
    }

    &:hover {
      transform: scale(1.15);
    }
  }

  .rating-value {
    margin-left: 6px;
    font-size: 0.9em;
    font-weight: 600;
    color: #fbbf24;
  }

  &.size-small {
    .star {
      width: 16px;
      height: 16px;
    }
    .rating-value {
      font-size: 12px;
    }
  }

  &.size-medium {
    .star {
      width: 20px;
      height: 20px;
    }
    .rating-value {
      font-size: 14px;
    }
  }

  &.size-large {
    .star {
      width: 28px;
      height: 28px;
    }
    gap: 4px;
    .rating-value {
      font-size: 18px;
      margin-left: 10px;
    }
  }

  &.readonly .star {
    cursor: default;
    &:hover {
      transform: none;
    }
  }
}
</style>
