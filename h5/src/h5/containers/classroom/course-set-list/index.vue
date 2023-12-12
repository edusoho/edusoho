<template>
  <e-panel class="relative" :title="title" :need-flex="false" :defaul-value="defaulValue">
    <div class="absolute top-32 right-0 flex items-center bg-fill-7 py-4 px-12 rounded-full" @click="goSearch">
      <IconSearch />
      <label class="ml-4 text-text-6">{{ $t('e.searchCourse') }}</label>
    </div>
    <template v-if="courseSets.length">
      <moreMask
        v-if="!disableMask && courseSets.length > 5"
        :force-show="true"
        @maskLoadMore="loadMore"
      >
        <template v-for="item in partCourseSets">
          <course
            :feedback="feedback"
            :course="item"
            :classroom="details"
            style="padding-left: 0;padding-right: 0;"
          />
        </template>
      </moreMask>
      <template v-else>
        <template v-for="item in courseSets">
          <course
            :feedback="feedback"
            :course="item"
            :classroom="details"
            style="padding-left: 0;padding-right: 0;"
          />
        </template>
      </template>
    </template>
    <div class="empty-course" v-if="courseSets.length <= 0">
        <img class="empty-course__img" src="static/images/classroom/none-course.png" alt="" />
      <p class="empty-course__text">{{ $t('more.noCourses') }}</p>
      </div>
  </e-panel>
</template>
<script>
import course from '&/components/e-course/e-course';
import moreMask from '@/components/more-mask';
import IconSearch from '&/components/IconSvg/IconSearch.vue'

export default {
  name: 'CourseSetList',
  components: {
    course,
    moreMask,
    IconSearch
  },
  props: {
    courseSets: {
      default: null,
    },
    classId: {
      type: String,
      default: ''
    },
    title: {
      default: '',
    },
    defaulValue: {
      default: '',
    },
    disableMask: {
      type: Boolean,
      default: false,
    },
    feedback: {
      type: Boolean,
      default: true,
    },
    details: {
      type: Object,
      default: {}
    },
  },
  data() {
    return {
      maxShowNum: 5,
    };
  },
  computed: {
    partCourseSets() {
      return this.courseSets.slice(0, 5);
    },
  },
  methods: {
    loadMore() {
      this.$emit('update:disableMask', true);
    },
    goSearch() {
      this.$router.push({ path: '/search', query: { id: this.classId } });
    }
  },
};
</script>
