<template>
  <module-frame :is-active="isActive" :is-incomplete="isIncomplete" container-class="setting-graphicNavigation">
    <div slot="preview" >
        <e-graphic-navigation
          :graphicNavigation="copyModuleData.data"
        />
    </div>

    <div slot="setting" class="carousel-allocate">
      <e-suggest v-if="moduleData.tips" :suggest="moduleData.tips" :key="moduleData.moduleType"></e-suggest>
      <header class="title">
        图文导航设置
        <div  class="help-text" >建议图片尺寸为80x80px，支持 jpg/png/gif 格式，大小不超过2MB</div>
      </header>
      <div v-for="(item, index) in copyModuleData.data" :key="index">
        <item
          :item="item"
          :index="index"
          :active="activeItemIndex"
          @selected="selected"
          @removeItem="removeHanlder"/>
      </div>
      <el-button type="primary" class="setting-graphicNavigation-add" @click="addHandler">添加图文导航</el-button>
    </div>
  </module-frame>
</template>

<script>
import { MODULE_DEFAULT } from 'admin/config/module-default-config'
import item from './item'
import moduleFrame from '../module-frame'
import eGraphicNavigation from '&/components/e-graphic-navigation/e-graphic-navigation.vue'
import suggest from "&/components/e-suggest/e-suggest.vue"

export default {
  components: {
    item,
    moduleFrame,
    'e-graphic-navigation':eGraphicNavigation,
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
      activeItemIndex: 0,
      pathName: this.$route.name
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
        console.log(this.moduleData);
        return this.moduleData
      },
      set() {
        console.log('changed copyModuleData')
      }
    },
  },
  watch: {
    copyModuleData: {
      handler(data) {
        console.log('update', data)
        this.$emit('updateModule', data)
      },
      deep: true
    }
  },
  methods: {
    selected(selected) {
      this.activeItemIndex = selected.selectIndex
     // this.copyModuleData.data[this.activeItemIndex].image.url=selected.imageUrl
    },
    removeHanlder(index) {
      if (this.copyModuleData.data.length <= 1) {
        this.$message({
          message: '不得少于1个',
          type: 'info'
        })
        return;
      }
      this.copyModuleData.data.splice(index, 1);
    },
    addHandler() {
      if (this.copyModuleData.data.length >= 10) {
        this.$message({
          message: '最多上传10个',
          type: 'info'
        })
        return;
      }
      this.copyModuleData.data.push({
        title: '',
        image: {
          url: '',
          uri: ''
        },
        link: {}
      })
    }
  }
}
</script>
