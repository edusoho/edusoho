<template>
    <div class="course-tasks-content">
        <div class="js-tasks-show goods-task-list"
             :data-url="renderUrl"></div>
    </div>
</template>

<script>
    import axios from "axios";
    import PagedCourseTask from 'app/js/courseset/show/paged-course-task-list';

    export default {
        data() {
            return {
               courseItems: [],
               hasOptional: false,
               renderUrl: `/course/${this.sku.targetId}/task/list/render/default`,
            };
        },
        props: {
            sku: {
                type: Object,
                default: null
            },
            i18n: {
                type: Object,
                default: null,
            },
            activityMetas: {
                type: Object,
                default: null,
            }
        },
        methods: {
            getTasksListInfo() {
                // axios.get(`/course/${this.sku.targetId}/tasks/list_data`, {
                //     headers: {'X-Requested-With': 'XMLHttpRequest'}
                // }).then((res) => {
                //     if (res.data.dataoptionalTaskCount > 0) {
                //         this.hasOptional = true;
                //     }
                //     this.courseItems = res.data.courseItems;
                //     console.log(this.courseItems);
                // });
                console.log(this.renderUrl);

                this.renderUrl = `/course/${this.sku.targetId}/task/list/render/default`;
                $('.js-tasks-show').data('url', this.renderUrl);
                new PagedCourseTask();
            },
            isChapter(data) {
                return 'chapter' === data.itemType;
            },
            getChapterName(data) {
                return Translator.trans('course.chapter', { chapter_name: '章', number: data.number, title: data.title, colon: (data.title ? ':' : '') });
            },
            isUnit(data) {
                return 'unit' === data.itemType;
            },
            getUnitName(data) {
                return Translator.trans('course.unit', { part_name: '节', number: data.number, title: data.title, colon: (data.title ? ':' : '') });
            },
            isLesson(data) {
                return 'lesson' === data.itemType;
            },
            getLessonName(data, context) {
                if (context.isItemDisplayedAsOptional(data, context)) {
                    return data.title;
                } else {
                    return Translator.trans('course.lesson', { part_name: '课时', number: context.getLessonNum(data, context), title: data.title });
                }
            },
            getTaskName(data) {
                return Translator.trans('course.catalogue.task_status.task', { taskName: '任务', taskNumber: data.number, taskTitle: data.title });
            },
            isTask(data) {
                return 'task' === data.itemType;
            },
            getMetaIcon(data) {
                if (typeof this.activityMetas[data.type] != 'undefined') {
                    return this.activityMetas[data.type]['icon'] + ' es-icon ml5';
                }
                return 'es-icon ml5';
            },
        },
        created() {
            this.getTasksListInfo();
        },
        watch: {
            sku: {
                immediate: true,
                handler(val) {
                    this.getTasksListInfo();
                },
            },
        },
    }
</script>