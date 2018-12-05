<template>
  <module-frame containerClass="setting-coupon" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="find-page__part coupon-preview__container">
      <div class="coupon-preview__title">优惠券</div>
      <div class="coupon-preview__content clearfix" v-show="courseSets.length">
        <e-coupon v-for="item in courseSets.slice(0, 2)" :key="item.id" :item="item"></e-coupon>
      </div>
    </div>
    <div slot="setting" class="coupon-allocate">
      <header class="title">
        优惠券设置
      </header>
      <div class="coupon-allocate__content">
        优惠券选择：
        <el-button size="mini" @click="addCoupon">添加优惠券</el-button>
        <div class="coupon-list-container" v-if="courseSets.length">
          <draggable v-model="courseSets" class="section__course-container">
            <el-tag
              class="courseLink coupon-list-item"
              closable
              :disable-transitions="true"
              v-for="(item, index) in courseSets"
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
      :courseList="courseSets"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses">
    </course-modal>
  </module-frame>
</template>
<script>
import Api from '@admin/api';
import moduleFrame from '../module-frame'
import courseModal from '../course/modal/course-modal';
import coupon from '@/containers/components/e-coupon/e-coupon';
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
      courseSets: [],
      imageMode: [
        'responsive',
        'size-fit',
      ],
      pathName: this.$route.name,
      type: 'coupon',
      radio: 'insideLink'
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
        return this.moduleData.data;
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
      this.courseSets = data;
      if (!data.length) return;
    },
    addCoupon() {
      this.modalVisible = true;
    },
    removeCourseLink(index) {
      this.courseSets.splice(index, 1);
    },
    handleClose(index) {
      this.removeCourseLink(index);
    },
  }
}

</script>
