<template>
  <div class="review-item">
    <div class="user-avatar"></div>
    <div class="review-detail">
f
    </div>
  </div>
</template>

<style lang="scss" scoped>
.review-item {
  display: flex;
  padding: vw(16) 0;
  margin: 0 vw(16);
  border-bottom: 1px solid #f5f5f5;

  .user-avatar {
    margin-right: vw(4);
    width: vw(42);
    height: vw(42);
    background-color: red;

    img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
    }
  }

  .review-detail {
    flex-grow: 1;
  }
}
</style>
