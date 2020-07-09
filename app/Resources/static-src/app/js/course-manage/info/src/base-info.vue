<template>
    <div>
        <el-form v-if="hasMulCourses" :model="baseInfoForm" id="base-info-form" :rules="formRule" ref="baseInfoForm"
                 label-position="right"
                 label-width="150px">
            <div class="course-manage-subltitle cd-mb40">{{ 'course.base_info'|trans }}</div>
            <el-form-item :label="'course.plan_setup.name'|trans" prop="title">
                <el-col span="18">
                    <el-input ref="title" v-model="baseInfoForm.title" auto-complete="off"
                              :placeholder="'course.plan_setup.placeholder'|trans">
                    </el-input>
                </el-col>
            </el-form-item>
            <el-form-item :label="'course.plan_setup.subtitle'|trans" prop="subtitle">
                <el-col span="18">
                    <el-input ref="subtitle" v-model="baseInfoForm.subtitle" type="textarea" rows="3"></el-input>
                </el-col>
            </el-form-item>
        </el-form>
        <el-form v-if="isUnMultiCourseSet" :model="baseInfoForm" :rules="formRule" ref="baseInfoForm"
                 label-position="right"
                 label-width="150px">
            <div class="course-manage-subltitle cd-mb40">{{ 'course.base_info'|trans }}</div>

            <el-form-item :label="'course.plan_setup.name'|trans" prop="title">
                <el-col span="18">
                    <el-input ref="title" v-model="baseInfoForm.title" auto-complete="off"
                              :placeholder="'course.plan_setup.placeholder'|trans">
                    </el-input>
                </el-col>
            </el-form-item>
            <el-form-item :label="'course.plan_setup.subtitle'|trans" prop="subtitle">
                <el-col span="18">
                    <el-input ref="subtitle" v-model="baseInfoForm.subtitle" type="textarea" rows="3"></el-input>
                </el-col>
            </el-form-item>
            <el-form-item :label="'course.base.tag'|trans">
                <el-col span="18">
                    <tags v-bind:tag-data="baseInfoForm.tags" v-on:update:tags="baseInfoForm.tags = $event"></tags>
                    <div class="help-block courseset-manage-body__tip">{{ 'course.base.tag_tips'|trans }}</div>
                </el-col>
            </el-form-item>

            <el-form-item :label="'course.base.category'|trans">
                <el-col span="8">
                    <category v-bind:category="baseInfoForm.categoryId"
                              v-on:update:category="baseInfoForm.categoryId = $event"></category>
                </el-col>
            </el-form-item>

            <el-form-item v-if="enableOrg" :label="'site.org'|trans">
                <el-col span="8">
                    <org v-bind:params="{}" v-bind:org-code="baseInfoForm.orgCode"></org>
                </el-col>
            </el-form-item>

            <el-form-item :label="'course.base.serialize_mode'|trans">
                <el-radio v-model="baseInfoForm.serializeMode"
                          v-for="(label, value) in serializeModeRadio"
                          class="cd-radio"
                          :label="value"
                          :key="value">
                    {{label}}
                </el-radio>
            </el-form-item>

            <el-form-item :label="'course.cover_image.content_title'|trans">
                <el-col span="18">
                    <div v-html="uploadImageTemplate"></div>
                    <div class="help-block">{{ 'course.cover_image.upload_tips'|trans }}</div>
                </el-col>
            </el-form-item>

            <el-form-item :label="'course.detail.summary'|trans">
                <el-col span="18">
                    <textarea name="summary" rows="10" data-form="base-info-form"
                              data-button="button"
                              id="courseset-summary-field" class="form-control"
                              :data-image-upload-url="imageUploadUrl">{{ baseInfoForm.summary }}</textarea>
                    <div class="help-block">{{ 'editor.iframe_tips'|trans }}</div>
                </el-col>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import * as validation from 'common/element-validation';
    import tags from 'app/js/common/src/tags.vue';
    import category from 'app/js/common/src/category.vue';
    import org from 'app/js/common/src/org.vue';

    let summaryEditor = null;
    export default {
        name: "base-info",
        props: {
            course: {},
            courseSet: {},
            hasMulCourses: false,
            isUnMultiCourseSet: false,
            tags: '',
            imageSaveUrl: '',
            imageSrc: '',
            imageUploadUrl: '',
            enableOrg: 0
        },
        watch: {
            uploadImageTemplate(newVal, oldVal) {
                this.$nextTick(() => {
                    import('app/js/upload-image/index.js');
                    summaryEditor = CKEDITOR.replace('courseset-summary-field', {
                        toolbar: 'Simple',
                        filebrowserImageUploadUrl: $('#courseset-summary-field').data('imageUploadUrl')
                    });
                });
            },
        },
        components: {
            tags,
            category,
            org
        },
        methods: {
            validateForm() {
                if (!this.$refs.baseInfoForm) {
                    return {result: true, invalidFields: {}};
                }

                let result = false;
                let invalids = {};
                this.$refs.baseInfoForm.clearValidate();

                this.$refs.baseInfoForm.validate((valid, invalidFields) => {
                    if (valid) {
                        result = true;
                    } else {
                        invalids = invalidFields;
                    }
                });

                return {result: result, invalidFields: invalids};
            },
            getFormData() {
                if (this.isUnMultiCourseSet) {
                    this.baseInfoForm.orgCode = $('.js-org-tree-select').children('option:selected').val();
                    this.baseInfoForm.summary = summaryEditor.getData();
                }

                return this.baseInfoForm;
            },
            getUploadImageTemplate() {
                let params = {
                    saveUrl: this.imageSaveUrl,
                    targetImg: 'course-cover',
                    cropWidth: '480',
                    cropHeight: '270',
                    uploadToken: 'tmp',
                    imageClass: 'course-manage-cover',
                    imageText: Translator.trans('course.cover.change'),
                    imageSrc: this.imageSrc,
                };
                this.$axios.get('/render/upload/image?' + this.$qs.stringify(params)).then((res) => {
                    this.uploadImageTemplate = res.data;
                });
            }
        },
        data() {
            let baseForm = {
                title: this.course.title ? this.course.title : this.course.courseSetTitle,
                subtitle: this.course.subtitle ? this.course.subtitle : this.courseSet.subtitle,
            };

            if (this.isUnMultiCourseSet) {
                Object.assign(baseForm, {
                    tags: this.tags,
                    categoryId: this.course.categoryId,
                    serializeMode: this.course.serializeMode,
                    orgCode: this.course.orgCode,
                    summary: this.courseSet.summary
                });

                this.getUploadImageTemplate();
            }


            return {
                course: {},
                isUnMultiCourseSet: false,
                hasMulCourses: false,
                tags: '',
                uploadImageTemplate: '',
                summaryTemplate: '',
                baseInfoForm: baseForm,
                formRule: {
                    title: [
                        {
                            required: true,
                            message: Translator.trans('validate.required.message', {'display': Translator.trans('course.plan_setup.name')}),
                            trigger: 'blur'
                        },
                        {
                            max: 10,
                            message: Translator.trans('validate.length_max.message', {'length': 10}),
                            trigger: 'blur',
                        },
                        {validator: validation.trim, trigger: 'blur'},
                        {validator: validation.course_title, trigger: 'blur'}
                    ],
                    subtitle: [
                        {
                            max: 30,
                            message: Translator.trans('validate.length_max.message', {'length': 30}),
                            trigger: 'blur',
                        },
                    ]
                },
                serializeModeRadio: {
                    none: Translator.trans('course.base.serialize_mode.none'),
                    serialized: Translator.trans('course.base.serialize_mode.serialized'),
                    finished: Translator.trans('course.base.serialize_mode.finished')
                },
                enableOrg: 0
            };
        }
        ,
        mounted() {
        }
    }
</script>

<style scoped>
    .el-form-item label {
        width: 150px;
    }
</style>