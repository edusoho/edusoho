<template>
  <div class="gn-item clearfix">
    <div class="gn-item__img pull-left">
      <div class="modity-mask" @click="handleModityImage">{{ 'decorate.change_picture' | trans }}</div>
      <img :src="item.image.uri || '/static-dist/app/img/vue/decorate/gn_empty.png'">
    </div>
    <div class="gn-form pull-left">
      <div class="gn-form__item">
        <span class="gn-form__label gn-form__label--required">{{ 'decorate.title2' | trans }}：</span>
        <a-input
          :placeholder="'decorate.please_enter_a_title' | trans"
          size="small"
          style="width: 160px;"
          allow-clear
          :default-value="item.title"
          @change="onChange"
        />
      </div>
      <div class="gn-form__item">
        <span class="gn-form__label gn-form__label--required">{{ 'decorate.link_source' | trans }}：</span>
        <a-select
          size="small"
          :default-value="item.link.type"
          style="width: 132px"
          :placeholder="'decorate.please_choose' | trans"
          @change="handleCategory"
        >
          <a-select-option  v-for="category in categorys" :key="category.key" @click="openCustomLink(category.key)">
            {{ category.text | trans }}
          </a-select-option>
        </a-select>
      </div>
      <div class="gn-form__item" v-if="categoryInfo.text">
        <span class="gn-form__label">{{ categoryInfo.text }}：</span>
        <a-select
          size="small"
          :default-value="item.link.categoryId"
          :style="{ width: categoryInfo.stateKey === 'openCourseCategory' ? '118px' : '132px' }"
          :placeholder="'decorate.please_choose' | trans"
          @change="handleSecondCategory"
        >
          <a-select-option v-for="category in categoryInfo.list" :key="category.id">
            {{ category.name }}
          </a-select-option>
        </a-select>
      </div>
      <div class="gn-form__item" v-if="selectdLink" style="display: flex;align-items: center;">
        <div class="gn-form__label">{{ 'decorate.select_link' | trans }}：</div>
        <div style="flex: 1;display: flex;justify-content: space-between;" >
          <div
            v-show="selectdLink"
            @mouseenter="isSelectdLinkHover = true"
            @mouseleave="isSelectdLinkHover = false"
            style="display: flex;align-items: center;"
          >
            <div class="text-overflow" style="max-width:90px;">
              {{ selectdLink }}
            </div>
            <a-icon v-show="isSelectdLinkHover"  @click="removeSelectedLink" type="close-circle" style="color: #31A1FF;" />
          </div>
          <a class="ant-dropdown-link"  @click="openCustomLink(item.link.type)">
            {{ 'decorate.revise' |trans }}
          </a>
        </div>
      </div>
    </div>

    <a-icon
      class="remove-btn"
      type="close-circle"
      theme="filled"
      @click="handleClickRemove"
    />
    <custom-link-modal ref="customLink" @update-link="handleUpdateLink" />
  </div>
</template>

<script>
const categorys = [
  { text: 'decorate.class_classification', key: 'classroom' },
  { text: 'decorate.open_class_classification', key: 'openCourse' },
  { text: 'decorate.course_sorts', key: 'course' },
  { text: 'decorate.members_only', key: 'vip' },
  { text: 'decorate.custom_link',key: 'customLink' },
  { text: 'decorate.question_bank',key:'questionBank' }
];

import _ from 'lodash';
import { Category } from 'common/vue/service/index.js';
import { state, mutations } from 'app/vue/views/operation/app_setting/decorate/store.js';
import CustomLinkModal from "../CustomLinkModal.vue";

export default {
  name: 'GraphicNavigationEditItem',

  components: {
    CustomLinkModal
  },

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
      categoryInfo: {},
      isSelectdLinkHover:false,
      selectdLink:this.item.customLink
    }
  },

  mounted() {
    const { type } = this.item.link;
    this.getSecondCategory(type);
  },
  watch:{
    item:{
      handler(newVal){
        this.selectdLink = newVal.customLink;
      },
      deep:true
    }
  },
  methods: {
    setCourseCategory: mutations.setCourseCategory,
    setClassroomCategory: mutations.setClassroomCategory,
    setOpenCourseCategory: mutations.setOpenCourseCategory,
    setQuestionBankCategory:mutations.setQuestionBankCategory,

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

    handleCustomLink(value){
      this.$emit('modity', {
        type: 'customLink',
        index: this.index,
        value: value,
      });
    },

    handleUpdateLink({url}) {
      this.selectdLink = url;
      this.handleCustomLink(url);
    },

    openCustomLink(value){
      if(value!=='customLink') return;
      this.$refs.customLink.showModal(this.item.customLink);
    },

    removeSelectedLink(){
      this.selectdLink = '';
      this.$refs.customLink.setFormData('');
      this.handleCustomLink('');
    },

    handleCategory(value) {
      this.getSecondCategory(value);

      this.removeSelectedLink();

      this.$emit('modity', {
        type: 'type',
        index: this.index,
        value: value
      });
    },

    async getSecondCategory(type) {
      if (type === 'vip'||type === 'customLink') {
        this.categoryInfo = {};
        return;
      }

      const store = {
        openCourse: {
          text: Translator.trans('decorate.open_class_classification'),
          stateKey: 'openCourseCategory',
          mutationsKey: 'setOpenCourseCategory',
          query: { type: 'course' }
        },
        course: {
          text: Translator.trans('decorate.course_sorts'),
          stateKey: 'courseCategory',
          mutationsKey: 'setCourseCategory',
          query: { type: 'course' }
        },
        classroom: {
          text: Translator.trans('decorate.class_classification'),
          stateKey: 'classroomCategory',
          mutationsKey: 'setClassroomCategory',
          query: { type: 'classroom' }
        },
        questionBank:{
          text: Translator.trans('decorate.question_bank'),
          stateKey: 'questionBankCategory',
          mutationsKey: 'setQuestionBankCategory',
          query: { type: 'itemBankExercise' }
        }
      }

      const { text, stateKey, mutationsKey, query } = store[type];


      if (!_.size(state[stateKey])) {
        const data = await Category.get({ query });
        this[mutationsKey](data);
      }
      const defaultCategory = {
        id: 0,
        name: '全部',
      };

      this.categoryInfo = {
        text,
        stateKey,
        list: [defaultCategory, ...state[stateKey]]
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
    border-radius: 32px;

    img {
      width: 100%;
      height: 100%;
      border-radius: 32px;
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
      color: #666;

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
