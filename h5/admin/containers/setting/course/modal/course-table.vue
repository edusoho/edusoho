<template>
  <div class="course-table">
    <div class="course-table__th">
      <span class="course-table__td"
        v-for="(item, index) in head"
        :class="tdClass(item.col)"
        :key="index">{{ item.title }}</span>
    </div>

    <draggable v-model="courseSets">
      <div class="course-table__tr" v-for="(course, courseIndex) in courseSets" :key="courseIndex">
        <div class="tr-content">
          <span class="course-table__td text-overflow"
            v-for="(item, index) in head"
            :class="[ tdClass(item.col), { 'delete': head[index].label === 'delete' }]"
            @click="deleteItem(head[index].label === 'delete', courseIndex)">
            {{ getContext(course, head[index].label) }}
          </span>
        </div>
      </div>

    </draggable>
    <div v-show="!courseSets.length" class="course-table__empty">暂无数据</div>
  </div>
</template>

<script>
import draggable from 'vuedraggable';
import { formatTime } from '@/utils/date-toolkit.js';

export default {
  name: 'course-table',
  components: {
    draggable,
  },
  props: {
    courseList: {
      type: Array,
      default: [],
    },
  },
  data () {
    return {
      head: [
        {
          col: 5,
          title: '课程名称',
          label: 'courseSetTitle',
        }, {
          col: 3,
          title: '商品价格',
          label: 'price',
        }, {
          col: 3,
          title: '创建时间',
          label: 'createdTime',
        }, {
          col: 0,
          title: '操作',
          label: 'delete',
        }
      ],
    }
  },
  computed: {
    courseSets: {
      get() {
        return this.courseList;
      },
      set(courses) {
        this.$emit('updateCourses', courses);
      }
    }
  },
  methods: {
    tdClass(ratio) {
      return `td-col-${ratio}`
    },
    deleteItem(isDeleteBtn, index) {
      if (!isDeleteBtn) {
        return;
      }
      this.courseSets.splice(index, 1);
    },
    getContext(course, label) {
      if (label === 'price') {
        return `${course[label]}元`;
      } else if (label === 'createdTime') {
        const date = new Date(course[label]);
        return formatTime(date);
      } else if (label === 'delete') {
        return `移除`;
      }
      return course[label];
    }
  }
}
</script>

