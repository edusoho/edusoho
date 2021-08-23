<template>
  <div class="review-item">
    <div class="user-avatar">
      <img :src="item.user.avatar.small">
    </div>
    <div class="review-detail">
      <div class="review-detail__rate">
        <div class="nickname">{{ item.user.nickname }}</div>
        <van-rate
          :value="item.rating * 1"
          :size="16"
          color="#ffd21e"
          void-icon="star"
          void-color="#eee"
        />
      </div>
      <div class="review-detail__time">{{ item.createdTime | formatCourseTime }}</div>
      <div
        ref="content"
        class="review-detail__content"
        :class="classObject"
        v-html="item.content"
      />
      <div class="all-content-btn" v-if="isAllBtn" @click="handleClickToggleOverflow">{{ allConentBtnText }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReviewItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      isOverflow: true,
      isAllBtn: false
    }
  },

  computed: {
    allConentBtnText() {
      return this.isOverflow ? this.$t('courseLearning.showAll') : this.$t('courseLearning.collapse');
    },

    classObject() {
      return {
        overflow: this.isOverflow,
        mask: this.isAllBtn && this.isOverflow
      }
    }
  },

  mounted() {
    this.checkOverFlow();
  },

  methods: {
    checkOverFlow() {
      const el = this.$refs.content;

      this.isAllBtn = el.scrollHeight > el.clientHeight;
    },

    handleClickToggleOverflow() {
      this.isOverflow = !this.isOverflow;
    }
  }
}
</script>

<style lang="scss" scoped>
.review-item {
  display: flex;
  padding: vw(16) 0;
  margin: 0 vw(16);
  border-bottom: 1px solid #f5f5f5;

  .user-avatar {
    flex-shrink: 0;
    margin-right: vw(4);
    width: vw(42);
    height: vw(42);

    img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
    }
  }

  .review-detail {
    flex-grow: 1;

    &__rate {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .nickname {
        font-size: vw(14);
        font-weight: 500;
        color: #333;
        line-height: vw(20);
      }
    }

    &__time {
      margin-top: vw(4);
      font-size: vw(12);
      color: #999;
      line-height: vw(16);
    }

    &__content {
      margin-top: vw(4);
      font-size: vw(14);
      color: #333;
      line-height: vw(20);

      &.overflow {
        position: relative;
        @include text-overflow(3);
      }

      &.mask:after {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        content: '';
        background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, #fff 100%);
      }
    }

    .all-content-btn {
      margin-top: vw(8);
      text-align: right;
      font-size: vw(12);
      color: #999;
      line-height: vw(16);
    }
  }
}
</style>
