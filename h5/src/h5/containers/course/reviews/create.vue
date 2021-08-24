<template>
  <div class="review-create">
    <div class="create-header">
      <van-icon
        name="cross"
        size="18px"
        color="#000"
        @click="handleClickGoToList"
      />
      <h3 class="create-header__title">{{ $t('courseLearning.evaluation') }}</h3>
      <span class="create-header__btn" @click="createReview">{{ $t('courseLearning.publish') }}</span>
    </div>

    <van-form ref="form">
      <div class="review-create__rate">
        <van-rate
          v-model="rating"
          :size="24"
          color="#ffd21e"
          void-icon="star"
          void-color="#eee"
        />
        <p v-if="rateHasError" class="error-message">{{ $t('courseLearning.scoring') }}</p>
      </div>

      <div class="review-create__content">
        <van-field
          v-model="content"
          type="textarea"
          rows="3"
          autosize
          :placeholder="$t('courseLearning.evaluationContent')"
          :rules="[{ required: true, message: $t('courseLearning.inputEvaluationContent') }]"
        />
      </div>
    </van-form>
  </div>
</template>

<script>
import Api from '@/api';

export default {
  name: 'ReviewCreate',

  props: {
    userReview: {
      type: Object,
      required: true
    },

    targetInfo: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      rating: this.userReview.rating * 1 || 0,
      content: this.userReview.content || '',
      rateHasError: false
    }
  },

  methods: {
    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    },

    createReview() {
      this.rateHasError = !this.rating;
      this.$refs.form.validate().then(async () => {
        if (this.rateHasError) return;

        const result = await Api.createReview({
          data: {
            ...this.targetInfo,
            content: this.content,
            rating: this.rating
          }
        });

        this.$emit('change-current-component', { component: 'List', data: result });
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.review-create {

  .create-header {
    position: relative;
    padding: vw(16);
    text-align: center;

    .van-icon {
      position: absolute;
      left: vw(16);
      top: 50%;
      transform: translateY(-50%);
    }


    &__title {
      margin: 0;
      font-size: vw(16);
      font-weight: 500;
      color: #333;
      line-height: vw(24);
    }

    &__btn {
      position: absolute;
      right: vw(16);
      top: 50%;
      transform: translateY(-50%);
      font-size: 16px;
      color: $primary-color;
      line-height: 24px;
    }
  }

  &__rate {
    text-align: center;

    .error-message {
      font-size: vw(12);
      color: #ee0a24;
    }
  }

  &__content {
    padding: vw(16);

    .van-cell {
      background: #f5f5f5;
      border-radius: vw(8);
    }
  }
}
</style>
