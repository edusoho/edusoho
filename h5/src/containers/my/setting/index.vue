<template>
  <div class="my_setting">
    <e-loading v-if="isLoading"></e-loading>
    <div class="my_setting-item" v-for="(item, index) in settings" @click="handleSetting(index)">
      <span class="my_setting-title title-18">{{item.name}}</span>
      <div class="my_setting-content">
        <img :src="item.info || option.img" alt="" v-if="!index" class="my_setting-avatar">
        <span v-if="index">{{item.info}}</span>
        <img src="static/images/more.png" alt="" class="my_setting-more">
      </div>
      <van-uploader :before-read="beforeUpload" v-if="!index">
        <van-popup v-model="dialogVisible" position="top" :overlay="false">
          <div class="cropper-container">
            <vueCropper
              ref="cropper"
              v-show="option.img"
              :img="option.img"
              :fixed="option.fixed"
              :enlarge="option.enlarge"
              :autoCrop="option.autoCrop"
              :fixedNumber="option.fixedNumber"
              :autoCropWidth="option.autoCropWidth"
              :autoCropHeight="option.autoCropHeight"
            ></vueCropper>
          </div>
        <div class="dialog-footer">
          <van-button @click="dialogVisible = false">取 消</van-button>
          <van-button type="primary" @click="stopCropFn">确 定</van-button>
        </div>
        </van-popup>
      </van-uploader>
    </div>
    <div class="log-out-btn title-18" @click="logout"><span>退出登录</span></div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import { Toast } from 'vant';
import Api from '@/api';
import * as types from '@/store/mutation-types';
import store from '@/store';
import { Dialog } from 'vant';
import { VueCropper } from 'vue-cropper';

export default {
  components: {
    VueCropper
  },
  data() {
    return {
      settings: [{
        name: '头像',
        info: '',
      }, {
        name: '用户名',
        info: ''
      // }, {
      //   name: '手机',
      //   info: ''
      }],
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
      }
    }
  },
  computed: {
    ...mapState({
      user: state => state.user,
      isLoading: state => state.isLoading
    })
  },
  created() {
    this.$set(this.settings[0], 'info', this.user.avatar.large);
    this.$set(this.settings[1], 'info', this.user.nickname);
    // this.$set(this.settings[2], 'info', this.user.school);
  },
  methods: {
    ...mapActions([
      'setAvatar'
    ]),
    handleSetting(index) {
      switch(index) {
        case 0:
          break;
        case 1:
          this.$router.push({
            name: 'setting_nickname',
            query: {
              nickname: (this.user.nickname == '') ? '' : (this.user.nickname)
            }
          });
          break;
        case 2:
          Toast('更改手机号，后续开通');
          break;
        default:
          break;
      }
    },
    logout() {
      Dialog.confirm({
        title: '退出登录',
        message: '确定要退出登录吗？'
      }).then(() => {
        this.$store.commit(types.USER_LOGIN, {
          token: '',
          user: {}
        });
        this.$router.push({
          name: 'my'
        })
      })
    },
    stopCropFn() {
      const $cropper = this.$refs.cropper[0];
      $cropper.stopCrop()
      this.dialogVisible = false;
      $cropper.getCropData((data) => {
        this.imageCropped = true;
        this.uploadImg(data);
        this.option.img = data;
      })
    },
    beforeUpload(file) {
      const type = file.type;
      const size = file.size / 1024 / 1024;

      if (type.indexOf('image') === -1) {
        Toast.fail('文件类型仅支持图片格式');
        return;
      }

      if (size > 2) {
        Toast.fail('文件大小不得超过 2 MB');
        return;
      }

      this.dialogVisible = true;
      const reader = new FileReader();
      reader.onload = () => {
        this.option.img = reader.result;
      }
      reader.readAsDataURL(file)
    },
    uploadImg(file) {
      if (!this.imageCropped) return;
      this.imageCropped = false;
      let formData = new FormData()
      formData.append('file', file)
      formData.append('group', 'user')
      Api.updateFile({
        data: formData
      })
      .then(res => {
        this.$set(this.settings[0], 'info', file.content);
        this.setAvatar({
          avatarId: res.id
        }).then(() => {
          Toast.success('修改成功');
        }).catch(err => {
          Toast.fail(err.message)
        })
      })
      .catch((err) => {
        Toast.fail(err.message)
      });
    }
  }
}
</script>
<style>
  .van-popup {
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
