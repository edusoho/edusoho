<template>
  <div class="h5-home" :style="{ minHeight: windowHeight + 'px' }">
    <el-row>
      <el-col :span="8">
        <div class="h5-home-left" :style="menuStyle">
          <el-menu
            default-active="1-0"
            class="el-menu-vertical-demo"
            :collapse="false"
          >
            <el-submenu index="1">
              <template slot="title">
                <span>基础组件</span>
              </template>
              <el-menu-item-group>
                <el-menu-item
                  :index="`1-${index}`"
                  v-for="(item, index) in baseModules"
                  :key="`base-${index}`"
                  @click="addModule(item, index)"
                >
                  <i :class="['iconfont', item.icon]"></i>
                  {{ item.name }}
                </el-menu-item>
              </el-menu-item-group>
            </el-submenu>

            <el-submenu index="2">
              <template slot="title">
                <span>营销组件</span>
              </template>
              <el-menu-item-group>
                <el-menu-item
                  :index="`2-${index}`"
                  v-for="(item, index) in marketingModules"
                  :key="`marketing-${index}`"
                  @click="addModule(item, index)"
                >
                  <i :class="['iconfont', item.icon]"></i>
                  {{ item.name }}
                </el-menu-item>
              </el-menu-item-group>
            </el-submenu>
          </el-menu>
        </div>
      </el-col>
      <el-col :span="16">
        <div class="h5-home-center">
          <div
            class="setting-page setting-page-h5"
            :class="{'setting-page-miniprogram': portal === 'miniprogram' }"
          >
          <find-header :portal="portal"></find-header>

            <!-- 操作预览区域 -->
            <div class="find-body">
              <draggable
                v-model="modules"
                :options="{
                  filter: stopDraggleClasses,
                  preventOnFilter: false,
                  forceFallback: true
                }"
                @start="startDrag"
                @end="endDrag"
              >
                <module-template
                  v-for="(module, index) in modules"
                  :key="index"
                  :saveFlag="saveFlag"
                  :startValidate="startValidate"
                  :index="index"
                  :module="module"
                  :active="isActive(index)"
                  :moduleKey="`${module.type}-${index}`"
                  @activeModule="activeModule"
                  @updateModule="updateModule($event, index)"
                  @removeModule="removeModule($event, index)"
                >
                </module-template>
              </draggable>
            </div>

            <find-footer :portal="portal"></find-footer>
          </div>
        </div>
      </el-col>
    </el-row>
    <!-- 发布预览按钮 -->
    <div class="setting-button-group">
      <el-button
        class="setting-button-group__button text-14 btn-border-primary"
        size="mini"
        @click="reset"
        :disabled="isLoading"
        >重 置</el-button
      >
      <el-button
        class="setting-button-group__button text-14 btn-border-primary"
        size="mini"
        @click="save('draft')"
        :disabled="isLoading"
        >预 览</el-button
      >
      <el-button
        class="setting-button-group__button text-14"
        type="primary"
        size="mini"
        @click="save('published')"
        :disabled="isLoading"
        >发 布</el-button
      >
    </div>
  </div>
</template>
<script>
import Api from "admin/api";
import * as types from "admin/store/mutation-types";
import {
  H5_MARKETING_MODULE,
  H5_BASE_MODULE
} from "admin/config/module-default-config";
import ModuleCounter from "admin/utils/module-counter";
import needUpgrade from "admin/utils/version-compare";
import pathName2Portal from "admin/config/api-portal-config";
import marketingMixins from "admin/mixins/marketing";
import ObjectArray2ObjectByKey from "@/utils/array2object";
import moduleTemplate from "./module-template";
import findFooter from "./footer";
import findHeader from "./header";
import draggable from "vuedraggable";
import { mapActions, mapState } from "vuex";

