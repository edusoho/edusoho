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
          placeholder="请选择"
          @change="handleCategory"
        >
          <a-select-option v-for="category in categorys" :key="category.key">
            {{ category.text }}
          </a-select-option>
        </a-select>
      </div>
      <div class="gn-form__item" v-if="categoryInfo.text">
        <span class="gn-form__label">{{ categoryInfo.text }}：</span>
        <a-select
          size="small"
          style="width: 118px"
          placeholder="请选择"
          @change="handleSecondCategory"
        >
          <a-select-option v-for="category in categoryInfo.list" :key="category.id">
            {{ category.name }}
          </a-select-option>
        </a-select>
      </div>
    </div>
    <a-icon
      class="remove-btn"
      type="close-circle"
      theme="filled"
      @click="handleClickRemove"
    />
  </div>
</template>

<script>
const categorys = [
  { text: '会员专区', key: 'vip' },
  { text: '公开课分类', key: 'openCourse' },
  { text: '班级分类', key: 'classroom' },
  { text: '课程分类', key: 'course' }
];

import _ from 'lodash';
import { Category } from 'common/vue/service/index.js';
import { state, mutations } from 'app/vue/views/operation/app_setting/decorate/store.js';

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
      categoryInfo: {}
    }
  },

  mounted() {
    const { type } = this.item.link;
    this.getSecondCategory(type);
  },

  methods: {
    setCourseCategory: mutations.setCourseCategory,
    setClassroomCategory: mutations.setClassroomCategory,
    setOpenCourseCategory: mutations.setOpenCourseCategory,

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

    handleCategory(value) {
      this.getSecondCategory(value);
      this.$emit('modity', {
        type: 'type',
        index: this.index,
        value: value
      });
    },

    async getSecondCategory(type) {
      if (type === 'vip') {
        this.categoryInfo = {};
        return;
      }

      const store = {
        openCourse: {
          text: '公开课分类',
          stateKey: 'openCourseCategory',
          mutationsKey: 'setOpenCourseCategory',
          query: { type: 'course' }
        },
        course: {
          text: '课程分类',
          stateKey: 'courseCategory',
          mutationsKey: 'setCourseCategory',
          query: { type: 'course' }
        },
        classroom: {
          text: '班级分类',
          stateKey: 'classroomCategory',
          mutationsKey: 'setClassroomCategory',
          query: { type: 'classroom' }
        }
      }

      const { text, stateKey, mutationsKey, query } = store[type];


      if (!_.size(state[stateKey])) {
        const data = await Category.get({ query });
        this[mutationsKey](data);
      }

      this.categoryInfo = {
        text,
        list: state[stateKey]
      };
    },

    handleSecondCategory(value) {
      this.$emit('modity', {
        type: 'conditions',
        index: this.index,
        value: {
          categoryId: value
        }
      });
    },

    handleClickRemove() {
      this.$emit('modity', {
        type: 'remove',
        index: this.index
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

  .remove-btn {
    position: absolute;
    top: -6px;
    right: -6px;
    display: none;
    font-size: 18px;
    color: #bbb;
    text-align: center;
    cursor: pointer;
    transform: all .3s ease;

    &:hover {
      color: #aaa;
    }
  }

  &:hover {
    .remove-btn {
      display: block;
    }
  }

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
      margin-bottom: 4px;

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
