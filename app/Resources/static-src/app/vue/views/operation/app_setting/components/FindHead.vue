<template>
  <div class="app-header">
    <img src="/static-dist/app/img/vue/app_head.jpeg" />
    <div class="app-header__search">
      <a-input placeholder="阔知学院">
        <a-icon slot="prefix" type="search" />
      </a-input>
    </div>
  </div>
</template>

<style lang="less" scoped>
.app-header {
  position: relative;
  width: 100%;

  img {
    width: 100%;
  }

  &__search {
    position: absolute;
    width: 100%;
    bottom: 4px;
    padding: 0 16px;

    /deep/ .ant-input {
      border-radius: 24px;
    }
  }
}
</style>
