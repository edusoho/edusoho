<template>
  <div class="gn-item clearfix">
    <div class="gn-item__img pull-left">
      <div class="modity-mask" @click="handleModityImage">更换图片</div>
      <img :src="item.image.url || '/static-dist/app/img/vue/decorate/gn_empty.png'">
    </div>
    <div class="gn-form pull-left">
      <div class="gn-form__item">
        <span class="gn-form__label">标题：</span>
        <a-input
          placeholder="请输入标题"
          size="small"
          style="width: 160px;"
          allow-clear
          :default-value="item.title"
          @change="onChange"
        />
      </div>
      <div class="gn-form__item">
        <span class="gn-form__label">链接来源：</span>
      </div>
      <div class="gn-form__item">
        <span class="gn-form__label">链接来源：</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GraphicNavigationEditItem',

  props: {
    item: {
      type: Object,
      required: true
    },

    index: {
      type: Number,
      required: true
    }
  },

  methods: {
    handleModityImage() {
      this.$emit('modity', {
        type: 'image',
        index: this.index
      });
    },

    onChange(e) {
      this.$emit('modity', {
        type: 'title',
        index: this.index,
        value: e.target.value
      });
    }
  }
}
</script>

<style lang="less" scoped>
.gn-item {
  position: relative;
  padding: 15px 10px;
  border: 1px solid #e1e1e1;
  background-color: #fff;

  &__img {
    position: relative;
    overflow: hidden;
    margin-right: 10px;
    width: 80px;
    height: 80px;
    line-height: 80px;
    text-align: center;
    background-color: #f5f5f5;
    cursor: pointer;

    img {
      width: 100%;
      height: 100%;
      border-radius: 16px;
    }

    .modity-mask {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      color: transparent;
      transition: all .2s ease-in-out;
    }

    &:hover {
      .modity-mask {
        background: rgba(0, 0, 0, .5);
        color: #fff;
        transition: all .2s ease-in-out;
      }
    }
  }

  .gn-form {
    &__item {
      margin-bottom: 8px;

      &:last-child {
        margin-bottom: 0;
      }
    }
  }
}
</style>
