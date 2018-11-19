<template>
  <e-panel :title="title" :needFlex="false">
    <template v-for="item in courseSets">
      <course :course="item" style="padding-left: 0;padding-right: 0;"></course>
    </template>
  </e-panel>
</template>
<script>
import course from '@/containers/components/e-course/e-course';

export default {
  name: 'courseSetList',
  components: {
    course
  },
  props: ['courseSets', 'title'],
}
</script>

