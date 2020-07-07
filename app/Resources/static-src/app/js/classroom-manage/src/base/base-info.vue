<template>
    <div>
        <el-form :model="form" :rules="formRule"
                 ref="baseInfoForm"
                 label-position="right"
                 label-width="150px">
            <div class="course-manage-subltitle cd-mb40 ml0">{{ 'classroom.basic_info'|trans }}</div>
            <el-form-item :label="'classroom.title_label'|trans" prop="title">
                <el-col span="18">
                    <el-input ref="title" v-model="form.title" auto-complete="off"></el-input>
                </el-col>
            </el-form-item>
            <el-form-item :label="'classroom.subtitle_label'|trans">
                <el-col span="18">
                    <el-input ref="subtitle" v-model="form.subtitle" type="textarea" rows="3"></el-input>
                </el-col>
            </el-form-item>
            <el-form-item :label="'classroom.tag_label'|trans">
                <el-col span="18">
                    <tags v-bind:tag-data="form.tags" v-on:update:tags="form.tags = $event"></tags>
                    <div class="help-block courseset-manage-body__tip">
                        {{ 'classroom.manage.set_info.tags.help_block'|trans }}
                    </div>
                </el-col>
            </el-form-item>

            <el-form-item :label="'classroom.category_label'|trans">
                <el-col span="8">
                    <category v-bind:category="form.categoryId"
                              v-on:update:category="form.categoryId = $event"></category>
                </el-col>
            </el-form-item>

            <el-form-item v-if="enableOrg" :label="'site.org'|trans">
                <el-col span="8">
                    {{ form.orgCode }}
                    <org v-bind:params="{}" v-bind:org-code="form.orgCode"></org>
                </el-col>
            </el-form-item>

            <el-form-item :label="'classroom.cover_image'|trans">
                <el-col span="18">
                    <div v-html="uploadImageTemplate"></div>
                    <div class="help-block">{{ 'classroom.cover_image.upload_tips'|trans }}</div>
                </el-col>
            </el-form-item>

            <el-form-item :label="'classroom.about'|trans">
                <el-col span="18">
                    <textarea name="about" rows="6"
                              :data-image-upload-url="imageUploadUrl"
                              :data-flash-upload-url="flashUploadUrl"
                              id="about">{{ form.about }}</textarea>
                    <div class="help-block">{{ 'editor.iframe_tips'|trans }}</div>
                </el-col>
            </el-form-item>


        </el-form>
    </div>

</template>

<script>
    import tags from 'app/js/common/src/tags.vue';
    import category from 'app/js/common/src/category.vue';
    import org from 'app/js/common/src/org.vue';

    export default {
        name: "base-info",
        components: {
            tags, category, org
        },
        props: {
            classroom: {},
            tags: [],
            enableOrg: 0,
            cover: '',
            coverCropUrl: '',
            imageUploadUrl: '',
            flashUploadUrl: '',
        },
        watch: {
            uploadImageTemplate(newVal, oldVal) {
                this.$nextTick(() => {
                    import('app/js/upload-image/index.js');
                    CKEDITOR.replace('about', {
                        allowedContent: true,
                        toolbar: 'Detail',
                        fileSingleSizeLimit: app.fileSingleSizeLimit,
                        filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
                        filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
                    });
                });
            },
        },
        methods: {
            initUploaderAndEditor() {
                let params = {
                    saveUrl: this.coverCropUrl,
                    targetImg: 'classroom-cover',
                    cropWidth: '540',
                    cropHeight: '340',
                    uploadToken: 'tmp',
                    imageClass: 'classroom-manage-cover',
                    imageText: Translator.trans('classroom.upload_picture_btn'),
                    imageSrc: this.cover,
                };
                this.$axios.get('/render/upload/image?' + this.$qs.stringify(params)).then((res) => {
                    this.uploadImageTemplate = res.data;
                });
            }
        },
        data() {
            this.initUploaderAndEditor();

            return {
                uploadImageTemplate: '',
                form: {
                    title: this.classroom.title,
                    subtitle: this.classroom.subtitle ? this.classroom.subtitle : null,
                    tags: this.tags,
                    categoryId: this.classroom.categoryId,
                    orgCode: this.classroom.orgCode,
                    about: this.classroom.about,
                },
                formRule: {
                    title: {
                        required: true,
                        message: Translator.trans('validate.required.message', {'display': Translator.trans('classroom.name_label')}),
                        trigger: 'blur'
                    }
                },
            };
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>