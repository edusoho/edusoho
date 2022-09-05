<template>
  <div class="app-footer clearfix">
    <div class="app-footer__item pull-left">
      <img style="width: 27px;" src="/static-dist/app/img/vue/decorate/navbar/home.png" />
      <div style="color: #3DCD7F;">{{ 'app.footer.find' | trans }}</div>
    </div>
    <div class="app-footer__item pull-left">
      <img style="width: 27px;" src="/static-dist/app/img/vue/decorate/navbar/learn.png" />
      <div>{{ 'app.footer.learn' | trans }}</div>
    </div>
    <div class="app-footer__item pull-left">
      <img style="width: 27px;" src="/static-dist/app/img/vue/decorate/navbar/my.png" />
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
  height: 55px;
  width: 100%;
  background-color: #fff;
  border-top: 1px solid #e1e1e1;

  &__item {
    width: 33.33%;
    text-align: center;

    .svg-icon {
      font-size: 20px;
    }
  }
}
</style>
