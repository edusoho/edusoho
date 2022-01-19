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

    // 仅有一个模块时使用
    .single-content-sec {
      padding: 24px;
      // 60px（导航）+ 26px（title）+ 32px（padding）+ 16px（margin-top）= 134px;
      min-height: calc(100vh - 134px);
      background-color: #fff;
    }

    .multiple-content-sec {
      padding: 24px;
      min-height: calc(100vh - 134px);
      background-color: #fff;
    }
  }
}
</style>
