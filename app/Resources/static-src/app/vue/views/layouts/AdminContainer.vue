<template>
  <div class="aside-container">
    <div class="aside-header">
      <div class="aside-header__title">
        <slot name="title"></slot>
      </div>
    </div>
    <div class="aside-content">
      <slot></slot>
    </div>
  </div>
</template>

<style lang="less" scoped>
.aside-container {
  margin-top: -10px;
  padding: 16px;

  .aside-header {
    &__title {
      position: relative;
      padding-left: 22px;
      font-size: 16px;
      color: #333;
      letter-spacing: 0;
      line-height: 26px;

      &::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 8px;
        transform: translateY(-50%);
        width: 2px;
        height: 14px;
        background: #1890ff;
        border-radius: 1px;
      }
    }
  }

  .aside-content {
    margin-top: 16px;
  }
}
</style>
