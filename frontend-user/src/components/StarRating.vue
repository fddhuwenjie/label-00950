<template>
  <div class="star-rating" :class="[`size-${size}`, { interactive: !readonly }]">
    <span
      v-for="i in 5"
      :key="i"
      class="star"
      :class="{ filled: getStarFill(i) === 'full', half: getStarFill(i) === 'half' }"
      @click="handleClick(i)"
      @mousemove="handleHover(i)"
      @mouseleave="handleLeave"
      :role="readonly ? 'img' : 'button'"
      :aria-label="`${i} 星`"
    >
      <svg viewBox="0 0 24 24" width="100%" height="100%">
        <defs>
          <linearGradient :id="halfGradId(i)" x1="0" x2="1" y1="0" y2="0">
            <stop offset="50%" stop-color="#f59e0b" />
            <stop offset="50%" stop-color="#e5e7eb" />
          </linearGradient>
        </defs>
        <path
          :fill="getStarColor(i)"
          d="M12 2l2.9 6.9 7.4.6-5.6 4.9 1.7 7.3L12 17.8 5.6 21.7l1.7-7.3L1.7 9.5l7.4-.6L12 2z"
        />
      </svg>
    </span>
    <span v-if="showText" class="score-text">{{ displayValue.toFixed(1) }}</span>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Number,
    default: 0,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'md', // sm | md | lg
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  showText: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const hoverValue = ref(0)
const instanceId = Math.random().toString(36).slice(2, 9)

const displayValue = computed(() => {
  if (!props.readonly && hoverValue.value > 0) return hoverValue.value
  return props.modelValue || 0
})

const halfGradId = (i) => `star-half-grad-${instanceId}-${i}`

// 返回 'full' | 'half' | 'empty'
const getStarFill = (i) => {
  const v = displayValue.value
  if (v >= i) return 'full'
  if (v >= i - 0.5) return 'half'
  return 'empty'
}

const getStarColor = (i) => {
  const fill = getStarFill(i)
  if (fill === 'full') return '#f59e0b'
  if (fill === 'half') return `url(#${halfGradId(i)})`
  return '#e5e7eb'
}

const handleClick = (i) => {
  if (props.readonly) return
  emit('update:modelValue', i)
  emit('change', i)
}

const handleHover = (i) => {
  if (props.readonly) return
  hoverValue.value = i
}

const handleLeave = () => {
  if (props.readonly) return
  hoverValue.value = 0
}
</script>

<style lang="scss" scoped>
.star-rating {
  display: inline-flex;
  align-items: center;
  gap: 2px;

  .star {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 0;
    color: #e5e7eb;
    transition: transform 0.15s ease;
  }

  &.size-sm .star { width: 14px; height: 14px; }
  &.size-md .star { width: 18px; height: 18px; }
  &.size-lg .star { width: 28px; height: 28px; }

  &.interactive .star {
    cursor: pointer;

    &:hover {
      transform: scale(1.15);
    }
  }

  .score-text {
    margin-left: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #f59e0b;
  }
}
</style>
