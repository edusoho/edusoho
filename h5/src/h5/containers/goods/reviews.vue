<template>
  <div class="goods-reviews">
    <h3>{{ $t('goods.gradeTheCourse') }}</h3>
    <van-rate
      v-model="value"
      :size="25"
      color="#ffd21e"
      void-icon="star"
      void-color="#D8D8D8"
      gutter="15px"
    />
    <textarea
      name=""
      id=""
      class="goods-reviews__content"
      v-model="content"
    ></textarea>
    <div class="goods-reviews__submit" @click="onSubmit">{{ $t('goods.submit') }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      value: 0,
      content: '',
    };
  },
  methods: {
    onSubmit() {
      console.log(this.value);
      console.log(this.content);
    },
  },
};
</script>
