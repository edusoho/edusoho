<template>
  <div class="component-classify-item">
    <div :class="{ active: isActive }">
      <svg-icon :icon="classify.icon" />
      <div class="classify-title">{{ classify.title }}</div>
    </div>
    <template v-if="classify.lists">
      <div v-show="isActive" class="add-component-lists">
        <div class="clearfix">
          <div
            v-for="component in classify.lists"
            :key="component.name"
            class="add-list-item pull-left"
          >
            <svg-icon :icon="component.icon" />
            <div class="component-title">{{ component.title }}</div>
          </div>
        </div>
        <div class="arrow-content" :style="{ top: (88 * index + 44) + 'px' }"></div>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    classify: {
      type: Object,
      required: true
    },

    index: {
      type: Number,
      required: true
    },

    currentClassify: {
      type: String,
      required: true
    }
  },

  computed: {
    isActive() {
      return this.classify.key === this.currentClassify;
    }
  }
}
</script>

<style lang="less" scoped>
.component-classify-item {
  margin-bottom: 40px;
  text-align: center;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.35);

  .svg-icon {
    font-size: 24px;
    display: block;
    margin: 8px auto;
  }

  .classify-title {
    margin-top: 4px;
    line-height: 16px;
    height: 16px;
    font-size: 12px;
  }

  .active {
    .svg-icon {
      color: #fff;
    }

    .classify-title {
      color: #fff;
    }
  }

  .add-component-lists {
    position: absolute;
    top: 0;
    left: 80px;
    width: 190px;
    height: 100%;
    background: #fff;
    box-shadow: 4px 0px 8px 0px rgba(0, 0, 0, 0.05);
    z-index: 10;
    padding: 11px;
    color: #333;

    .add-list-item {
      position: relative;
      width: 78px;
      height: 72px;
      text-align: center;
      border-radius: 2px;
      cursor: pointer;
      margin-left: 10px;
      transition: all 0.3s ease;

      .svg-icon {
        font-size: 24px;
        display: block;
        margin: 8px auto;
      }

      .component-title {
        color: #4a4a4a;
        line-height: 16px;
        height: 16px;
        font-size: 12px;
      }

      &.limit-add {
        opacity: 0.3;
        cursor: not-allowed;
      }

      &:nth-of-type(even) {
        margin-left: 0;
      }

      &:hover {
        background: #31a1ff;
        color: #fff;
      }
    }

    .arrow-content {
      position: absolute;
      width: 8px;
      height: 8px;
      left: 0;
      margin-left: -4px;
      transform: rotate(45deg);
      background-color: #fff;
    }
  }
}
</style>
