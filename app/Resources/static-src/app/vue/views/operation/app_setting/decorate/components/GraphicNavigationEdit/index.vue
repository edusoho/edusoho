<template>
  <edit-layout>
    <template #title>图文导航设置</template>

    <div class="design-editor">
      fdsaf
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';

export default {
  name: 'GraphicNavigationEdit',

  props: {
    moduleData: {
      type: Array,
      required: true
    }
  },

  components: {
    EditLayout
  }
}
</script>
