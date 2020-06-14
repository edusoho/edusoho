<template>
    <div>
        <el-form v-if="hasMulCourses" :model="baseInfoForm" :rules="formRule" ref="baseInfoForm" label-position="right"
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
        <el-form v-else-if="isUnMultiCourseSet" :model="baseInfoForm" :rules="formRule" ref="baseInfoForm"
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
                    <el-select
                        class="un-multi-course-set-tags"
                        v-model="baseInfoForm.tags"
                        multiple
                        filterable
                        remote
                        reserve-keyword
                        @focus="searchTags('')"
                        :remote-method="searchTags"
                        :loading="loading">
                        <el-option
                            v-for="tag in tags"
                            :key="tag.id"
                            :label="tag.name"
                            :value="tag.name">
                        </el-option>
                    </el-select>
                    <div class="help-block courseset-manage-body__tip">{{ 'course.base.tag_tips'|trans }}</div>
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

        </el-form>
    </div>
</template>

<script>
    import * as validation from 'common/element-validation';

    export default {
        name: "base-info",
        props: {
            course: {},
            hasMulCourses: false,
            isUnMultiCourseSet: false,
            tags: '',
            tagSearchUrl: ''

        },
        methods: {
            searchTags(query) {
                this.$axios.get(this.tagSearchUrl, {
                    params: {q: query},
                }).then((response) => {
                    this.tags = response.data;
                    console.log(response.data);
                });

            },
            validateForm() {
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
                return this.baseInfoForm;
            }
        },
        // mixins: [validation.trans],
        // refFormRule: formRule,
        data() {
            let baseForm = {
                title: this.course.title ? this.course.title : this.course.courseSetTitle,
                subtitle: this.course.subtitle
            };

            if (this.isUnMultiCourseSet) {
                Object.assign(baseForm, {
                    tags: this.tags,
                    serializeMode: this.course.serializeMode
                });
            }

            return {
                course: {},
                isUnMultiCourseSet: false,
                hasMulCourses: false,
                tags: '',
                tagSearchUrl: '',
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
                }
            };
        },
        mounted() {
            console.log();
        }
    }
</script>

<style scoped>
    .el-form-item label {
        width: 150px;
    }
</style>