<template>
  <edit-layout>
    <template #title>课程列表设置</template>

    <div class="design-editor">
      course-list-edit
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';

export default {
  name: 'CourseListEdit',

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
