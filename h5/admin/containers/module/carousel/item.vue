<template>
  <div class="carousel-item clearfix" :class="active === index ? 'active' : ''" @click="selected(item, index)">
    <el-upload
      class="add-img"
      action="string"
      :http-request="uploadImg"
      :on-preview="handlePictureCardPreview"
      :on-remove="handleRemove"
      :show-file-list="false"
      >
      <img class="carousel-img" :src="item.image">
      <span v-show="!item.image"><i class="text-18">+</i> 添加图片</span>
    </el-upload>
    <i class="h5-icon h5-icon-cuowu1 icon-delete" @click="handleRemove"></i>
    <div class="add-title pull-left">标题：<input type="text" placeholder="请输入标题"></div>
    <div class="pull-left">链接：<button class="btn-gray btn-choose-course">选择课程</button></div>
  </div>
</template>

<script>
  import Api from '@admin/api';

  export default {
    props: ['item', 'index', 'active'],
    data() {
      return {
        // activeIndex: 0,
      };
    },
    methods: {
      uploadImg(item) {
        let formData = new FormData()
        formData.append('file', item.file)
        formData.append('group', 'system')

        Api.uploadFile({
          data: formData
        })
        .then((data) => {
          this.item.image = data.uri;
          this.$emit('selected',
          {
            activeStatus: true,
            imageUrl: this.item.image
          }
        );
          console.log(data)
        })
        .catch((err) => {
          console.log(err, 'error');
        });
      },
      selected(item, index) {
        this.imgAdress = item.image;
        const activeStatus = this.isActive;
        this.$emit('selected',
          {
            selectIndex: index,
            activeStatus: true,
            imageUrl: this.item.image
          }
        );
      },
      handleRemove(e) {
        e.target.parentNode.remove();
      },
      handlePictureCardPreview(file) {
        this.dialogImageUrl = file.url;
        this.dialogVisible = true;
      },
    }
  }

</script>