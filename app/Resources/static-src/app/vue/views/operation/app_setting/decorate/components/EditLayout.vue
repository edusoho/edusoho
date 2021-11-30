<template>
  <div class="edit-layout">
    <div class="edit-layout__title">
      <slot name="title" />
    </div>
    <div class="edit-layout__container">
      <slot />
    </div>
  </div>
</template>

<style lang="less" scoped>
.edit-layout {
  &__title {
    padding: 0 26px;
    width: 100%;
    height: 61px;
    line-height: 60px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #f5f5f5;
  }

  &__container {
    padding: 26px 0 60px;
    font-size: 14px;

    .design-editor {
      padding: 0 26px 20px;
      margin-bottom: 20px;
      border-bottom: 1px solid #f5f5f5;
    }

    .design-editor:last-child {
      padding-bottom: 0;
      margin-bottom: 0;
      border-bottom: none;
    }
  }
}
</style>
