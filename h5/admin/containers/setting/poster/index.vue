<template>
    <module-frame containerClass="setting-poster" :isActive="isActive">
        <div slot="preview" class="poster-image-container">
            <img v-bind:src="this.imgAdress" class="poster-image">
            <img class="icon-delete" src="static/images/delete.png" @click="handleRemove()" v-show="isActive">
        </div>
        <div slot="setting" class="poster-allocate">
            <header class="title">图片广告设置</header>
            <div class="poster-item-setting clearfix">
                <div class="poster-item-setting__section">
                    <p class="pull-left section-left">广告图片：</p>
                    <div class="section-right">
                    <el-upload
                            class="image-uploader"
                            action="string"
                            :http-request="uploadImg"
                            :show-file-list="false"
                    >
                        <img v-show="this.copyModuleData.image.uri" :src="this.copyModuleData.image.uri" class="poster-img">
                        <span><i class="text-18">+</i> 添加图片</span>
                    </el-upload>
                    </div>
                </div>

                <div class="poster-item-setting__section mtl">
                    <p class="pull-left section-left">链接：</p>
                    <div class="section-right">
                        <el-radio label="condition">站内课程</el-radio>
                        <el-radio label="custom">自定义链接</el-radio>
                    </div>
                </div>

                <div class="poster-item-setting__section mtl">
                    <p class="pull-left section-left">课程名称：</p>
                    <div class="section-right">
                        <el-button type="info" size="mini" @click="openModal">选择课程</el-button>
                    </div>
                </div>

                <div class="poster-item-setting__section mtl">
                    <p class="pull-left section-left">自适应手机屏幕：</p>
                    <div class="section-right">
                        <el-radio label="condition">开启</el-radio>
                        <el-radio label="custom">关闭</el-radio>
                    </div>
                </div>

            </div>
         </div>
    </module-frame>
</template>

<script>
import Api from '@admin/api';
import moduleFrame from '../module-frame'

export default {
    components: {
        moduleFrame,
    },
    data() {
        return  {
            modalVisible: false,
            imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg'
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
        }
    },
    mounted() {
        console.log(this.copyModuleData)
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
                    this.copyModuleData.image = data;
                    console.log(data)
                })
                .catch((err) => {
                    console.log(err, 'error');
                });
        },
        handleRemove() {
            this.$el.remove();
        },
        modalVisibleHandler(visible) {
            this.modalVisible = visible;
        },
        openModal() {
            this.modalVisible = true;
        },
    }
}

</script>