<template>
  <module-frame containerClass="setting-search" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" >
      <van-search
        shape="round"
        background="#ffffff"
        placeholder="搜索课程、班级"
      />
    </div>

    <div slot="setting">
        <header class="title">
        搜索设置
      </header>
      <div  class="text-14 color-gray mts">
        <i class="el-icon-warning"></i> 
        搜索功能目前只支持搜索班级和课程</div>
    </div>

  </module-frame>
</template>
<script>
import courseList from "&/components/e-course-list/e-course-list.vue";
import moduleFrame from '../module-frame';
export default {
  name:"search",
  components: {
    moduleFrame
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
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data)
      },
      deep: true
    }
  }
}
</script>