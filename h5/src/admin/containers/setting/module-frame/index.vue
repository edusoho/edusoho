<template>
  <div :class="containerClass" class="module-frame">
    <!-- 左侧预览区域 -->
    <template>
      <div :class="{ active: isActive, incomplete: isIncomplete }" class="module-frame__preview">
        <slot name="preview"/>
      </div>
    </template>
    <!-- 右侧设置区域 -->
    <template>
      <div v-show="isActive" class="module-frame__setting">
        <slot name="setting"/>
      </div>
    </template>

    <!-- 模态框 -->
    <slot name="modal"/>
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
      default: false
    },
    isIncomplete: {
      type: Boolean,
      default: false
    }
  }
}
</script>
