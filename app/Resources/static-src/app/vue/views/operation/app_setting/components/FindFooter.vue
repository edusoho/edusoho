<template>
  <div class="app-footer clearfix">
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-check-circle" />
      <div>发现</div>
    </div>
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-check-circle" />
      <div>学习</div>
    </div>
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-check-circle" />
      <div>动态</div>
    </div>
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-check-circle" />
      <div>我的</div>
    </div>
  </div>
</template>

<style lang="less" scoped>
.app-footer {
  padding: 5px 0;
  height: 50px;
  width: 100%;
  background-color: #fff;
  border-top: 1px solid #e1e1e1;

  &__item {
    width: 25%;
    text-align: center;
  }
}
</style>
