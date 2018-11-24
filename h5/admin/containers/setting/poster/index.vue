<template>
  <module-frame containerClass="setting-poster" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="poster-image-container">
      <div class="image-mask" v-show="!copyModuleData.image.uri">
        广告图片
      </div>
      <poster :class="imageMode[copyModuleData.responsive]" :poster="copyModuleData" :feedback="false"></poster>
    </div>

    <div slot="setting" class="poster-allocate">
      <header class="title">
        图片广告设置
        <div class="help-text">建议图片宽度为750px,支持jpg/png/gif格式，图片大小不超过2MB</div>
      </header>
      <div class="poster-item-setting clearfix">
        <div class="poster-item-setting__section">
          <p class="pull-left section-left required-option">广告图片：</p>
          <div class="section-right">
            <el-upload
              action="string"
              accept=".jpg,.jpeg,.png,.gif,.bmp,.JPG,.JPEG,.PBG,.GIF,.BMP"
              :http-request="uploadImg"
              :before-upload="beforeUpload"
              :show-file-list="false">
              <div class="image-uploader">
                <img v-show="copyModuleData.image.uri" :src="copyModuleData.image.uri" class="poster-img">
                <div class="add-img" v-show="!copyModuleData.image.uri">
                  <span><i class="text-18">+</i> 添加图片</span>
                </div>
                <div class="uploader-mask" v-show="copyModuleData.image.uri">更换图片</div>
              </div>
            </el-upload>
          </div>
        </div>
        <div class="poster-item-setting__section mtl">
          <p class="pull-left section-left">链接：</p>
          <div class="section-right">
            <el-radio v-model="copyModuleData.link.type" label="course">站内课程</el-radio>
            <el-radio v-if="pathName === 'h5Setting'" v-model="copyModuleData.link.type" label="url">自定义链接</el-radio>
          </div>
        </div>
        <div class="poster-item-setting__section mtl" v-show="copyModuleData.link.type === 'course'">
          <p class="pull-left section-left">课程名称：</p>
          <div class="section-right">
            <el-dropdown @command="insideLinkHandle" v-show="!courseLinkText">
              <span class="el-dropdown-link">
                {{linkText}}<i class="el-icon-arrow-down el-icon--right"></i>
              </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item :command="item" v-for="item in linkOptions" :key="item.value">{{item.label}}</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
            <el-tag class="courseLink" closable :disable-transitions="true" @close="handleClose" v-show="courseLinkText">
              <el-tooltip class="text-content ellipsis" effect="dark" placement="top">
                <span slot="content">{{courseLinkText}}</span>
                <span>{{ courseLinkText }}</span>
              </el-tooltip>
            </el-tag>
          </div>
        </div>
        <div class="poster-item-setting__section mtl" v-show="copyModuleData.link.type === 'url'">
          <p class="pull-left section-left">输入链接：</p>
          <div class="section-right">
            <el-input size="mini" v-model="copyModuleData.link.url" placeholder="例如 http://www.eduosho.com" clearable></el-input>
          </div>
        </div>
        <div class="poster-item-setting__section mtl">
          <p class="pull-left section-left">自适应手机屏幕：</p>
          <div class="section-right">
            <el-radio v-model="copyModuleData.responsive" label="1">开启</el-radio>
            <el-radio v-model="copyModuleData.responsive" label="0">关闭</el-radio>
          </div>
        </div>
      </div>
    </div>

    <course-modal
      slot="modal"
      :visible="modalVisible"
      :type="type"
      limit=1
      :courseList="courseSets"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses">
    </course-modal>
  </module-frame>
</template>
<script>
import Api from '@admin/api';
import moduleFrame from '../module-frame'
import courseModal from '../course/modal/course-modal';
import poster from '@/containers/components/e-poster/e-poster.vue';


export default {
  components: {
    moduleFrame,
    courseModal,
    poster
  },
  data() {
    return {
      modalVisible: false,
      imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
      courseSets: [],
      imageMode: [
        'responsive',
        'size-fit',
      ],
      linkOptions: [{
        type: 'course_list',
        label: '选择课程',
      }, {
        type: 'class_list',
        label: '选择班级',
      }],
      pathName: this.$route.name,
      type: 'course_list',
      linkText: '选择课程'
    }
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object
    },
    incomplete: {
      type: Boolean,
      default: false,
    }
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {}
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {}
    },
    copyModuleData: {
      get() {
        return this.moduleData.data;
      },
      set() {}
    },
    courseLinkText() {
      if (this.courseSets[0]) {
        return this.courseSets[0].displayedTitle;
      }
      if (this.copyModuleData.link.target.title) {
        return this.copyModuleData.link.target.title;
      }
      return;
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
  },
  methods: {
    beforeUpload(file) {
      const type = file.type;
      const size = file.size / 1024 / 1024;

      if (type.indexOf('image') === -1) {
        this.$message({
          message: '文件类型仅支持图片格式',
          type: 'error'
        });
        return false;
      }

      if (size > 2) {
        this.$message({
          message: '文件大小不得超过 2 MB',
          type: 'error'
        });
        return false;
      }
    },
    uploadImg(item) {
      let formData = new FormData()
      formData.append('file', item.file)
      formData.append('group', 'system')
      Api.uploadFile({
          data: formData
        })
        .then(data => {
          if (this.pathName !== 'h5Setting') {
            // 小程序后台替换图片协议
            data.uri = data.uri.replace(/^(\/\/)|(http:\/\/)/, 'https://');
          }
          this.copyModuleData.image = data;
          this.$message({
            message: '图片上传成功',
            type: 'success'
          });
        })
        .catch(err => {
          this.$message({
            message: err.message,
            type: 'error'
          });
        });
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    getUpdatedCourses(courses) {
      this.courseSets = courses;
      if (!courses.length) return;

      this.moduleData.data.link.target = {
        id: courses[0].id,
        title: courses[0].title || courses[0].courseSetTitle,
        courseSetId: courses[0].courseSet.id,
      };
    },
    removeCourseLink() {
      this.courseSets = [];
      this.$set(this.copyModuleData.link, 'target', {});
    },
    handleClose() {
      this.removeCourseLink();
    },
    insideLinkHandle(commands) {
      this.modalVisible = true;
      this.type = commands.type;
      this.linkText = commands.label;
    }
  }
}

</script>
