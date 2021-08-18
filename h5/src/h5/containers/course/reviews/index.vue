<template>
  <div class="reviews">
    <component
      :is="currentComponent"
      :user-review="userReview"
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

  data() {
    return {
      currentComponent: 'List',
      userReview: {}
    }
  },

  computed: {
    ...mapState({
      user: state => state.user
    })
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
          targetId: this.courseId,
          targetType: 'course',
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
