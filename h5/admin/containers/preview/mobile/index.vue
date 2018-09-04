<template>
  <div class="find-page">
    <div class="find-page__part" v-for="part in parts">
      <e-swipe v-if="part.type == 'slide_show'" :slides="part.data"></e-swipe>
      <e-course-list v-if="part.type == 'course_list'" :courseList="part.data" :feedback="feedback"></e-course-list>
      <e-poster v-if="part.type == 'poster'" :poster="part.data" :feedback="feedback"></e-poster>
    </div>
    <!-- 垫底的 -->
    <div class="mt50"></div>
  </div>
</template>

<script>
  import courseList from '@/containers/components/e-course-list/e-course-list.vue';
  import poster from '@/containers/components/e-poster/e-poster.vue';
  import swipe from '@/containers/components/e-swipe/e-swipe.vue';
  import { mapActions } from 'vuex';

  export default {
    components: {
      'e-course-list': courseList,
      'e-swipe': swipe,
      'e-poster': poster,
    },
    props: {
      feedback: {
        type: Boolean,
        default: true,
      },
    },
    data() {
      return {
        parts: [],
      };
    },
    created() {
      this.getDraft({
        portal: 'h5',
        type: 'discovery',
        mode: 'draft',
      }).then((res) => {
        this.parts = Object.values(res);
      }).catch((err) => {
        console.log(err, 'error');
      });
    },
    methods: {
       ...mapActions([
        'getDraft',
      ]),
    }
  }
</script>
