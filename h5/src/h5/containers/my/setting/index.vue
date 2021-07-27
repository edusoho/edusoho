<template>
  <div class="my_setting">
    <e-loading v-if="isLoading" />
    <div
      v-for="(item, index) in settings"
      class="my_setting-item"
      :key="index"
      @click="handleSetting(index)"
    >
      <span class="my_setting-title title-18">{{ $t(item.name) }}</span>
      <div class="my_setting-content">
        <img
          v-if="!index"
          :src="item.info || option.img"
          alt=""
          class="my_setting-avatar"
        />
        <span v-if="index">{{ item.info }}</span>
        <img src="static/images/more.png" alt="" class="my_setting-more" />
      </div>
      <van-uploader v-if="!index" :before-read="beforeUpload">
        <van-popup v-model="dialogVisible" :overlay="false" position="top">
          <div class="cropper-container">
            <vueCropper
              v-show="option.img"
              ref="cropper"
              :img="option.img"
              :fixed="option.fixed"
              :enlarge="option.enlarge"
              :auto-crop="option.autoCrop"
              :fixed-number="option.fixedNumber"
              :auto-crop-width="option.autoCropWidth"
              :auto-crop-height="option.autoCropHeight"
            />
          </div>
          <div class="dialog-footer">
            <van-button @click="dialogVisible = false">{{ $t('btn.cancel') }}</van-button>
            <van-button type="primary" @click="stopCropFn">{{ $t('btn.confirm') }}</van-button>
          </div>
        </van-popup>
      </van-uploader>
    </div>
    <div class="log-out-btn title-18" @click="logout">
      <span>{{ $t('btn.dropOut') }}</span>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import { Toast, Dialog } from 'vant';
import Api from '@/api';
import * as types from '@/store/mutation-types';

import { VueCropper } from 'vue-cropper';

export default {
  components: {
    VueCropper,
  },
  data() {
    return {
      settings: [
        {
          name: 'setting.heads',
          info: '',
        },
        {
          name: 'setting.nickname',
          info: '',
        },
        {
          name: 'setting.language',
          info: ''
        },
      ],
      dialogVisible: false,
      imageCropped: false,
      option: {
        img: '',
        autoCrop: true,
        autoCropWidth: 200,
        autoCropHeight: 200,
        fixedNumber: [1, 1],
        fixed: true,
        high: false,
        enlarge: 2,
      },
    };
  },
  computed: {
    ...mapState({
      user: state => state.user,
      isLoading: state => state.isLoading,
    }),
  },
  created() {
    this.$set(this.settings[0], 'info', this.user.avatar.large);
    this.$set(this.settings[1], 'info', this.user.nickname);
    this.$set(this.settings[2], 'info', this.$t('lang.language'));
  },
  methods: {
    ...mapActions(['setAvatar']),
    handleSetting(index) {
      switch (index) {
        case 0:
          break;
        case 1:
          this.$router.push({
            name: 'setting_nickname',
            query: {
              nickname: this.user.nickname == '' ? '' : this.user.nickname,
            },
          });
          break;
        case 2:
          this.$router.push({
            name: 'settingLang',
          });
          break;
        default:
          break;
      }
    },
    logout() {
      Dialog.confirm({
        title: this.$t('setting.dropOut'),
        message: this.$t('setting.dropOutCancelConfirm'),
        confirmButtonText: this.$t('btn.confirm'),
        cancelButtonText: this.$t('btn.cancel')
      }).then(() => {
        this.$store.commit(types.USER_LOGIN, {
          token: '',
          user: {},
        });
        window.localStorage.setItem('mobile_bind_skip', false);
        this.$router.push({
          name: 'my',
        });
      });
    },
    stopCropFn() {
      const $cropper = this.$refs.cropper[0];
      $cropper.stopCrop();
      this.dialogVisible = false;
      $cropper.getCropData(data => {
        this.imageCropped = true;
        this.uploadImg(data);
        this.option.img = data;
      });
    },
    beforeUpload(file) {
      const type = file.type;
      const size = file.size / 1024 / 1024;

      if (type.indexOf('image') === -1) {
        Toast.fail(this.$t('setting.fileTypeOnlySupportsImageFormat'));
        return;
      }

      if (size > 2) {
        Toast.fail(this.$t('setting.fileSizeMustNotExceed2MB'));
        return;
      }

      this.dialogVisible = true;
      const reader = new FileReader();
      reader.onload = () => {
        this.option.img = reader.result;
      };
      reader.readAsDataURL(file);
    },
    uploadImg(file) {
      if (!this.imageCropped) return;
      this.imageCropped = false;
      const formData = new FormData();
      formData.append('file', file);
      formData.append('group', 'user');
      Api.updateFile({
        data: formData,
      })
        .then(res => {
          this.$set(this.settings[0], 'info', file.content);
          this.setAvatar({
            avatarId: res.id,
          })
            .then(() => {
              Toast.success(this.$t('setting.modifySuccess'));
            })
            .catch(err => {
              Toast.fail(err.message);
            });
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
  },
};
</script>
<style scode>
.my_setting .van-popup {
  height: 100%;
}
.cropper-container {
  height: 85%;
}
.dialog-footer {
  position: absolute;
  bottom: 40px;
  left: 0;
  width: 100%;
  padding-top: 40px;
  background: #fff;
  text-align: center;
}
.dialog-footer .van-button {
  margin: 0 20px;
}
</style>
