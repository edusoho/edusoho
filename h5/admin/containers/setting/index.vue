<template>
  <div class="setting-page">
    <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
    <div class="find-navbar"><i class="h5-icon h5-icon-houtui"></i>EduSoho 微网校</div>

    <div class="find-body">
      <module-template v-for="(module, index) in modules"
        :module="module" :active="isActive(module)"
        :key="index"
        @activeModule="activeModule"
        @updateModule="updateHandler($event, index)">
      </module-template>
    </div>

    <div class="find-footer">
      <div class="find-footer-item" v-for="item in items"
          :class="{ active: item.name === 'find' }"
          :style="footerItemStyle"
          :key="item.type">
        <img class="find-footer-item__icon" :src="item.name === 'find' ? item.active : item.normal" />
        <span class="find-footer-item__text">{{ item.type }}</span>
      </div>
    </div>
  </div>
</template>
<script>
import items from '@/utils/footer-config'
import Api from '@admin/api';
import moduleTemplate from './module-template';
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  components: {
    moduleTemplate
  },
  data() {
    return {
      title: 'EduSoho 微网校',
      modules: [],
      currentModule: 'slide-1',
      items,
    }
  },
  computed: {
    footerItemStyle() {
      return { width: `${100/items.length}%` }
    }
  },
  created() {
    // 保存设置
    // this.saveDraft({
    //   data: this.modules,
    //   portal: 'h5',
    //   type: 'discovery',
    //   mode: 'draft',
    // })

    // 读取设置
    this.getDraft({
      portal: 'h5',
      type: 'discovery',
      mode: 'draft',
    }).then((res) => {
      this.modules = Object.values(res);
    })

    // 获得课程分类列表
    this.getCategories()
  },
  methods: {
    ...mapActions ([
      'getCategories',
      'saveDraft',
      'getDraft',
    ]),
    isActive(module) {
      return module.moduleType === this.currentModule;
    },
    activeModule({ moduleId }) {
      this.currentModule = moduleId;
    },
    updateHandler(data, index) {
      console.log(data, 888);
    }
  }
}

</script>
