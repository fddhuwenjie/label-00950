<template>
  <div
    class="star-rating"
    :class="{ readonly, interactive: !readonly }"
    :style="{ fontSize: size + 'px' }"
  >
    <span
      v-for="star in 5"
      :key="star"
      class="star"
      :class="{
        active: star <= currentRating,
        half: star - 0.5 <= currentRating && star > currentRating,
        hover: star <= hoverRating && !readonly,
      }"
      @mouseenter="handleMouseEnter(star)"
      @mouseleave="handleMouseLeave"
      @click="handleClick(star)"
    >
      <svg
        viewBox="0 0 24 24"
        fill="currentColor"
      >
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
      </svg>
    </span>
    <span
      v-if="showValue && !readonly"
      class="rating-value"
    >{{ currentRating }}分</span>
    <span
      v-if="showValue && readonly && averageRating"
      class="rating-value"
    >{{ averageRating.toFixed(1) }}</span>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

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
    type: Number,
    default: 20,
  },
  showValue: {
    type: Boolean,
    default: false,
  },
  averageRating: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const hoverRating = ref(0)

const currentRating = computed(() => {
  if (props.readonly) {
    return props.averageRating || props.modelValue
  }
  return hoverRating.value || props.modelValue
})

const handleMouseEnter = (star) => {
  if (!props.readonly) {
    hoverRating.value = star
  }
}

const handleMouseLeave = () => {
  if (!props.readonly) {
    hoverRating.value = 0
  }
}

const handleClick = (star) => {
  if (!props.readonly) {
    emit('update:modelValue', star)
    emit('change', star)
  }
}

watch(() => props.modelValue, (val) => {
  if (!props.readonly) {
    hoverRating.value = 0
  }
})
</script>

<style lang="scss" scoped>
.star-rating {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  color: #e5e7eb;

  &.interactive {
    cursor: pointer;
  }

  .star {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1em;
    height: 1em;
    transition: color 0.15s ease;

    svg {
      width: 100%;
      height: 100%;
    }

    &.active,
    &.hover {
      color: #fbbf24;
    }

    &.half {
      position: relative;
      color: #e5e7eb;

      &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        overflow: hidden;
        color: #fbbf24;
        background: currentColor;
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/%3E%3C/svg%3E") center/contain no-repeat;
                mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/%3E%3C/svg%3E") center/contain no-repeat;
      }
    }
  }

  .rating-value {
    margin-left: 8px;
    font-size: 0.8em;
    font-weight: 600;
    color: #f59e0b;
  }
}
</style>
