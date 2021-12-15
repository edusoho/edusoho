<template>
  <edit-layout>
    <template #title>班级列表设置</template>

    <div class="design-editor">
      classroom-list-edit
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';

export default {
  name: 'ClassroomListEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout
  }
}
</script>
