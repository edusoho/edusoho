<template>
  <div class="carousel-item clearfix" :class="{ active: active === index }" @click="selected(index)">
    <el-upload
      class="add-img"
      action="string"
      :http-request="uploadImg"
      :show-file-list="false"
      >
      <img class="carousel-img" :src="item.image.uri" v-show="item.image.uri">
      <span v-show="!item.image.uri"><i class="text-xlarge">+</i> 添加图片</span>
    </el-upload>
    <img class="icon-delete" src="static/images/delete.png" v-show="active === index" @click="handleRemove($event, index, itemNum)">
    <div class="add-title pull-left">标题：<el-input size="mini" v-model="item.title" placeholder="请输入标题" clearable></el-input>
    </div>
    <div class="pull-left">链接：<el-button type="info" size="mini" @click="openModal" v-show="!linkTextShow">选择课程</el-button>
      <el-tag
        class="courseLink"
        closable
        :disable-transitions="true"
        @close="handleClose"
        v-show="linkTextShow">
        <span class="text-content ellipsis">{{ linkTextShow }}</span>
      </el-tag>
    </div>
  </div>
</template>

<script>
  import Api from '@admin/api';

  export default {
    props: ['item', 'index', 'active', 'itemNum', 'courseSets'],
    data() {
      return {
        activeIndex: this.active,
      };
    },
    computed: {
      linkTextShow() {
        return this.item.link.target && this.item.link.target.title;
      },
    },
    watch: {
      courseSets(sets) {
        console.log(sets, 'courseSets')
        if (sets.length) {
          this.item.link.target = {
            id: sets[0].id,
            title: sets[0].title
          }
        } else {
          this.item.link.target = {};
        }
      }
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
          this.item.image.uri = data.uri;
          this.$emit('selected',
          {
            selectIndex: this.activeIndex,
            imageUrl: data.uri
          });
        })
        .catch((err) => {
          console.log(err, 'error');
        });
      },
      selected(index) {
        this.imgAdress = this.item.image.uri;
        this.activeIndex = index;
        this.$emit('selected',
          {
            selectIndex: index,
            imageUrl: this.item.image.uri
          }
        );
      },
      handleRemove(e, index, length) {
        e.stopPropagation();
        if (length > 1) {
          this.$emit('remove', index);
        } else {
          this.$message({
            message: '至少要留一张轮播图',
            type: 'warning'
          });
        }
      },
      handlePictureCardPreview(file) {
        this.dialogImageUrl = file.url;
        this.dialogVisible = true;
      },
      openModal() {
        this.$emit('chooseCourse');
      },
      handleClose() {
        this.$emit('removeCourseLink', this.index);
      },
    }
  }

</script>
