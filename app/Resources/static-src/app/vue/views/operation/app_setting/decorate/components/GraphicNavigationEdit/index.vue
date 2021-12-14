<template>
  <edit-layout>
    <template #title>图文导航设置</template>

    <div class="design-editor">
      <div class="design-editor__item" v-for="(item, index) in moduleData" :key="index">
        <item :item="item" />
      </div>

      <div class="design-editor__item">
        <a-button type="primary" block>
          添加图文导航
        </a-button>
      </div>
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import Item from './Item.vue';

export default {
  name: 'GraphicNavigationEdit',

  props: {
    moduleData: {
      type: Array,
      required: true
    }
  },

  components: {
    EditLayout,
    Item
  }
}
</script>
