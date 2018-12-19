<template>
  <module-frame containerClass="setting-coupon" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="find-page__part coupon-preview__container">
      <e-coupon :coupons="copyModuleData.data.items" :feedback="false" :showTitle="radio"></e-coupon>
    </div>
    <div slot="setting" class="coupon-allocate">
      <header class="title">
        优惠券设置（仅显示已发布优惠券）
      </header>
      <div class="default-allocate__content">
        <!-- 标题栏 -->
        <setting-cell title="标题栏：">
          <el-radio v-model="radio" label="show">显示</el-radio>
          <el-radio v-model="radio" label="unshow">不显示</el-radio>
        </setting-cell>

        <!-- 优惠券选择 -->
        <setting-cell title="优惠券选择：" leftClass="required-option">
          <el-button size="mini" @click="addCoupon">添加优惠券</el-button>
        </setting-cell>

        <div v-if="copyModuleData.data.items">
          <draggable v-model="copyModuleData.data.items" class="default-draggable__list">
            <div class="default-draggable__item" v-for="(item, index) in copyModuleData.data.items" :key="index">
              <div class="default-draggable__title text-overflow">{{ item.name }}</div>
              <i class="h5-icon h5-icon-cuowu1 default-draggable__icon-delete" @click="handleClose(index)"></i>
            </div>
          </draggable>
        </div>
      </div>
    </div>
    <course-modal
      slot="modal"
      :visible="modalVisible"
      :type="type"
      limit=10
      :courseList="copyModuleData.data.items"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses">
    </course-modal>
  </module-frame>
</template>
<script>
import Api from '@admin/api';
import moduleFrame from '../module-frame';
import settingCell from '../module-frame/setting-cell';
import courseModal from '../course/modal/course-modal';
import coupon from '@/containers/components/e-coupon-list/e-coupon-list';
import draggable from 'vuedraggable';

export default {
  components: {
    moduleFrame,
    courseModal,
    draggable,
    settingCell,
    'e-coupon': coupon
  },
  data() {
    return {
      modalVisible: false,
      imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
      imageMode: [
        'responsive',
        'size-fit',
      ],
      pathName: this.$route.name,
      type: 'coupon',
    }
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object
    },
    incomplete: {
      type: Boolean,
      default: false,
    }
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {}
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {}
    },
    copyModuleData: {
      get() {
        return this.moduleData;
      },
      set() {}
    },
    radio: {
      get() {
        return this.copyModuleData.data.titleShow;
      },
      set(value) {
        this.copyModuleData.data.titleShow = value;
      },
    },
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
    radio(value) {
      this.showTitle(value);
    },
  },
  methods: {
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    getUpdatedCourses(data) {
      this.copyModuleData.data.items = data;
      if (!data.length) return;
    },
    addCoupon() {
      this.modalVisible = true;
    },
    removeCourseLink(index) {
      this.copyModuleData.data.items.splice(index, 1);
    },
    handleClose(index) {
      this.removeCourseLink(index);
    },
    showTitle() {
      this.copyModuleData.data.titleShow = this.radio
    }
  }
}

</script>
