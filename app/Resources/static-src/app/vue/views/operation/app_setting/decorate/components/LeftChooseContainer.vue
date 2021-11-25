<template>
  <aside class="left-choose-container pull-left">
    <div class="component-add-container">
      <component-classify
        v-for="(classify, index) in Classifys"
        :key="index"
        :index="index"
        :classify="classify"
        :current-classify="currentClassify"
        @add-component="addComponent"
        @change-classify="changeClassify"
      />
    </div>
  </aside>
</template>

<script>
import { Classifys } from '../default-config';

import ComponentClassify from './ComponentClassify.vue';

export default {
  name: 'LeftChooseContainer',

  components: {
    ComponentClassify
  },

  data() {
    return {
      Classifys,
      currentClassify: 0
    }
  },

  methods: {
    addComponent(info) {
      this.$emit('add-component', info);
    },

    changeClassify(value) {
      this.currentClassify = value;
    }
  }
}
</script>

<style lang="less" scoped>
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
</style>
