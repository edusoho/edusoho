<template>
  <div>
    <div class="setting-page">
      <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
      <div class="find-navbar"><i class="h5-icon h5-icon-houtui"></i>EduSoho 微网校</div>

      <div class="find-body">
        <module-template v-for="(module, index) in modules"
          :module="module" :active="isActive(index)"
          :key="index"
          :index="index"
          @activeModule="activeModule"
          @updateModule="updateHandler($event, index)">
        </module-template>
      </div>

      <div class="find-section clearfix">
        <div class="section-title">点击添加组件</div>
        <el-button class="find-section-item" type="" size="medium"
          v-for="(item, index) in moduleItems"
          @click="addModuleItem(item.default)"
          :key="index">
          {{ item.name }}
        </el-button>
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

    <div class="setting-button-group">
      <el-button class="setting-button-group__button text-medium btn-border-primary" size="mini" @click="save('draft')">预 览</el-button>
      <el-button class="setting-button-group__button text-medium" type="primary" size="mini" @click="save('published')">发 布</el-button>
    </div>
  </div>
</template>
<script>
import items from '@/utils/footer-config'
import ObjectArray2ObjectByKey from '@/utils/array2object';
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
      currentModuleIndex: '0',
      items,
      moduleItems: [{
          name: '轮播图',
          default: {
            "type": "slide_show",
            "moduleType": "",
            "data": [{
              "title": "",
              "image": {},
              "link": {
                "type": "url",
                "target": null,
                "url": ""
              }
            }]
          }
        },
        {
          name: '课程列表',
          default: {
            "type": "course_list",
            "moduleType": "",
            "data":
            {
              "title": "",
              "sourceType": "condition",
              "categoryId": "0",
              "sort": "-studentNum",
              "lastDays": "0",
              "limit": "4",
              "items": []
            }
          }
        },
        {
          name: '图片广告',
          default: {
            "type": "poster",
            "moduleType": "",
            "data":
            {
              "image": {},
              "link": {
                "type": "url",
                "target": null,
                "url": ""
              }
            }
          }
        }
      ]
    }
  },
  computed: {
    footerItemStyle() {
      return { width: `${100/items.length}%` }
    }
  },
  created() {
    // 读取设置
    this.getDraft({
      portal: 'h5',
      type: 'discovery',
      mode: 'published',
    }).then((res) => {
      this.modules = Object.values(res);
    })

    // 获得课程分类列表
    this.getCategories()
  },
  methods: {
    ...mapActions([
      'getCategories',
      'saveDraft',
      'getDraft',
    ]),
    isActive(index) {
      return index === this.currentModuleIndex;
    },
    activeModule(index) {
      this.currentModuleIndex = index;
    },
    updateHandler(data, index) {
      console.log(data, 888);
    },
    addModuleItem(item) {
      this.modules.push(item);
    },
    save(mode, needTrans = true) {
      // 保存设置
      const data = this.modules;
      if (needTrans) {
        const data = ObjectArray2ObjectByKey(this.modules, 'moduleType');
      }

      this.saveDraft({
        data,
        mode,
        portal: 'h5',
        type: 'discovery',
      }).then(() => {
        this.$router.push({ name: 'preview' })
      })
    }
  }
}

</script>