export default {
  name: "h5-home",
  components: {
    moduleTemplate,
    draggable,
    findFooter,
    findHeader
  },
  mixins: [marketingMixins],
  data() {
    return {
      windowHeight: document.documentElement.clientHeight,
      title: "EduSoho 微网校",
      modules: [],
      //保存标志，只有点击过保存或者预览按钮才开始实时校验，具体表现错误模块为有错误模块边框变红提示！。这里设置成为数字原因是：每次点击发布或者预览按钮时都需要去实时校验一次，
      saveFlag: 0,
      //非空提示，在点击发布或者预览按钮时才需要提示，具体表现为弹窗提示
      startValidate: false,
      incomplete: true,
      validateResults: [],
      currentModuleIndex: 0,
      baseModules: H5_BASE_MODULE,
      marketingModules: H5_MARKETING_MODULE,
      typeCount: {},
      pathName: this.$route.name,
      couponSwitch: 0,
      moduleLength: 0,
      menuStyle:{}//右侧菜单栏样式
    };
  },
  computed: {
    ...mapState([
      "isLoading",
      "vipLevels",
      "vipSettings",
      "vipSetupStatus",
      "vipPlugin"
    ]),
    stopDraggleClasses() {
      return (
        ".module-frame__setting, .find-footer," +
        ".search__container, .el-dialog__header, .el-dialog__footer"
      );
    },
    portal() {
      return pathName2Portal[this.pathName];
    }
  },
  created() {
    //设置样式
    this.setStyle();
    // 请求发现页配置
    this.load();
    // 获得课程分类列表
    this.getCourseCategories();
    // 获得班级分类列表
    this.getClassCategories();
    // 获得优惠券开关
    this.getCouponSwitch();
  },
  beforeDestroy(){
    document.getElementById("app").style.background="#ffffff";
  },
  methods: {
    ...mapActions([
      "getCourseCategories",
      "getClassCategories",
      "deleteDraft",
      "saveDraft",
      "getDraft"
    ]),
    setStyle(){
      //设置背景色
      const windowHeight = document.documentElement.clientHeight;
      document.getElementById("app").style.background="#f5f5f5";
      this.menuStyle = {
        height: this.windowHeight + "px",
        overflow: "auto"
      };
    },
    getCouponSwitch() {
      Api.getCouponSetting().then(res => {
        this.couponSwitch = parseInt(res.enabled, 10);
      });
    },
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
    },
    removeModule(data, index) {
      // 删除一个模块
      this.typeCount.removeByType(data.type);
      this.currentModuleIndex = Math.max(this.currentModuleIndex - 1, 0);
      this.modules.splice(index, 1);
    },
    scrollBottom(){
      const top =document.body.clientHeight;
      window.scroll({top:top,left:0,behavior:'smooth' });
    },
    addModule(data, index) {
      /*
       * 后台会员组件交互处理:
       * 会员插件未安装：隐藏按钮 (vipSetupStatus)
       * 会员插件未升级：/admin/app/upgrades (vipPlugin)
       * 未开通会员功能：/admin/setting/vip (vipSettings)
       * 开通会员但未配置会员等级：/admin/setting/vip/level (vipLevels)
       */
      switch (data.default.type) {
        case "vip":
          if (!this.vipSetupStatus) {
            return;
          } else if (needUpgrade("1.7.26", this.vipPlugin.version)) {
            this.$confirm("请升级会员插件", "提示", {
              confirmButtonText: "去升级",
              cancelButtonText: "取消"
            })
              .then(() => {
                window.open(window.location.origin + "/admin/app/upgrades");
              })
              .catch(() => {});
            return;
          } else if (
            !this.vipSettings ||
            !this.vipSettings.enabled ||
            !this.vipSettings.h5Enabled
          ) {
            this.$confirm("会员功能未开通", "提示", {
              confirmButtonText: "去开通",
              cancelButtonText: "取消"
            })
              .then(() => {
                window.open(window.location.origin + "/admin/setting/vip");
              })
              .catch(() => {});
            return;
          } else if (!this.vipLevels || !this.vipLevels.length) {
            this.$confirm("请先设置会员等级", "提示", {
              confirmButtonText: "去设置",
              cancelButtonText: "取消"
            })
              .then(() => {
                window.open(
                  window.location.origin + "/admin/setting/vip/level"
                );
              })
              .catch(() => {});
            return;
          }
          break;
        case "coupon":
          if (!this.couponSwitch) {
            this.$confirm("优惠券功能未开通", "提示", {
              confirmButtonText: "去开通",
              cancelButtonText: "取消"
            })
              .then(() => {
                window.open(window.location.origin + "/admin/setting/coupon");
              })
              .catch(() => {});
            return;
          }
          break;
        default:
          break;
      }

      // 新增一个模块
      if (
        data.default.type === "search" &&
        this.typeCount.getCounterByType(data.default.type) >= 1
      ) {
        this.$message({
          message: "搜索组件最多添加 1 个",
          type: "warning"
        });
        return;
      }

      if (this.typeCount.getCounterByType(data.default.type) >= 5) {
        this.$message({
          message: "同一类型组件最多添加 5 个",
          type: "warning"
        });
        return;
      }
      this.moduleLength = this.moduleLength + 1;
      this.typeCount.addByType(data.default.type);
      const defaultCopied = JSON.parse(JSON.stringify(data.default));
      defaultCopied.oldIndex = this.moduleLength;      //oldIndex用于组件的key,减少组件重新创建
      this.modules.push(defaultCopied);
      this.currentModuleIndex = Math.max(this.modules.length - 1, 0);
      //使用异步，保证组件添加完成再滑动到底部
      setTimeout(()=>{this.scrollBottom(),500})
    },
    load() {
      // 读取草稿配置
      const mode = this.$route.query.draft == 1 ? "draft" : "published";

      this.getDraft({
        portal: this.portal,
        type: "discovery",
        mode
      })
        .then(res => {
          //默认排列方式
          Object.keys(res).forEach((element, index) => {
            res[element] = this.formateAppDisplay(
              res[element].type, //测试数据，上线删除
              res[element]
            );
            res[element].oldIndex = index; //oldIndex用于组件的key,减少组件重新创建
          });
          this.moduleLength = Object.keys(res).length - 1;
          this.modules = Object.values(res);
          this.moduleCountInit();
        })
        .catch(err => {
          this.moduleCountInit();
          this.$message({
            message: err.message,
            type: "error"
          });
        });
    },
    //处理班级课程排列（可删）
    formateAppDisplay(type, item) {
      if (
        (type === "course_list" || type === "classroom_list") && this.portal === "h5") {
          item.data.displayStyle = "row";
      }
      return item;
    },
    reset() {
      parent.location.reload();
      // 删除草稿配置配置
      this.deleteDraft({
        portal: this.portal,
        type: "discovery",
        mode: "draft"
      })
        .then(res => {
          this.$message({
            message: "重置成功",
            type: "success"
          });
          this.load();
        })
        .catch(err => {
          this.$message({
            message: err.message || "重置失败",
            type: "error"
          });
        });
    },
    save(mode, needTrans = true) {
      this.startValidate = true;
      this.saveFlag++;
      // 验证提交配置
      const validateAndSubmit = () => {
        let data = this.modules;
        const isPublish = mode === "published";

        this.startValidate = false;

        this.validate();

        // 如果已经是对象就不用转换
        if (needTrans) {
          data = ObjectArray2ObjectByKey(this.modules, "moduleType");
        }
        if (this.incomplete) {
          return;
        }
        this.saveDraft({
          data,
          mode,
          portal: this.portal,
          type: "discovery"
        })
          .then(() => {
            this.saveFlag = 0;
            if (isPublish) {
              this.$message({
                message: "发布成功",
                type: "success"
              });
              return;
            }
            this.$store.commit(types.UPDATE_DRAFT, data);
            this.toPreview(isPublish);
          })
          .catch(err => {
            this.$message({
              message: err.message || "发布失败，请重新尝试",
              type: "error"
            });
          });
      };
      setTimeout(() => {
        validateAndSubmit();
      }, 500); // 点击 预览／发布 时去验证所有组件，会有延迟，目前 low 的解决方法延迟 500ms 判断验证结果
    },
    toPreview(isPublish) {
      this.$router.push({
        name: "preview",
        query: {
          times: 10,
          preview: isPublish ? 0 : 1,
          duration: 60 * 5,
          from: this.pathName
        }
      });
    },
    validate() {
      for (var i = 0; i < this.modules.length; i++) {
        if (this.validateResults[i]) {
          this.incomplete = this.validateResults[i];
          return;
        }
      }
      this.incomplete = false;
    },
    startDrag() {
      //开始拖动
      const settings = document.getElementsByClassName("module-frame__setting");
      for (let i = 0; i < settings.length; i++) {
        settings[i].style.display = "none";
      }
    },
    endDrag() {
      //结束拖动
      const settings = document.getElementsByClassName("module-frame__setting");
      settings[this.currentModuleIndex].style.display = "block";
    }
  }
};
</script>
