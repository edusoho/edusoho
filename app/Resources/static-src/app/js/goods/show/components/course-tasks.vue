<template>
    <div class="course-tasks-content">
        <ul class="task-list">
            <span v-for="item in courseItems">
                <li class="task-item bg-gray-lighter js-task-chapter infinite-item" v-if="isChapter(item)">
                    <i class="es-icon es-icon-menu left-menu"></i>
                    <a href="javascript:" class="title gray-dark">{{ getChapterName(item) }}</a>
                    <i class="right-menu es-icon es-icon-remove js-remove-icon"></i>
                </li>
                <li class="task-item color-gray bg-gray-lighter infinite-item" v-if="isUnit(item)">
                    <span class="title">{{ getUnitName(item) }}</span>
                </li>
                <li class="task-item infinite-item {lessonContainerClass}" v-if="isLesson(item)">
                    <span class="title">{{ getLessonName(item) }}</span>
                    <span v-if="item.isItemDisplayedAsUnpublished">
                      <span class="right-menu ">{{'course.catalogue.task_status.looking_forward'|trans}}</span>
                    </span>
                </li>
                <li class="task-item task-content mouse-control infinite-item color-gray bg-gray-lighter" v-if="isTask(item)">
                    <i v-if="item.isTaskLocked" class="{taskClass}" data-toggle="tooltip" data-trigger="hover"
                       data-placement="top" title="'course.task.lock_tips'|trans">
                    </i>
                    <i v-if="item.isTaskLocked" class="hidden"></i>
                    <span class="title" href="javascript:;" data-toggle="modal" style="margin-top:-6px">{{ getTaskName(item) }}</span>
                    <span class="right-menu color-gray "></span>
                    <!--试看逻辑-->
                </li>
            </span>
        </ul>
    </div>
</template>

<script>
    import axios from "axios";

    export default {
        data() {
            return {
               courseItems: [],
               hasOptional: false,
            };
        },
        props: {
            sku: {
                type: Object,
                default: null
            },
        },
        methods: {
            getTasksListInfo() {
                axios.get(`/course/${this.sku.targetId}/tasks/list_data`, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                }).then((res) => {
                    if (res.data.dataoptionalTaskCount > 0) {
                        this.hasOptional = true;
                    }
                    this.courseItems = res.data.courseItems;
                    console.log(this.courseItems);
                });
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
            getTaskName(data, context) {
                return data.title;
            },
            isTask(data) {
                return 'task' === data.itemType;
            },
        },
        created() {
            this.getTasksListInfo();
        }
    }
</script>