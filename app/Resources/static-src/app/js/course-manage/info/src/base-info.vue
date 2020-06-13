<template>
    <div v-if="hasMulCourses">
        <div class="course-manage-subltitle cd-mb40">{{ 'course.base_info'|trans }}</div>
        <el-form :model="baseInfoForm" :rules="formRule" ref="baseInfoForm" label-position="right"
                 label-width="150px">
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
    </div>
</template>

<script>
    import * as validation from 'common/element-validation';

    export default {
        name: "base-info",
        props: {
            course: {},
            hasMulCourses: {},
            isUnMultiCourseSet: false,
        },
        methods: {
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
            let baseInfoForm = {
                title: this.course.title ? this.course.title : this.course.courseSetTitle,
                subtitle: this.course.subtitle
            };

            if (this.isUnMultiCourseSet) {
                Object.assign(baseInfoForm, {});
            }


            return {
                course: {},
                isUnMultiCourseSet: false,
                hasMulCourses: false,
                baseInfoForm: baseInfoForm,
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