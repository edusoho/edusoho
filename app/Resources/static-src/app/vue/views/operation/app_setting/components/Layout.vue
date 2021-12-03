<template>
  <div class="component-layout">
    <div class="component-contaienr">
      <slot />
    </div>
    <div class="edit-operate" v-show="active">
      <div class="operate-active" v-if="!isFirst" @click.stop="handleClickActions('up')">
        <a-icon type="arrow-up" />
      </div>
      <div class="operate-active" v-if="!isLast"  @click.stop="handleClickActions('down')">
        <a-icon type="arrow-down" />
      </div>
      <div class="operate-active" @click.stop="handleClickActions('remove')">
        <a-icon type="close" />
      </div>
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
    },

    isFirst: {
      type: Boolean,
      required: true
    },

    isLast: {
      type: Boolean,
      required: true
    }
  },

  methods: {
    handleClickActions(type) {
      this.$emit('event-actions', type);
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

  .component-contaienr {
    pointer-events: none;
  }

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

    .operate-active {
      box-sizing: content-box;
      width: 100%;
      height: 34px;
      cursor: pointer;
      font-weight: 600;
      text-align: center;
      line-height: 34px;
      border-top: 1px solid #f5f5f5;

      &:first-child {
        border-top: none;
      }

      i {
        font-size: 16px;
      }
    }
  }
}
</style>

