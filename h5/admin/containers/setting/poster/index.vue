<template>
  <module-frame containerClass="setting-poster" :isActive="isActive">
    <div slot="preview" :class="'poster-image-container ' +  imageMode[copyModuleData.responsive]">
      <div class="image-mask" v-show="!copyModuleData.image.uri">
        广告图片
      </div>
      <poster :poster="copyModuleData" :feedback="false"></poster>
    </div>

    <div slot="setting" class="poster-allocate">
      <header class="title">图片广告设置</header>
      <div class="poster-item-setting clearfix">
        <div class="poster-item-setting__section">
          <p class="pull-left section-left">广告图片：</p>
          <div class="section-right">
            <el-upload
              action="string"
              accept=".jpg,.jpeg,.png,.gif,.bmp,.JPG,.JPEG,.PBG,.GIF,.BMP"
              :http-request="uploadImg"
              :before-upload="beforeUpload"
              :show-file-list="false">
              <div class="image-uploader">
                <img v-show="copyModuleData.image.uri" :src="copyModuleData.image.uri" class="poster-img">
                <div class="uploader-mask" v-show="!copyModuleData.image.uri">
                  <span><i class="text-18">+</i> 添加图片</span>
                </div>
              </div>
            </el-upload>
          </div>
        </div>
        <div class="poster-item-setting__section mtl">
          <p class="pull-left section-left">链接：</p>
          <div class="section-right">
            <el-radio v-model="copyModuleData.link.type" label="course">站内课程</el-radio>
            <el-radio v-model="copyModuleData.link.type" label="url">自定义链接</el-radio>
          </div>
        </div>
        <div class="poster-item-setting__section mtl" v-show="copyModuleData.link.type === 'course'">
          <p class="pull-left section-left">课程名称：</p>
          <div class="section-right">
            <el-button type="info" size="mini" @click="openModal" v-show="!courseLinkText">选择课程</el-button>
            <el-tag class="courseLink" closable :disable-transitions="true" @close="handleClose" v-show="courseLinkText">
              <span class="text-content ellipsis">{{courseLinkText}}</span>
            </el-tag>
          </div>
        </div>
        <div class="poster-item-setting__section mtl" v-show="copyModuleData.link.type === 'url'">
          <p class="pull-left section-left">输入链接：</p>
          <div class="section-right">
            <el-input size="mini" v-model="copyModuleData.link.url" placeholder="请输入自定义链接" clearable></el-input>
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

    <course-modal slot="modal" :visible="modalVisible" limit=1 :courseList="courseSets" @visibleChange="modalVisibleHandler" @updateCourses="getUpdatedCourses">
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
    }
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object
    }
  },
  computed: {
    isActive: {
      get() {
        return this.active;
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
      if (!this.courseSets[0]) return

      return this.courseSets[0] ? this.courseSets[0].title || this.courseSets[0].courseSetTitle : '';
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
        .then((data) => {
          this.copyModuleData.image = data;
          console.log(data)
        })
        .catch((err) => {
          console.log(err, 'error');
        });
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal() {
      this.modalVisible = true;
    },
    getUpdatedCourses(courses) {
      this.courseSets = courses;
      if (!courses.length) return;

      this.moduleData.data.link.target = {
        id: courses[0].id,
        title: courses[0].title || courses[0].courseSetTitle,
      };
    },
    removeCourseLink() {
      this.courseSets = [];
    },
    handleClose() {
      this.removeCourseLink();
      this.linkTextShow = false;
    },
  }
}

</script>
