<template>
  <div class="app-footer clearfix">
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-find" />
      <div>{{ 'app.footer.find' | trans }}</div>
    </div>
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-study" />
      <div>{{ 'app.footer.learn' | trans }}</div>
    </div>
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-dynamic" />
      <div>{{ 'app.footer.dynamic' | trans }}</div>
    </div>
    <div class="app-footer__item pull-left">
      <svg-icon icon="icon-me" />
      <div>{{ 'app.footer.mine' | trans }}</div>
    </div>
  </div>
</template>

<style lang="less" scoped>
.app-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  padding: 5px 0;
  height: 50px;
  width: 100%;
  background-color: #fff;
  border-top: 1px solid #e1e1e1;

  &__item {
    width: 25%;
    text-align: center;

    .svg-icon {
      font-size: 20px;
    }
  }
}
</style>
