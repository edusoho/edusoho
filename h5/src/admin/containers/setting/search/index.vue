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
    </div>

  </module-frame>
</template>
<script>
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