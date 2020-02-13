<template>
  <module-frame :is-active="isActive" :is-incomplete="isIncomplete" container-class="setting-graphicNavigation">
    <div slot="preview" >
        <div class="graphicNavigation__warp">
          <div 
          :class="[index===1? 'graphicNavigation__border':'','graphicNavigation__item']" 
          v-for="(item, index) in copyModuleData.data"
          :key="index">
            <img v-if="!item.image.url" class="graphicNavigation__img" :src="getDefaultImg(item.link.type)" />
            <img v-else class="graphicNavigation__img" :src="item.image.url" />
            <span v-if="item.title">{{item.title}}</span>
          </div>
        </div>
    </div>

    <div slot="setting" class="carousel-allocate">
      <header class="title">
        图文导航设置
        <div  class="help-text" >建议图片尺寸为80x80px，支持 jpg/png/gif 格式，大小不超过2MB</div>
      </header>
      <div v-for="(item, index) in copyModuleData.data" :key="index">
        <item
          :item="item"
          :index="index"
          :active="activeItemIndex"
          @selected="selected"/>
      </div>
    </div>
  </module-frame>
</template>

<script>
import { MODULE_DEFAULT } from 'admin/config/module-default-config'
import item from './item'
import moduleFrame from '../module-frame'

export default {
  components: {
    item,
    moduleFrame,
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
        this.$emit('updateModule', data)
      },
      deep: true
    }
  },
  methods: {
    getDefaultImg(type){
      switch(type){
        case "openCourse":
          return "static/images/openCourse.png"
        case "course":
          return "static/images/hotcourse.png"
        case "class":
          return "static/images/hotclass.png"
      }
    },
    selected(selected) {
      this.activeItemIndex = selected.selectIndex
     // this.copyModuleData.data[this.activeItemIndex].image.url=selected.imageUrl
    }
  }
}
</script>
