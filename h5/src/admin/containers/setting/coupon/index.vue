<template>
  <module-frame :is-active="isActive" :is-incomplete="isIncomplete" container-class="setting-coupon">
    <div slot="preview" class="find-page__part coupon-preview__container">
      <e-coupon :coupons="copyModuleData.data.items" :feedback="false" :show-title="radio"/>
    </div>
    <div slot="setting" class="coupon-allocate">
      <e-suggest v-if="moduleData.tips" :suggest="moduleData.tips" :key="moduleData.moduleType"></e-suggest>
      <header class="title">
        优惠券设置（仅显示未过期的优惠券）
        <div v-if="portal === 'miniprogram'" class="text-12 color-gray mts">使用优惠券配置功能，小程序版本需要升级到1.3.2及以上</div>
      </header>
      <div class="default-allocate__content">
        <!-- 标题栏 -->
        <setting-cell title="标题栏：">
          <el-radio v-model="radio" label="show">显示</el-radio>
          <el-radio v-model="radio" label="unshow">不显示</el-radio>
        </setting-cell>

        <!-- 优惠券选择 -->
        <setting-cell title="优惠券选择：" left-class="required-option">
          <el-button size="mini" @click="addCoupon">添加优惠券</el-button>
        </setting-cell>

        <div v-if="copyModuleData.data.items">
          <draggable v-model="copyModuleData.data.items" class="default-draggable__list">
            <div v-for="(item, index) in copyModuleData.data.items" :key="index" class="default-draggable__item">
              <div class="default-draggable__title text-overflow">{{ item.name }}</div>
              <i class="h5-icon h5-icon-cuowu1 default-draggable__icon-delete" @click="handleClose(index)"/>
            </div>
          </draggable>
        </div>
      </div>
    </div>
    <course-modal
      slot="modal"
      :visible="modalVisible"
      :type="type"
      :course-list="copyModuleData.data.items"
      limit="10"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses"/>
  </module-frame>
</template>
<script>
import moduleFrame from '../module-frame'
import settingCell from '../module-frame/setting-cell'
import courseModal from '../course/modal/course-modal'
import coupon from '&/components/e-coupon-list/e-coupon-list'
import draggable from 'vuedraggable'
import pathName2Portal from 'admin/config/api-portal-config'
import suggest from "&/components/e-suggest/e-suggest.vue"

export default {
  components: {
    moduleFrame,
    courseModal,
    draggable,
    settingCell,
    'e-coupon': coupon,
    'e-suggest':suggest
  },
  props: {
    active: {
      type: Boolean,
      default: false
    },
    moduleData: {
      type: Object,
      default: () => {}
    },
    incomplete: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      modalVisible: false,
      imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
      imageMode: [
        'responsive',
        'size-fit'
      ],
      pathName: this.$route.name,
      type: 'coupon'
    }
  },
  computed: {
    isActive: {
      get() {
        return this.active
      },
      set() {}
    },
    isIncomplete: {
      get() {
        return this.incomplete
      },
      set() {}
    },
    copyModuleData: {
      get() {
        return this.moduleData
      },
      set() {}
    },
    radio: {
      get() {
        return this.copyModuleData.data.titleShow
      },
      set(value) {
        this.copyModuleData.data.titleShow = value
      }
    },
    portal() {
      return pathName2Portal[this.pathName]
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data)
      },
      deep: true
    },
    radio(value) {
      this.showTitle(value)
    }
  },
  methods: {
    modalVisibleHandler(visible) {
      this.modalVisible = visible
    },
    getUpdatedCourses(data) {
      this.copyModuleData.data.items = data
      if (!data.length) return
    },
    addCoupon() {
      this.modalVisible = true
    },
    removeCourseLink(index) {
      this.copyModuleData.data.items.splice(index, 1)
    },
    handleClose(index) {
      this.removeCourseLink(index)
    },
    showTitle() {
      this.copyModuleData.data.titleShow = this.radio
    }
  }
}

</script>
