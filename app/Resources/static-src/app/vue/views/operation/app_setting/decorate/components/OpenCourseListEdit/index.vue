<template>
  <edit-layout>
    <template #title>公开课列表设置</template>

    <div class="design-editor">
      open-course-list-edit
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';

export default {
  name: 'OpenCourseListEdit',

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
