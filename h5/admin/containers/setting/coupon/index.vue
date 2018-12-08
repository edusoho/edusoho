<template>
  <module-frame containerClass="setting-coupon" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="find-page__part coupon-preview__container">
      <e-coupon :coupons="copyModuleData.data" :feedback="false" :showTitle="radio"></e-coupon>
    </div>
    <div slot="setting" class="coupon-allocate">
      <header class="title">
        优惠券设置（已过期的优惠券不做展示）
      </header>
      <div class="coupon-allocate__content">
        <div class="mbm text-14" @change="showTitle">
          <span class="coupon-title">标题栏：</span>
          <el-radio v-model="radio" label="show">显示</el-radio>
          <el-radio v-model="radio" label="unshow">不显示</el-radio>
        </div>
        <span class="text-14">优惠券选择：</span>
        <el-button size="mini" @click="addCoupon">添加优惠券</el-button>
        <div class="coupon-list-container" v-if="copyModuleData.data">
          <draggable v-model="copyModuleData.data" class="section__course-container">
            <el-tag
              class="courseLink coupon-list-item text-overflow"
              closable
              :disable-transitions="true"
              v-for="(item, index) in copyModuleData.data"
              @close="handleClose(index)"
              :key="item.id">
              <span>{{ item.name }}</span>
            </el-tag>
          </draggable>
        </div>
      </div>
    </div>
    <course-modal
      slot="modal"
      :visible="modalVisible"
      :type="type"
      limit=10
      :courseList="copyModuleData.data"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses">
    </course-modal>
  </module-frame>
</template>
<script>
import Api from '@admin/api';
import moduleFrame from '../module-frame'
import courseModal from '../course/modal/course-modal';
import coupon from '@/containers/components/e-coupon-list/e-coupon-list';
import draggable from 'vuedraggable';

export default {
  components: {
    moduleFrame,
    courseModal,
    draggable,
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
      radio: 'show'
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
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    }
  },
  methods: {
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    getUpdatedCourses(data) {
      this.copyModuleData.data = data;
      if (!data.length) return;
    },
    addCoupon() {
      this.modalVisible = true;
    },
    removeCourseLink(index) {
      this.copyModuleData.data.splice(index, 1);
    },
    handleClose(index) {
      this.removeCourseLink(index);
    },
    showTitle() {
      this.copyModuleData.titleShow = this.radio
    }
  }
}

</script>
