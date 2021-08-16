<template>
  <div class="course-detail">
    <e-loading v-if="isLoading" />
    <join-after :details="details" />
  </div>
</template>

<script>
import joinAfter from './join-after.vue';
import { mapState, mapActions, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';

export default {
  components: {
    joinAfter
  },

  computed: {
    ...mapState('course', {
      details: state => state.details,
    }),

    ...mapState({
      isLoading: state => state.isLoading,
    })
  },

  watch: {
    $route(to, from) {
      this.getData();
    }
  },

  created() {
    this.getData();
  },

  methods: {
    ...mapActions('course', ['getCourseLessons']),

    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE,
    }),

    getData() {
      this.getCourseLessons({
        courseId: this.$route.params.id,
      }).then(res => {
        if (!res.member && !Number(this.details.parentId)) {
          this.$router.push({
            path: `/goods/${res.goodsId}/show`
          });
        }
      });
    },

    // 获取加入后课程目录和学习状态
    getJoinAfter() {
      this.getJoinAfterDetail({
        courseId: this.$route.params.id,
      }).catch(err => {
        this.$toast.fail(err.message);
      });
    }
  },

  beforeRouteLeave(to, from, next) {
    this.setSourceType({
      sourceType: 'img',
      taskId: 0
    });
    next();
  }
};
</script>
