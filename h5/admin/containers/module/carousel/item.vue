<template>
  <div class="carousel-item clearfix" :class="{ active: isActive }" @click="selected(item)">
   <!--  <van-uploader :after-read="onRead" class="add-img">
      <img class='carousel-img' :src="item.image"/>
      <span v-show="!item.image"><i class="text-18">+</i> 添加图片</span>
    </van-uploader> -->
    <el-upload
      class="add-img"
      action="string"
      :http-request="uploadImg"
      :on-preview="handlePictureCardPreview"
      :on-remove="handleRemove"
      :show-file-list="false"
      @progress="">
      <img v-if="imageUrl" :src="imageUrl" class="avatar">
      <i class="text-18">+</i> 添加图片
    </el-upload>
    <div class="add-title pull-left">标题：<input type="text" placeholder="请输入标题"></div>
    <div class="pull-left">链接：<button class="btn-gray btn-choose-course">选择课程</button></div>
  </div>
</template>

<script>
  import Api from '@admin/api';

  export default {
    props: ['item', 'index'],
    data() {
      return {
        imageUrl: '',
        isActive: false,
      };
    },
    created() {
      // Api.uploadFile({
      //   data: {
      //     file: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
      //     group : 'tmp'
      //   }
      // })
      // .then((data) => {
      //   console.log(data)
      // })
      // .catch((err) => {
      //   console.log(err, 'error');
      // });
    },
    methods: {
      // onRead(file) {
      //   console.log(file);
      //   this.item.image = file.content;
      // },
      uploadImg(item) {
        console.log('上传图片接口-参数', item)
        Api.uploadFile({
          data: {
            file: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
            group : 'system'
          }
        })
        .then((data) => {
          console.log(data)
        })
        .catch((err) => {
          console.log(err, 'error');
        });
      },
      selected(item) {
        this.imgAdress = item.image;
        this.isActive = true;
        const miaomiaomiao = true;
        this.$emit('selected', miaomiaomiao);
      },
      handleRemove(file, fileList) {
        console.log(file, fileList);
      },
      handlePictureCardPreview(file) {
        this.dialogImageUrl = file.url;
        this.dialogVisible = true;
      },
      handleAvatarSuccess(res, file) {
        this.imageUrl = URL.createObjectURL(file.raw);
      },
      beforeAvatarUpload(file) {
        const isJPG = file.type === 'image/jpeg';
        const isLt2M = file.size / 1024 / 1024 < 2;

        if (!isJPG) {
          this.$message.error('上传头像图片只能是 JPG 格式!');
        }
        if (!isLt2M) {
          this.$message.error('上传头像图片大小不能超过 2MB!');
        }
        return isJPG && isLt2M;
      }
    }
  }

</script>