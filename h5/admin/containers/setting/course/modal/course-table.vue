<template>
  <div class="course-table">
    <div class="course-table__th">
      <!-- 遍历表头数据 -->
      <span class="course-table__td"
        v-for="(item, index) in head[type]"
        :class="tdClass(item.col)"
        :key="index">{{ item.title }}</span>
    </div>

    <draggable v-model="courseSets">
      <div class="course-table__tr" v-for="(course, courseIndex) in courseSets" :key="courseIndex">
        <div class="tr-content">
          <!-- 遍历表格内数据 -->
          <template v-for="(item, index) in head[type]">
            <span class="course-table__td text-overflow course-table__td-space"
              v-if="index !== 0"
              :class="[ tdClass(item.col), { 'delete': item.label === 'delete' }]"
              @click="deleteItem(item.label === 'delete', courseIndex)">
              {{ getContext(course, item.label) }}</span>
            <el-tooltip
              v-if="index === 0"
              :disabled="getContext(course, item.label).length <= 20"
              :class="['text-content', `td-col-${item.col}`]"
              placement="top-start"
              effect="dark">
              <span slot="content">{{ getContext(course, item.label) }}</span>
              <span class="course-table__td text-overflow">{{ getContext(course, item.label) }}</span>
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
import head from '@admin/config/modal-config';
import { formatTime } from '@/utils/date-toolkit';

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
      if (label.toLocaleLowerCase().includes('price')) {
        if (!course[label]) {
          return '未设置';
        }
        return `${course[label]}元`;
      } else if (label === 'createdTime') {
        if (!course[label]) {
          return '未知日期'
        }
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

