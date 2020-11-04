<template>
  <div class="course-reviews">
    <div class="course-reviews__heading">
      我的评价
      <span
        v-if="reviews !== null"
        @click="onResetReviews"
        class="pull-right reset-reviews"
        >重新评价</span
      >
    </div>
    <div class="course-reviews__body">
      <div class="text-center" v-if="reviews === null">
        <van-rate
          v-model="value"
          :size="25"
          color="#ffd21e"
          void-icon="star"
          void-color="#eee"
        />
        <van-field
          style="border: 1px solid #eee; padding: 10px; margin-top: 10px;"
          v-model="message"
          rows="2"
          autosize
          type="textarea"
          maxlength="500"
          placeholder="请输入评价"
          show-word-limit
        />
        <van-button
          type="default"
          size="small"
          style="border-radius: 8px; margin-top: 10px; background: #408ffb; color: #fff;"
          @click="onSubmit"
          >提交评价</van-button
        >
      </div>
      <div class="clearfix" v-else>
        <div class="pull-left course-reviews__avatar">
          <img :src="reviews.user.avatar.large" alt="" />
        </div>
        <div class="pull-left course-reviews__content">
          <div class="reviews-content__header clearfix">
            <span class="reviews-content__header__nickname pull-left">
              {{ reviews.user.nickname }}
            </span>
            <span class="reviews-content__header__time pull-right">
              {{ reviews.createdTime | createdTime }}
            </span>
          </div>
          <div class="reviews-content_rating">
            <van-rate
              class="plan-rate"
              readonly
              :value="reviews.rating * 1"
              gutter="2"
            />
          </div>
          <div class="reviews-content_text">{{ reviews.content }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Api from '@/api';
import { mapState } from 'vuex';

export default {
  name: 'Reviews',
  data() {
    return {
      value: 0,
      message: '',
      reviews: null,
    };
  },
  props: {
    details: {
      type: Object,
      value: () => {},
    },
  },
  watch: {
    details: {
      handler(value) {
        this.reviews = value.myReview;
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    onSubmit() {
      if (this.value == 0) {
        this.$toast('评分不能为空~');
        return;
      }
      if (!this.message.trim()) {
        this.$toast('评价内容不能为空~');
        return;
      }
      let targetId, targetType;
      if (Number(this.details.parentId)) {
        targetType = 'course';
        targetId = this.details.id;
      } else {
        targetType = 'goods';
        targetId = this.details.goodsId;
      }
      const data = {
        targetType,
        targetId,
        content: this.message,
        rating: this.value,
        userId: this.user.id,
      };
      Api.createReview({ data }).then(res => {
        this.reviews = res;
      });
    },
    onResetReviews() {
      this.message = this.reviews.content;
      this.value = this.reviews.rating * 1;
      this.reviews = null;
    },
  },
  computed: {
    ...mapState({
      user: state => state.user,
    }),
  },
  filters: {
    createdTime(date) {
      const reg = new RegExp('-', 'g');
      let time = date.replace(reg, '/');
      time = time.slice(0, -9);
      const hour = time.slice(11, 13);
      let str = '';
      if (hour >= 0 && hour < 6) {
        str = '凌晨';
      } else if (hour >= 6 && hour < 12) {
        str = '上午';
      } else if (hour >= 12 && hour < 18) {
        str = '下午';
      } else if (hour >= 18 && hour < 24) {
        str = '晚上';
      }
      const reg2 = new RegExp('T', 'g');
      time = time.replace(reg2, ' ' + str);
      return time;
    },
  },
};
</script>
