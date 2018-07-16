<template>
  <div class="loading">
    <van-loading type="spinner" color="white" size="40px" />
  </div>
</template>
<style lang="scss">
  .loading {
    position: fixed;
    height: 100vh;
    width: 100vw;
    .van-loading--white {
      position: absolute;
      top: 35%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: rgba(0, 0, 0, .5);
      padding: 10px;
      border-radius: 3px;
    }
  }
</style>
