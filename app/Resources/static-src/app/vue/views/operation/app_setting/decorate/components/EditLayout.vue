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

      &__item {
        margin-bottom: 24px;
      }

      &__label {
        color: #666;
      }

      &__required {
        position: relative;

        &::after {
          content: "*";
          position: absolute;
          top: -4px;
          left: -8px;
          color: red;
          font-size: 18px;
        }
      }

      &__tips {
        padding: 8px;
        background: rgba(237, 237, 237, 0.53);
        font-size: 12px;
        color: #666;
      }
    }

    .design-editor:last-child {
      padding-bottom: 0;
      margin-bottom: 0;
      border-bottom: none;
    }
  }
}

/deep/ label {
  font-weight: normal !important;
}
</style>
