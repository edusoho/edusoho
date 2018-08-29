<template>
  <div class="find-page">
    <e-loading v-if="isLoading"></e-loading>
    <div class="find-page__part" v-for="part in parts">
      <e-swipe v-if="part.type == 'slide_show'" :slides="part.data"></e-swipe>
      <e-course-list v-if="part.type == 'course_list'" :courseList="part.data"></e-course-list>
      <e-poster v-if="part.type == 'poster'" :poster="part.data"></e-poster>
    </div>
    <!-- 垫底的 -->
    <div class="mt50"></div>
  </div>
</template>

<script>
  import courseList from '../components/e-course-list/e-course-list.vue';
  import poster from '../components/e-poster/e-poster.vue';
  import swipe from '../components/e-swipe/e-swipe.vue';
  import Api from '@/api';
  import { mapState } from 'vuex';

  export default {
    components: {
      'e-course-list': courseList,
      'e-swipe': swipe,
      'e-poster': poster,
    },
    data () {
      return {
        parts: [],
        coursesList: [],
      };
    },
    computed: {
      ...mapState({
        isLoading: state => state.isLoading
      })
    },
    created() {
      Api.discoveries()
        .then((data) => {
          this.parts = data;
        })
        .catch((err) => {
          console.log(err, 'error');
        });
    },
  }
</script>
