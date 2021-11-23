<template>
  <div class="decorate-container">
    <the-header />

    <div class="decorate-main clearfix">
      <aside class="left-choose-container pull-left">
        <div class="component-add-container">
          <component-classify
            v-for="(classify, index) in Classifys"
            :key="index"
            :classify="classify"
            :index="index"
            :current-classify-index="currentClassifyIndex"
            @add-component="handleClickAdd"
            @click.native="handleChangeClassify(index)"
          />
        </div>
      </aside>

      <section class="center-preview-container pull-left">
        <div class="main-preview-container">
          <find-head />

          <component :is="item.type" v-for="(item, index) in components" :key="index" />

          <find-footer />
        </div>
      </section>

      <aside class="right-edit-container pull-left"></aside>
    </div>
  </div>
</template>

<script>
import { Classifys } from './default-config';

import TheHeader from './components/TheHeader.vue';
import ComponentClassify from './components/ComponentClassify.vue';
import FindHead from '../components/FindHead.vue';
import FindFooter from '../components/FindFooter.vue';
import slide_show from '../components/Swiper.vue';

export default {
  components: {
    TheHeader,
    ComponentClassify,
    FindHead,
    FindFooter,
    slide_show
  },

  data() {
    return {
      Classifys,
      currentClassifyIndex: 0,
      components: []
    }
  },

  methods: {
    handleChangeClassify(val) {
      this.currentClassifyIndex = val;
    },

    handleClickAdd(val) {
      this.components.push(val);
    }
  }
}
</script>

<style lang="less" scoped>
.decorate-container {
  width: 100%;
  height: 100%;

  .decorate-main {
    overflow-y: hidden;
    position: relative;
    width: 100%;
    height: calc(100% - 52px);

    .left-choose-container {
      width: 80px;
      height: 100%;
      background: #243042;

      .component-add-container {
        position: relative;
        padding-top: 20px;
        height: 100%;
        user-select: none;
      }
    }

    .center-preview-container {
      overflow-y: auto;
      padding: 40px 0 40px 190px;
      width: calc(100% - 384px - 80px);
      height: 100%;
      background-color: #f5f7fa;
      scrollbar-width: none;
      -ms-overflow-style: none;

      &::-webkit-scrollbar {
        display: none;
      }

      .main-preview-container {
        position: relative;
        margin: 0 auto;
        width: 375px;
        min-height: 90%;
        box-shadow: 0px 1px 3px 1px rgba(0, 0, 0, 0.05);
        background-color: #fafafa;
      }
    }

    .right-edit-container {
      overflow-y: auto;
      width: 384px;
      height: 100%;
      background-color: #fff;
    }
  }
}

</style>
