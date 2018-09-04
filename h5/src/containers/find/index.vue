<template>
  <div class="find-page">
    <e-loading v-if="isLoading"></e-loading>
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
  import courseList from '../components/e-course-list/e-course-list.vue';
  import poster from '../components/e-poster/e-poster.vue';
  import swipe from '../components/e-swipe/e-swipe.vue';
  import * as types from '@/store/mutation-types';
  import Api from '@/api';
  import { mapState } from 'vuex';

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
    computed: {
      ...mapState({
        isLoading: state => state.isLoading
      })
    },
    created() {
      const {preview, token} = this.$route.query

      if (preview == 1) {
        Api.discoveries({
          params: {
            mode: 'draft',
            preview: 1,
            token,
          },
        })
          .then((res) => {
            this.parts = Object.values(res);
          })
          .catch((err) => {
            console.log(err, 'error');
          });
        return;
      }

      Api.discoveries()
        .then((res) => {
          this.parts = Object.values(res);
        })
        .catch((err) => {
          console.log(err, 'error');
        });
    },
  }
</script>
