<template>
  <div class="my_setting">
    <e-loading v-if="isLoading"></e-loading>
    <div class="my_setting-item" v-for="(item, index) in settings" @click="handleSetting(index)">
      <span class="my_setting-title title-18">{{item.name}}</span>
      <div class="my_setting-content">
        <img :src="item.info" alt="" v-if="!index" class="my_setting-avatar">
        <span  v-if="index">{{item.info}}</span>
        <img src="/static/images/more.png" alt="" class="my_setting-more">
      </div>
      <van-uploader :after-read="onRead" v-if="!index"></van-uploader>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import { Toast } from 'vant';
import Api from '@/api';

export default {
  data() {
    return {
      settings: [{
        name: '头像',
        info: '',
      }, {
        name: '昵称',
        info: ''
      }, {
        name: '手机',
        info: ''
      }]
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
    this.$set(this.settings[2], 'info', this.user.school);
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
            name: 'setting_nickname'
          });
          break;
        case 2:
          Toast('更改手机号，后续开通');
          break;
        default:
          break;
      }
    },
    onRead(file) {
      Api.updateFile({
        data: {
          file: file.content,
          group:'user'
        }
      }).then(res => {
        this.$set(this.settings[0], 'info', file.content);
        this.setAvatar({
          avatarId: res.id
        }).then(() => {
          Toast.success('修改成功');
        }).catch(err => {
          Toast.fail(err.message)
        })
      })
    }
  }
}
</script>
