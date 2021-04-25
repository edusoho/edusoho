<template>
  <div class="course-detail">
    <e-loading v-if="isLoading" />
    <component :is="currentComp" :details="details" />
    <wx-open-subscribe
      template="gd7YkJSa2zh5k0z7O3PBPMosQmGS6zex8bumXbzHg5U"
      id="subscribe-btn"
    >
      <script type="text/wxtag-template" slot="style">
        <style>
          .subscribe-btn {
            width: 100px;
            height: 100px;
            color: #fff;
            background-color: #07c160;
          }
        </style>
      </script>
      <script type="text/wxtag-template">
        <button class="subscribe-btn">
          一次性模版消息订阅
        </button>
      </script>
    </wx-open-subscribe>
  </div>
</template>

<script>
import joinAfter from './join-after.vue';
import joinBefore from './join-before.vue';
import { mapState, mapActions, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import { Toast } from 'vant';
import initSubscribe from '@/utils/wechat-subscribe.js';

export default {
  components: {},
  inject: ['reload'],
  data() {
    return {
      currentComp: '',
    };
  },
  computed: {
    ...mapState('course', {
      selectedPlanIndex: state => state.selectedPlanIndex,
      joinStatus: state => state.joinStatus,
      details: state => state.details,
      selectedPlanId: state => state.selectedPlanId,
    }),
    ...mapState({
      isLoading: state => state.isLoading,
    }),
  },
  watch: {
    joinStatus: {
      handler: 'joinStatusChange',
    },
    $route(to, from) {
      this.getData();
    },
  },
  created() {
    this.getData();
    this.initSubscribe();
  },
  methods: {
    ...mapActions('course', ['getCourseLessons']),
    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE,
    }),

    initSubscribe,

    getData() {
      this.getCourseLessons({
        courseId: this.$route.params.id,
      }).then(res => {
        if (!res.member && !Number(this.details.parentId)) {
          this.$router.push({
            path: `/goods/${res.goodsId}/show`,
          });
          return;
        }
        console.log(res);
        this.joinStatusChange(res.member);
      });
    },
    joinStatusChange(status) {
      this.currentComp = '';
      if (status) {
        this.currentComp = joinAfter;
      } else {
        this.currentComp = joinBefore;
      }
    },
    // 获取加入后课程目录和学习状态
    getJoinAfter() {
      this.getJoinAfterDetail({
        courseId: this.$route.params.id,
      })
        .then(res => {})
        .catch(err => {
          Toast.fail(err.message);
        });
    },
  },
  beforeRouteLeave(to, from, next) {
    this.setSourceType({
      sourceType: 'img',
      taskId: 0,
    });
    next();
  },
};
</script>
