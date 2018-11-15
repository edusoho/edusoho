<template>
  <div>
    <div class="setting-page">
      <img class="find-head-img" :src="pathName === 'miniprogramSetting' ? 'static/images/miniprogram_head.jpg' : 'static/images/find_head_url.jpg'" alt="">
      <div class="find-navbar" :class="{'find-navbar-miniprogram': pathName === 'miniprogramSetting'}">
        <i class="h5-icon h5-icon-houtui"></i>{{ pathName === 'miniprogramSetting' ? '小程序' : '微网校'}}
      </div>

      <!-- 操作预览区域 -->
      <div class="find-body">
        <draggable
          v-model="modules"
          :options="{
            filter: stopDraggleDoms,
            preventOnFilter: false,
          }">
          <module-template v-for="(module, index) in modules"
            :key="index"
            :saveFlag="saveFlag"
            :index="index"
            :module="module"
            :active="isActive(index)"
            :moduleKey="`${module.type}-${index}`"
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
          @click="addModule(item, index)"
          :key="index">
          {{ item.name }}
        </el-button>
      </div>

      <find-footer></find-footer>
    </div>

    <!-- 发布预览按钮 -->
    <div class="setting-button-group">
      <el-button
        class="setting-button-group__button text-medium btn-border-primary"
        size="mini" @click="reset" :disabled="isLoading">重 置</el-button>
      <el-button
        class="setting-button-group__button text-medium btn-border-primary"
        size="mini" @click="save('draft')" :disabled="isLoading">预 览</el-button>
      <el-button
        class="setting-button-group__button text-medium" type="primary"
        size="mini" @click="save('published')" :disabled="isLoading">发 布</el-button>
    </div>
  </div>
</template>
<script>
import Api from '@admin/api';
import * as types from '@admin/store/mutation-types';
import moduleDefault from '@admin/utils/module-default-config';
import ModuleCounter from '@admin/utils/module-counter';
import pathName2Portal from '@admin/utils/api-portal-config';
import ObjectArray2ObjectByKey from '@/utils/array2object';
import moduleTemplate from './module-template';
import findFooter from './footer';
import draggable from 'vuedraggable';
import { mapActions, mapState } from 'vuex';

export default {
  components: {
    moduleTemplate,
    draggable,
    findFooter
  },
  data() {
    return {
      title: 'EduSoho 微网校',
      modules: [],
      saveFlag: 0,
      incomplete: true,
      validateResults: [],
      currentModuleIndex: '0',
      moduleItems: [{
          name: '轮播图',
          default: moduleDefault.slideShow,
        },
        {
          name: '课程列表',
          default: moduleDefault.courseList,
        },
        {
          name: '班级列表',
          default: moduleDefault.classList,
        },
        {
          name: '图片广告',
          default: moduleDefault.poster,
        }
      ],
      typeCount: {},
      pathName: this.$route.name,
    }
  },
  computed: {
    ...mapState(['isLoading']),
    stopDraggleDoms() {
      return '.module-frame__setting, .find-footer, .search__container, .el-dialog__header, .el-dialog__footer';
    },
  },
  created() {
    this.load();

    // 获得课程分类列表
    this.getCategories();
  },
  methods: {
    ...mapActions([
      'getCategories',
      'deleteDraft',
      'saveDraft',
      'getDraft',
    ]),
    moduleCountInit() {
      // 模块类型计数初始化
      const typeCount = new ModuleCounter();
      for (let i = 0, len = this.modules.length; i < len; i++) {
        typeCount.addByType(this.modules[i].type);
      }
      this.typeCount = typeCount;
    },
    isActive(index) {
      return index === this.currentModuleIndex;
    },
    activeModule(index) {
      // 激活编辑模块
      this.currentModuleIndex = index;
    },
    updateModule(data, index) {
      // 更新模块
      this.validateResults[index] = data.incomplete;
      console.log('updateModule', data);
    },
    removeModule(data, index) {
      // 删除一个模块
      this.typeCount.removeByType(data.type);

      this.currentModuleIndex = Math.max(this.currentModuleIndex - 1, 0);
      this.modules.splice(index, 1);
    },
    addModule(data, index) {
      // 新增一个模块
      if (this.typeCount.getCounterByType(data.default.type) >= 5) {
        this.$message({
          message: '同一类型组件最多添加 5 个',
          type: 'warning'
        })
        return;
      }
      this.typeCount.addByType(data.default.type);

      const defaultString = JSON.stringify(this.moduleItems[index].default); // 需要一个深拷贝对象
      const defaultCopied = JSON.parse(defaultString);

      this.modules.push(defaultCopied);
      this.currentModuleIndex = Math.max(this.modules.length - 1, 0);
    },
    load() {
      // 读取草稿配置
      const mode = this.$route.query.draft == 1 ? 'draft' : 'published';

      this.getDraft({
        portal: pathName2Portal[this.pathName],
        type: 'discovery',
        mode,
      }).then(res => {
        this.modules = Object.values(res);
        this.moduleCountInit();
      })
    },
    reset() {
      // 删除草稿配置配置
      this.deleteDraft({
        portal: pathName2Portal[this.pathName],
        type: 'discovery',
        mode: 'draft',
      }).then(res => {
        this.$message({
          message: '重置成功',
          type: 'success'
        });
        this.load();
      }).catch(err => {
        this.$message({
          message: err.message || '重置失败',
          type: 'error'
        });
      });
    },
    save(mode, needTrans = true) {
      this.saveFlag ++;

      // 验证提交配置
      const validateAndSubmit = () => {
        let data = this.modules;
        const isPublish = mode === 'published';

        this.validate();
        // 如果已经是对象就不用转换
        if (needTrans) {
          data = ObjectArray2ObjectByKey(this.modules, 'moduleType');
        }

        if (this.incomplete) {
          return;
        }

        this.saveDraft({
          data,
          mode,
          portal: pathName2Portal[this.pathName],
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
              from: this.pathName,
            }
          });
        }).catch(err => {
          this.$message({
            message: err.message || '发布失败，请重新尝试',
            type: 'error'
          });
        })
      };

      setTimeout(() => {
        validateAndSubmit();
      }, 500); // 点击 预览／发布 时去验证所有组件，会有延迟，目前 low 的解决方法延迟 500ms 判断验证结果
    },
    validate() {
      for (var i = 0; i < this.modules.length; i++) {
        if (this.validateResults[i]) {
          this.incomplete = this.validateResults[i]
          return;
        }
      }
      this.incomplete = false;
    },
  }
}

</script>
