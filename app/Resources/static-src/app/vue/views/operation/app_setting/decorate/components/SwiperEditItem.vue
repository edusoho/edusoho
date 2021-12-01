<template>
  <div class="image-item clearfix">
    <a-icon
      class="remove-btn"
      type="close-circle"
      theme="filled"
      @click="handleClickRemove"
    />
    <div class="image-item__img pull-left">
      <img :src="item.image.url" />
      <a-upload
        accept="image/*"
        :file-list="[]"
        :customRequest="() => {}"
        @change="changeUploadPicture"
      >
        <div class="re-upload">修改</div>
      </a-upload>
    </div>
    <div class="image-item__content pull-left">
      <a-dropdown>
        <a class="ant-dropdown-link" @click="(e) => e.preventDefault()">
          站内链接<a-icon type="down" />
        </a>
        <a-menu slot="overlay" @click="selectLink">
          <a-menu-item key="course">选择课程</a-menu-item>
          <a-menu-item key="classroom">选择班级</a-menu-item>
          <a-menu-item key="vip">选择会员</a-menu-item>
        </a-menu>
      </a-dropdown>
      <div style="margin-top: 18px;" @click="selectLink({ key: 'custom' })">
        <a class="ant-dropdown-link" @click="(e) => e.preventDefault()">
          自定义链接
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SwiperEditItem',

  props: {
    item: {
      type: Object,
      required: true
    },

    index: {
      type: Number,
      required: true
    }
  },

  methods: {
    changeUploadPicture(info) {
      const reader = new FileReader();

      reader.onload = (event) => {
        const params = {
          type: 'edit',
          index: this.index,
          imgUrl: event.target.result,
          imgName: info.file.originFileObj.name
        };
        this.$emit('update-image', params);
      };
      reader.readAsDataURL(info.file.originFileObj);
    },

    selectLink({ key }) {
      const params = {
        type: key,
        index: this.index
      };
      this.$emit('select-link', params);
    },

    handleClickRemove() {
      const params = {
        index: this.index
      };
      this.$emit('remove', params);
    }
  }
}
</script>

<style lang="less" scoped>
.image-item {
  position: relative;
  padding: 10px 6px;
  margin-bottom: 10px;
  width: 100%;
  border-radius: 2px;
  border: 1px solid #eee;
  background-color: #fff;
  font-size: 12px;
  cursor: move;

  .remove-btn {
    position: absolute;
    top: -6px;
    right: -6px;
    display: none;
    font-size: 18px;
    color: #bbb;
    text-align: center;
    cursor: pointer;
    transform: all .3s ease;

    &:hover {
      color: #aaa;
    }
  }

  &__img {
    position: relative;
    margin-right: 10px;
    width: 150px;
    height: 60px;
    border-radius: 2px;

    img {
      width: 100%;
    }

    .re-upload {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 20px;
      line-height: 20px;
      cursor: pointer;
      opacity: 0.5;
      font-size: 12px;
      text-align: center;
      color: #fff;
      background: black;
    }
  }

  &:hover {
    .remove-btn {
      display: block;
    }
  }
}
</style>
