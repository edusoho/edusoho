<template>
  <module-frame
    containerClass="setting-poster"
    :isActive="isActive"
    :isIncomplete="isIncomplete"
  >
    <div slot="preview" class="poster-image-container">
      <div class="image-mask" v-show="!copyModuleData.image.uri">
        {{ $t('imageAds.adPictures2') }}
      </div>
      <poster
        v-show="copyModuleData.image.uri"
        :class="imageMode[copyModuleData.responsive]"
        :poster="copyModuleData"
        :feedback="false"
      ></poster>
    </div>

    <div slot="setting" class="poster-allocate">
      <e-suggest
        v-if="moduleData.tips"
        :suggest="moduleData.tips"
        :key="moduleData.moduleType"
      ></e-suggest>
      <header class="title">
        {{ $t('imageAds.imageAdsSettings') }}
        <div class="help-text">
          {{ $t('imageAds.tips') }}
        </div>
      </header>

      <div class="default-allocate__content clearfix">
        <setting-cell
          :title="$t('imageAds.adPictures')"
          customClass="poster-item-setting__section"
          leftClass="required-option"
        >
          <el-upload
            action="string"
            accept=".jpg,.jpeg,.png,.gif,.bmp,.JPG,.JPEG,.PBG,.GIF,.BMP"
            :http-request="uploadImg"
            :before-upload="beforeUpload"
            :show-file-list="false"
          >
            <div class="image-uploader">
              <img
                v-show="copyModuleData.image.uri"
                :src="copyModuleData.image.uri"
                class="poster-img"
              />
              <div class="add-img" v-show="!copyModuleData.image.uri">
                <span><i class="text-18">+</i> {{ $t('imageAds.addPictures') }}</span>
              </div>
              <div class="uploader-mask" v-show="copyModuleData.image.uri">
                {{ $t('imageAds.replacePicture') }}
              </div>
            </div>
          </el-upload>
        </setting-cell>

        <setting-cell :title="$t('imageAds.links')" customClass="poster-item-setting__section">
          <el-radio v-model="radio" label="insideLink">{{ $t('imageAds.siteLink') }}</el-radio>
          <el-radio
            v-if="pathName !== 'miniprogramSetting'"
            v-model="radio"
            label="url"
            >{{ $t('imageAds.customLink') }}</el-radio
          >
        </setting-cell>

        <setting-cell
          title=""
          customClass="poster-item-setting__section"
          v-show="radio !== 'url'"
        >
          <el-dropdown v-show="!courseLinkText">
            <el-button size="mini" class="el-dropdown-link">
              {{ $t('imageAds.addLink') }}
            </el-button>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item
                @click.native="insideLinkHandle(item.type)"
                v-for="item in linkOptions"
                :key="item.key"
                >{{ item.label }}</el-dropdown-item
              >
            </el-dropdown-menu>
          </el-dropdown>
          <el-tag
            class="courseLink"
            closable
            :disable-transitions="true"
            @close="handleClose"
            v-show="courseLinkText"
          >
            <el-tooltip
              class="text-content ellipsis"
              effect="dark"
              placement="top"
            >
              <span slot="content">{{ courseLinkText }}</span>
              <span>{{ courseLinkText }}</span>
            </el-tooltip>
          </el-tag>
        </setting-cell>

        <setting-cell
          title="输入链接："
          customClass="poster-item-setting__section"
          v-show="radio === 'url'"
        >
          <el-input
            size="mini"
            v-model="copyModuleData.link.url"
            placeholder="例如 http://www.eduosho.com"
            clearable
          >
          </el-input>
        </setting-cell>

        <setting-cell
          :title="$t('imageAds.adaptiveMobilePhoneScreen')"
          customClass="poster-item-setting__section"
        >
          <el-radio v-model="copyModuleData.responsive" label="1"
            >{{ $t('btn.open') }}</el-radio
          >
          <el-radio v-model="copyModuleData.responsive" label="0"
            >{{ $t('btn.close') }}</el-radio
          >
        </setting-cell>
      </div>
    </div>

    <course-modal
      slot="modal"
      :visible="modalVisible"
      :type="
        ['course_list', 'classroom_list'].includes(type) ? type : 'course_list'
      "
      limit="1"
      :courseList="courseSets"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses"
    >
    </course-modal>
  </module-frame>
