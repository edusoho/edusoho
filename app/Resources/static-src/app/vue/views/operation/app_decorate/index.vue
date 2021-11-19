<template>
  <div class="app-find clearfix" :style="{ height: getMaxHeight + 'px' }">
    <div class="app-find__left pull-left" :style="{ transform: 'scale(' + getScale + ')' }">
      <img class="app-find-box" src="/static-dist/app/img/vue/phone.png">
      <div class="app-find-container">

      </div>
    </div>
    <div class="app-find__right pull-left">
      <h4>注：使用发现页配置功能，app版本需要升级到4.5.39及以上</h4>
      <a-button class="mt16" type="primary">编辑</a-button>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    getMaxHeight() {
      return $(window).height() - 230;
    },

    getScale() {
      return this.getMaxHeight / 867;
    }
  }
}
</script>

<style lang="less" scoped>
.app-find {
  overflow: hidden;

  &__left {
    position: relative;
    width: 434px;
    transform-origin: left top;

    .app-find-box {
      width: 100%;
      height: 100%;
    }

    .app-find-container {
      position: absolute;
      width: 375px;
      top: 60px;
      bottom: 60px;
      left: 30px;
      background-color: red;
    }
  }

  &__right {
    margin-top: 100px;
    width: calc(100% - 450px);
  }
}
</style>
