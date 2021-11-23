<template>
  <div class="app-header">
    <img src="/static-dist/app/img/vue/app_head.png" />
    <div class="app-header__search clearfix">
      <div class="school-title pull-left">网校名称</div>
      <a-input class="pull-right" placeholder="搜索课程、班级">
        <a-icon slot="prefix" type="search" />
      </a-input>
    </div>
  </div>
</template>

<style lang="less" scoped>
.app-header {
  margin-bottom: 12px;
  width: 100%;

  img {
    width: 100%;
  }

  &__search {
    padding: 0 16px;

    .school-title {
      font-size: 18px;
      color: #333;
      font-weight: 600;
      line-height: 28px;
    }

    /deep/ .ant-input-affix-wrapper {
      width: 184px;
      .ant-input {
        height: 28px;
        border-radius: 24px;
      }
    }
  }
}
</style>
