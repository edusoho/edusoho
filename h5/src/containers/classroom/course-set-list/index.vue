<template>
  <e-panel :title="title" :needFlex="false" :defaulValue="defaulValue">
    <template v-if="courseSets.length">
      <moreMask v-if="!disableMask && (courseSets.length > 5)" :forceShow="true" @maskLoadMore="disableMask = true">
        <template v-for="item in partCourseSets">
          <course :course="item" style="padding-left: 0;padding-right: 0;"></course>
        </template>
      </moreMask>
      <template v-else>
        <template v-for="item in courseSets">
          <course :course="item" style="padding-left: 0;padding-right: 0;"></course>
        </template>
      </template>
    </template>
  </e-panel>
</template>
<script>
import course from '@/containers/components/e-course/e-course';
import moreMask from '@/components/more-mask';

export default {
  name: 'courseSetList',
  components: {
    course,
    moreMask
  },
  props: ['courseSets', 'title', 'defaulValue'],
  data() {
    return {
      maxShowNum: 5,
      disableMask: false,
    };
  },
  computed: {
    partCourseSets() {
      return this.courseSets.slice(0, 5);
    }
  }
}
</script>

