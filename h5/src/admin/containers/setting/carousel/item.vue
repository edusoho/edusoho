<template>
  <div :class="{ active: active === index }" class="carousel-item clearfix" @click="selected(index)">
    <el-upload
      :http-request="uploadImg"
      :before-upload="beforeUpload"
      :show-file-list="false"
      class="add-img"
      action="string"
      accept=".jpg,.jpeg,.png,.gif,.bmp,.JPG,.JPEG,.PBG,.GIF,.BMP"
    >
      <img v-show="item.image.uri" :src="item.image.uri" class="carousel-img">
      <div v-show="item.image.uri" class="carousel-img-mask">更换图片</div>
      <span v-show="!item.image.uri"><i class="text-18">+</i> 添加图片</span>
    </el-upload>

    <el-dialog
      :visible.sync="dialogVisible"
      :append-to-body="true"
      title="提示:通过鼠标滚轮缩放图片"
      width="80%">
      <div class="setting-carousel-cropper-container">
        <vueCropper
          v-show="option.img"
          ref="cropper"
          :img="option.img"
          :fixed="option.fixed"
          :enlarge="option.enlarge"
          :auto-crop="option.autoCrop"
          :fixed-number="(pathName === 'appSetting') ? appFixedNumber : option.fixedNumber"
          :auto-crop-width="option.autoCropWidth"
          :auto-crop-height="option.autoCropHeight"
        />
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="stopCrop">确 定</el-button>
      </span>
    </el-dialog>

    <img v-show="active === index" class="icon-delete" src="static/images/delete.png" @click="handleRemove($event, index, itemNum)">
    <div v-if="pathName !== 'appSetting'" class="add-title">标题：<el-input v-model="item.title" size="mini" placeholder="请输入标题" max-length="15" clearable/>
    </div>
    <div v-if="pathName !== 'appSetting'" class="add-choose">
      链接：<el-radio v-model="radio" label="insideLink">站内链接</el-radio>
    </div>
    <div v-else class="add-choose">
      <el-radio v-model="radio" class="mt16" label="insideLink">站内链接</el-radio>
      <el-radio v-model="radio" class="mt16" label="url">自定义链接</el-radio>
    </div>
    <div v-if="radio==='insideLink'" class="add-inner">
      <el-dropdown v-show="!linkTextShow">
        <el-button size="mini" class="el-dropdown-link">
          添加链接
        </el-button>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item v-for="item in linkOptions" :key="item.key" @click.native="insideLinkHandle(item.type)">{{ item.label }}</el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
      <el-tag
        v-show="linkTextShow"
        :disable-transitions="true"
        class="courseLink"
        closable
        @close="handleClose">
        <el-tooltip class="text-content ellipsis" effect="dark" placement="top">
          <span slot="content">{{ linkTextShow }}</span>
          <span>{{ linkTextShow }}</span>
        </el-tooltip>
      </el-tag>
    </div>
    <div v-if="radio==='url'" class="add-outter">
      <div class="pull-left add-outter-title" >输入网址：</div>
      <el-input v-model="item.link.url" class="pull-right" size="mini" placeholder="例如 http://www.eduosho.com" clearable @change="changeLinkUrl"/>
    </div>
  </div>
</template>

<script>
import Api from 'admin/api'
import { VueCropper } from 'vue-cropper'
import settingCell from '../module-frame/setting-cell'
export default {
  components: {
    VueCropper,
    settingCell
  },
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'index', 'active', 'itemNum', 'courseSets'],
  data() {
    return {
      activeIndex: this.active,
      option: {
        img: '',
        autoCrop: true,
        autoCropWidth: 375,
        autoCropHeight: 200,
        fixedNumber: [375, 200],
        fixed: true,
        high: false,
        enlarge: 2
      },
      appFixedNumber: [375, 150],
      linkOptions: [{
        key: 0,
        type: 'course_list',
        label: '选择课程'
      }, {
        key: 1,
        type: 'classroom_list',
        label: '选择班级'
      }, {
        key: 2,
        type: 'vip',
        label: '选择会员'
      }],
      imageCropped: false,
      dialogVisible: false,
      pathName: this.$route.name,
      type: '',
      radio: 'insideLink', // 选择使用站内链接还是站外
      linkUrl: '' // 站外链接的url
    }
  },
  computed: {
    linkTextShow() {
      if (this.type === 'vip') {
        return '会员专区'
      }
      return this.item.link.target && this.item.link.target.displayedTitle
    }
  },
  watch: {
    courseSets(sets) {
      console.log(sets[0], 'courseSets')
      if (sets.length) {
        this.item.link.target = {
          id: sets[0].id,
          title: sets[0].title,
          courseSetId: sets[0].courseSetId,
          displayedTitle: sets[0].displayedTitle
        }
      } else {
        this.item.link.target = null
      }
    }
  },
  created() {
    this.type = this.item.link.type
    if (this.item.link.type === 'url') {
      this.radio = 'url'
    }
  },
  methods: {
    beforeUpload(file) {
      const type = file.type
      const size = file.size / 1024 / 1024

      if (type.indexOf('image') === -1) {
        this.$message({
          message: '文件类型仅支持图片格式',
          type: 'error'
        })
        return
      }

      if (size > 2) {
        this.$message({
          message: '文件大小不得超过 2 MB',
          type: 'error'
        })
        return
      }

      this.dialogVisible = true
      const reader = new FileReader()
      reader.onload = () => {
        this.option.img = reader.result
      }
      reader.readAsDataURL(file)
    },
    stopCrop() {
      this.$refs.cropper.stopCrop()
      this.dialogVisible = false
      this.$refs.cropper.getCropData((data) => {
        this.imageCropped = true
        this.uploadImg(data)
      })
    },
    uploadImg(file) {
      if (!this.imageCropped) return

      this.imageCropped = false

      const formData = new FormData()
      formData.append('file', file)
      formData.append('group', 'system')

      Api.uploadFile({
        data: formData
      })
        .then(data => {
          if (this.pathName === 'miniprogramSetting') {
            // 小程序后台替换图片协议
            data.uri = data.uri.replace(/^(\/\/)|(http:\/\/)/, 'https://')
          }
          this.item.image = data
          this.$emit('selected',
            {
              selectIndex: this.activeIndex,
              imageUrl: data.uri
            })

          this.$message({
            message: '图片上传成功',
            type: 'success'
          })
        })
        .catch((err) => {
          this.$message({
            message: err.message,
            type: 'error'
          })
        })
    },
    selected(index) {
      this.imgAdress = this.item.image.uri
      this.activeIndex = index
      this.$emit('selected',
        {
          selectIndex: index,
          imageUrl: this.item.image.uri
        }
      )
    },
    handleRemove(e, index, length) {
      e.stopPropagation()
      if (length > 1) {
        this.$emit('remove', index)
      } else {
        this.$message({
          message: '至少要留一张轮播图',
          type: 'warning'
        })
      }
    },
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url
      this.dialogVisible = true
    },
    handleClose() {
      this.type = ''
      this.$emit('removeCourseLink', this.index)
    },
    insideLinkHandle(value) {
      this.type = value
      this.$emit('chooseCourse', {
        'value': value,
        'index': this.index
      })
    },
    changeLinkUrl(value) {
      this.$emit('setOutLink', {
        'type': 'url',
        'url': value,
        'index': this.index
      })
    }
  }
}

</script>
