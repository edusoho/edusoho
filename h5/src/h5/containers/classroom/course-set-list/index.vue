<template>
  <e-panel :title="title" :need-flex="false" :defaul-value="defaulValue">
    <template v-if="courseSets.length">
      <moreMask v-if="!disableMask && (courseSets.length > 5)" :force-show="true" @maskLoadMore="loadMore">
        <template v-for="item in partCourseSets">
          <course :feedback="feedback" :course="item" style="padding-left: 0;padding-right: 0;"/>
        </template>
      </moreMask>
      <template v-else>
        <template v-for="item in courseSets">
          <course :feedback="feedback" :course="item" style="padding-left: 0;padding-right: 0;"/>
        </template>
      </template>
    </template>
  </e-panel>
</template>
<script>
import course from '&/components/e-course/e-course'
import moreMask from '@/components/more-mask'

export default {
  name: 'CourseSetList',
  components: {
    course,
    moreMask
  },
  props: {
    courseSets: {
      default: null
    },
    title: {
      default: ''
    },
    defaulValue: {
      default: ''
    },
    disableMask: {
      type: Boolean,
      default: false
    },
    feedback: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      maxShowNum: 5
    }
  },
  computed: {
    partCourseSets() {
      return this.courseSets.slice(0, 5)
    }
  },
  methods: {
    loadMore() {
      this.$emit('update:disableMask', true)
    }
  }
}
</script>

