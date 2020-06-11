<template>
    <div v-if="hasMulCourses">
        <div class="course-manage-subltitle cd-mb40">{{ 'course.base_info'|trans }}</div>
        <el-form :model="baseInfoForm" :rules="formRule" ref="baseInfoForm" label-position="right">
            <el-form-item :label="'course.plan_setup.name'|trans" prop="title">
                <el-input v-model="baseInfoForm.title" auto-complete="off"
                          :placeholder="'course.plan_setup.placeholder'|trans">
                </el-input>
            </el-form-item>
            <el-form-item :label="'course.plan_setup.subtitle'|trans" prop="subtitle">
                <el-input v-model="baseInfoForm.subtitle" type="textarea" rows="3"></el-input>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import * as commonValidation from 'common/element-validation';

    export default {
        name: "base-info",
        props: {
            course: {},
            hasMulCourses: {},
        },
        data() {
            return {
                course: {},
                hasMulCourses: false,
                baseInfoForm: {
                    title: this.course.title ? this.course.title : this.course.courseSetTitle,
                    subtitle: this.course.subtitle
                },
                formRule: {
                    title: [
                        {
                            required: true,
                            message: Translator.trans('validate.required.message', {'display': Translator.trans('course.plan_setup.name')}),
                            trigger: 'blur'
                        },
                        {
                            max: 10, message: Translator.trans('validate.max_length.message'),
                        }
                    ],
                },
            };
        },
        mounted() {
        }
    }
</script>

<style scoped>
    .el-form-item label {
        width: 150px;
    }
</style>