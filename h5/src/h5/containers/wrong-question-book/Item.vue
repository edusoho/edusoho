<template>
  <div class="list-item">
    <div class="list-item__image"></div>
    <div class="list-item__detail">
      <h3 class="title text-overflow">Photoshop基础入门班级课程…</h3>
      <p class="plan text-overflow">默认教学计划</p>
      <p class="number text-overflow">100道题</p>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../assets/styles/mixins.scss';

.list-item {
  display: flex;
  margin-bottom: vw(8);

  &__image {
    width: vw(128);
    height: vw(72);
    margin-right: vw(12);
    background-color: red;
  }

  &__detail {
    width: vw(200);

    .title {
      width: 200px;
      margin: 0;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      line-height: 20px;
    }

    .plan {
      margin-top: vw(4);
      font-size: 12px;
      color: #999;
      line-height: 16px;
    }

    .number {
      margin-top: vw(8);
      font-size: 14px;
      color: #ff4040;
      line-height: 20px;
    }
  }
}
</style>
