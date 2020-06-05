<template>
    <div v-if="hasMulCourses">
        <div class="course-manage-subltitle cd-mb40">{{ 'course.plan_setup.name'|trans }}</div>
        <div class="form-group">
            <div class="col-sm-2 control-label">
                <label for="title" class="control-label-required">{{ 'course.plan_setup.placeholder'|trans }}</label>
            </div>
            <div class="col-sm-8">
                <input v-if="course.title" class="form-control js-course-title" v-model="course.title" type="text" name="title" id="title" />
                <input v-else class="form-control js-course-title" v-model="course.courseSetTitle" type="text" name="title" id="title" />
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2 control-label">
                <label>{{ 'course.plan_setup.subtitle'|trans }}</label>
            </div>
            <div class="col-sm-8">
                <textarea v-model="course.subtitle" class="form-control js-course-subtitle" id="subtitle" name="subtitle"></textarea>
            </div>
        </div>
    </div>
</template>

<script>
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
            };
        }
    }
</script>

<style scoped>

</style>