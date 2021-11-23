<template>
  <div class="component-layout">
    <slot />
    <div class="edit-operate">
      <div class="operate-up">上</div>
      <div class="operate-down">下</div>
      <div class="operate-move">删</div>
    </div>
  </div>
</template>

<style lang="less" scoped>
.component-layout {
  width: 100%;
  position: relative;
  background-color: transparent;

  .edit-operate {
    position: absolute;
    top: -2px;
    right: -14px;
    width: 38px;
    z-index: 1000;
    transform: translate(100%, 0);
    box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.16);
    margin-left: 14px;
    background-color: #fff;
  }
}
</style>

