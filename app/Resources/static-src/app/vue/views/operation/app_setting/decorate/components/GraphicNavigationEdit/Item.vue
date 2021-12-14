<template>
  <div class="gn-item clearfix">
    <div class="gn-item__img pull-left">
      <div class="modity-mask" @click="handleModityImage">更换图片</div>
      <img :src="item.image.url || '/static-dist/app/img/vue/decorate/gn_empty.png'">
    </div>
    <div class="gn-form pull-left">
      <div class="gn-form__item">
        <span class="gn-form__label gn-form__label--required">标题：</span>
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
        <span class="gn-form__label gn-form__label--required">链接来源：</span>
        <a-select
          size="small"
          :default-value="item.link.type"
          style="width: 132px"
          @change="handleCategory"
        >
          <a-select-option v-for="category in categorys" :key="category.key">
            {{ category.text }}
          </a-select-option>
        </a-select>
      </div>
      <div class="gn-form__item">
        <span class="gn-form__label">链接来源：</span>
      </div>
    </div>
  </div>
</template>

<script>
const categorys = [
  { text: '会员专区', key: 'vip' },
  { text: '公开课分类', key: 'openCourse' },
  { text: '班级分类', key: 'classroom' },
  { text: '课程分类', key: 'course' }
]
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

  data() {
    return {
      categorys,
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
    },

    handleCategory() {

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
    margin-right: 16px;
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

    &__label {
      &--required {
        position: relative;

        &::after {
          content: "*";
          position: absolute;
          top: -4px;
          left: -8px;
          color: red;
          font-size: 18px;
        }
      }
    }
  }
}
</style>
