<template>
  <div class="question-item">
    <h3 class="question-title text-overflow">Photoshop基础入门班级课程阿建设大街爱Photoshop基础入门班级课程阿建设大街爱好</h3>
    <p class="question-desc">Photoshop基础入门班级Photoshop基础入门班级Photoshop基础入门班级Photoshop基础入门班级Photoshop基础入门班级…Photoshop基础入门班级Photoshop基础入门班级Photoshop基础入门班级Photoshop基础入门班级Photoshop基础入门班级…</p>
    <div class="question-details">
      <div class="question-details__left">
        <img class="user-avatar" src="">
        <span>曲敬良发起 03/21 20:25</span>
      </div>
      <div class="question-details__right">1.4k</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuestionItem'
}
</script>

<style lang="scss" scoped>
.question-item {
  padding: vw(16);
  border-bottom: 1px solid #f5f5f5;

  .question-title {
    margin: 0;
    font-size: vw(16);
    font-weight: 500;
    color: #333;
    line-height: vw(24);
  }

  .question-desc {
    margin-top: vw(8);
    font-size: vw(12);
    color: #666;
    line-height: vw(16);
    @include text-overflow(2);
  }

  .question-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: vw(8);
    font-size: vw(14);
    line-height: vw(20);

    &__left {
      display: flex;
      align-items: center;
      color: #666;

      .user-avatar {
        margin-right: vw(4);
        width: vw(24);
        height: vw(24);
        border-radius: 50%;
      }
    }

    &__right {
      color: #999;
    }
  }
}
</style>
