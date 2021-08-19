<template>
  <div class="reviews">
    <component
      :is="currentComponent"
      :user-review="userReview"
      :target-info="targetInfo"
      @change-current-component="changeCurrentComponent"
    />
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import { mapState } from 'vuex';
import List from './list.vue';
import Create from './create.vue';

export default {
  name: 'Reviews',

  components: {
    List,
    Create
  },

  props: {
    details: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      currentComponent: 'List',
      userReview: {},
      courseId: this.$route.params.id
    }
  },

  computed: {
    ...mapState({
      user: state => state.user
    }),

    // 课程，取商品页评价, targetType: goods, targetId: goodsId
    // 班级课程, 取课程评价, targetType: course, targetId: courseId
    targetInfo() {
      const { classroom, goodsId } = this.details;

      const targetType = classroom ? 'course' : 'goods';
      const targetId = classroom ? this.courseId : goodsId;

      return {
        targetType,
        targetId
      };
    }
  },

  watch: {
    currentComponent() {
      this.$emit('chang-tabs-status', this.currentComponent === 'List');
    }
  },

  created() {
    this.getUserReviews();
  },

  methods: {
    changeCurrentComponent(params) {
      const { component, data } = params;

      _.assign(this, {
        currentComponent: component
      });

      data && (this.userReview = data);
    },

    getUserReviews() {
      Api.getReview({
        params: {
          ...this.targetInfo,
          userId: this.user.id
        }
      }).then(res => {
        const { data } = res;
        if (_.size(data)) {
          this.userReview = data[0];
        }
      });
    },
  }
}
</script>
