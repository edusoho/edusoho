<template>
  <div>
    <div class="setting-page">
      <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
      <div class="find-navbar"><i class="h5-icon h5-icon-houtui"></i>微网校</div>

      <!-- 操作预览区域 -->
      <div class="find-body">
        <draggable v-model="modules">
          <module-template v-for="(module, index) in modules"
            :module="module" :active="isActive(index)"
            :key="index"
            :moduleKey="`${module.type}-${index}`"
            :index="index"
            @activeModule="activeModule"
            @updateModule="updateModule($event, index)"
            @removeModule="removeModule($event, index)">
          </module-template>
        </draggable>
      </div>

      <!-- 底部添加组件按钮 -->
      <div class="find-section clearfix">
        <div class="section-title">点击添加组件</div>
        <el-button class="find-section-item" type="" size="medium"
          v-for="(item, index) in moduleItems"
          @click="addModule(index)"
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

    <!-- 发布预览按钮 -->
    <div class="setting-button-group">
      <el-button class="setting-button-group__button text-medium btn-border-primary" size="mini" @click="reset">重 置</el-button>
      <el-button class="setting-button-group__button text-medium btn-border-primary" size="mini" @click="save('draft')">预 览</el-button>
      <el-button class="setting-button-group__button text-medium" type="primary" size="mini" @click="save('published')">发 布</el-button>
    </div>
  </div>
</template>
<script>
import items from '@/utils/footer-config'
import moduleDefault from '@admin/utils/module-default-config';
import ObjectArray2ObjectByKey from '@/utils/array2object';
import Api from '@admin/api';
import moduleTemplate from './module-template';
import draggable from 'vuedraggable';
import { mapActions } from 'vuex';
import * as types from '@admin/store/mutation-types';

export default {
  components: {
    moduleTemplate,
    draggable
  },
  data() {
    return {
      title: 'EduSoho 微网校',
      modules: [],
      currentModuleIndex: '0',
      items,
      moduleItems: [{
          name: '轮播图',
          default: moduleDefault.slideShow,
        },
        {
          name: '课程列表',
          default: moduleDefault.courseList,
        },
        {
          name: '图片广告',
          default: moduleDefault.poster,
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
    this.load();

    // 获得课程分类列表
    this.getCategories();
  },
  watch: {
    modules: {
      handler(value) {
        // value.for (let i = 0, len = value.length; i < len; i++) {
        //   Things[i]
        // }
        console.log(value.length)
      }
    },
  },
  methods: {
    ...mapActions([
      'getCategories',
      'deleteDraft',
      'saveDraft',
      'getDraft',
    ]),
    isActive(index) {
      return index === this.currentModuleIndex;
    },
    activeModule(index) {
      this.currentModuleIndex = index;
    },
    updateModule(data, index) {
      console.log('updateModule');
    },
    removeModule(data, index) {
      console.log('removeModule');
      this.currentModuleIndex = Math.max(this.currentModuleIndex - 1, 0);
      this.modules.splice(index, 1);
    },
    addModule(index) {
      console.log('addModule')
      // 需要一个深拷贝对象
      const defaultString = JSON.stringify(this.moduleItems[index].default);
      const defaultCopied = JSON.parse(defaultString);

      this.modules.push(defaultCopied);
      this.currentModuleIndex =  Math.max(this.modules.length - 1, 0);
    },
    load() {
      // 读取草稿配置
      this.getDraft({
        portal: 'h5',
        type: 'discovery',
        mode: 'draft',
      }).then((res) => {
        this.modules = Object.values(res);
      })
    },
    reset() {
      // 删除草稿配置配置
      this.deleteDraft({
        portal: 'h5',
        type: 'discovery',
        mode: 'draft',
      }).then((res) => {
        this.$message({
          message: '重置成功',
          type: 'success'
        });
      }).then(() => {
        this.load();
      }).catch(err => {
        this.$message({
          message: err.message || '重置失败',
          type: 'error'
        });
      });
    },
    save(mode, needTrans = true) {
      // 保存配置
      const isPublish = mode === 'published';
      let data = this.modules;

      // 如果已经是对象就不用转换
      if (needTrans) {
        data = ObjectArray2ObjectByKey(this.modules, 'moduleType');
      }

      this.saveDraft({
        data,
        mode,
        portal: 'h5',
        type: 'discovery',
      }).then(() => {
        if (isPublish) {
          this.$message({
            message: '发布成功',
            type: 'success'
          });
          return;
        }

        this.$store.commit(types.UPDATE_DRAFT, data);
        this.$router.push({
          name: 'preview',
          query: {
            times: 10,
            preview: isPublish ? 0 : 1,
            duration: 60 * 5,
          }
        });
      }).catch(err => {
        this.$message({
          message: err.message || '发布失败，请重新尝试',
          type: 'error'
        });
      })
    }
  }
}

</script>
