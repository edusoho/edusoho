<template>
  <div class="component-layout">
    <slot />
    <div class="edit-operate" v-show="active">
      <div class="operate-up">上</div>
      <div class="operate-down">下</div>
      <div class="operate-move">删</div>
    </div>

    <div class="active" v-show="active" />
  </div>
</template>

<script>
export default {
  name: 'ComponentLayout',

  props: {
    active: {
      type: Boolean,
      required: true
    }
  }
}
</script>

<style lang="less" scoped>
.component-layout {
  position: relative;
  padding: 12px 0;
  width: 100%;
  background-color: transparent;

  .active {
    position: absolute;
    z-index: 11;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border: 2px solid #31a1ff;
    pointer-events: none;
  }

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