</template>
<script>
import Api from 'admin/api';
import moduleFrame from '../module-frame';
import settingCell from '../module-frame/setting-cell';
import courseModal from '../course/modal/course-modal';
import poster from '&/components/e-poster/e-poster.vue';
import suggest from '&/components/e-suggest/e-suggest.vue';

export default {
  components: {
    moduleFrame,
    settingCell,
    courseModal,
    poster,
    'e-suggest': suggest,
  },
  data() {
    return {
      modalVisible: false,
      imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
      courseSets: [],
      imageMode: ['responsive', 'size-fit'],
      linkOptions: [
        {
          key: 0,
          type: 'course_list',
          label: this.$t('imageAds.chooseCourse')
        },
        {
          key: 1,
          type: 'classroom_list',
          label: this.$t('imageAds.chooseClass')
        },
        {
          key: 2,
          type: 'vip',
          label: this.$t('imageAds.chooseMember')
        },
      ],
      pathName: this.$route.name,
      type: 'course_list',
      radio: 'insideLink',
    };
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object,
    },
    incomplete: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {},
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {},
    },
    copyModuleData: {
      get() {
        return this.moduleData.data;
      },
      set() {},
    },
    courseLinkText: {
      // eslint-disable-next-line vue/return-in-computed-property
      get() {
        if (this.type === 'vip') {
          return '会员专区';
        }
        const data = this.courseSets[0];
        if (data) {
          return this.type === 'course_list' ? data.displayedTitle : data.title;
        }
        if (this.copyModuleData.link.target) {
          return this.copyModuleData.link.target.title;
        }
      },
      set() {},
    },
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
    radio(type) {
      const linkData = this.moduleData.data.link;
      if (type === 'insideLink') {
        const radioType =
          this.type === 'classroom_list' ? 'classroom' : 'course';
        linkData.type = radioType;
        return;
      }
      if (this.pathName !== 'miniprogramSetting') {
        linkData.type = 'url';
      }
    },
  },
  created() {
    if (this.pathName !== 'miniprogramSetting') {
      this.type = this.moduleData.data.link.type;
      if (this.moduleData.data.link.type === 'url') {
        this.radio = 'url';
      }
    }
  },
  methods: {
    beforeUpload(file) {
      const type = file.type;
      const size = file.size / 1024 / 1024;
      let message = '';

      if (type.indexOf('image') === -1) {
        message = '文件类型仅支持图片格式';
      } else if (size > 2) {
        message = '文件大小不得超过 2 MB';
      }

      if (message) {
        this.$message({
          type: 'error',
          message,
        });
        return false;
      }
    },
    uploadImg(item) {
      const formData = new FormData();
      formData.append('file', item.file);
      formData.append('group', 'system');
      Api.uploadFile({
        data: formData,
      })
        .then(data => {
          if (this.pathName === 'miniprogramSetting') {
            // 小程序后台替换图片协议
            data.uri = data.uri.replace(/^(\/\/)|(http:\/\/)/, 'https://');
          }
          this.copyModuleData.image = data;
          this.$message({
            message: '图片上传成功',
            type: 'success',
          });
        })
        .catch(err => {
          this.$message({
            message: err.message,
            type: 'error',
          });
        });
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    getUpdatedCourses(data) {
      this.courseSets = data;
      if (!data.length) return;

      if (this.type === 'classroom_list') {
        this.moduleData.data.link.target = {
          id: data[0].id,
          title: data[0].title,
          courseSetId: data[0].id,
        };
        this.moduleData.data.link.type = 'classroom';
        return;
      }

      this.moduleData.data.link.target = {
        id: data[0].id,
        title: data[0].title || data[0].courseSetTitle,
        courseSetId: data[0].courseSet.id,
        displayedTitle: data[0].displayedTitle,
      };
      this.moduleData.data.link.type = 'course';
    },
    removeCourseLink() {
      this.courseSets = [];
      this.$set(this.copyModuleData.link, 'target', null);
    },
    handleClose() {
      this.type = '';
      this.moduleData.data.link.type = '';
      this.removeCourseLink();
    },
    insideLinkHandle(value) {
      if (value !== 'vip') {
        this.modalVisible = true;
      } else {
        this.moduleData.data.link.type = 'vip';
      }
      this.type = value;
    },
  },
};
</script>
