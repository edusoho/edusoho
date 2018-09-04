<template>
  <div class="module-frame" :class="containerClass">
    <!-- 左侧预览区域 -->
    <template>
      <div class="module-frame__preview" :class="{ active: isActive, incomplete: isIncomplete }">
        <slot name="preview"></slot>
      </div>
    </template>
    <!-- 右侧设置区域 -->
    <template>
      <div class="module-frame__setting" v-show="isActive">
        <slot name="setting"></slot>
      </div>
    </template>

    <!-- 模态框 -->
    <slot name="modal"></slot>
  </div>
</template>

<script>
export default {
  props: {
    containerClass: {
      type: String,
      default: ''
    },
    isActive: {
      type: Boolean,
      default: false,
    },
    isIncomplete: {
      type: Boolean,
      default: false,
    }
  },
}
</script>
