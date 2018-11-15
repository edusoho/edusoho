<template>
  <div class="course-table">
    <div class="course-table__th">
      <!-- 遍历表头数据 -->
      <span class="course-table__td"
        v-for="(item, index) in head"
        :class="tdClass(item.col)"
        :key="index">{{ item.title }}</span>
    </div>

    <draggable v-model="courseSets">
      <div class="course-table__tr" v-for="(course, courseIndex) in courseSets" :key="courseIndex">
        <div class="tr-content">
          <!-- 遍历表格内数据 -->
          <template v-for="(item, index) in head">
            <span class="course-table__td text-overflow"
              v-if="head[index].label !== 'displayedTitle'"
              :class="[ tdClass(item.col), { 'delete': head[index].label === 'delete' }]"
              @click="deleteItem(head[index].label === 'delete', courseIndex)">
              {{ getContext(course, head[index].label) }}
            </span>
            <el-tooltip
              v-if="head[index].label === 'displayedTitle'"
              :disabled="getContext(course, head[index].label).length <= 20"
              class="text-content td-col-5"
              placement="top-start"
              effect="dark">
              <span slot="content">{{ getContext(course, head[index].label) }}</span>
              <span class="course-table__td text-overflow">{{ getContext(course, head[index].label) }}</span>
            </el-tooltip>
          </template>
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
    typeText: {
      type: String,
      default: '课程'
    }
  },
  data () {
    return {
      head: [
        {
          col: 5,
          title: `${this.typeText}名称`,
          label:  this.typeText === '班级' ? 'title' : 'displayedTitle',
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
    },
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
      this.courseSets = this.courseSets; // 触发 courseSets 的 set 事件，向父组件抛出事件
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

