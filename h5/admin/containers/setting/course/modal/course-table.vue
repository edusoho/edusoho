<template>
  <div class="course-table">
    <div class="course-table__th">
      <span class="course-table__td"
        v-for="(item, index) in head"
        :class="tdWidth(item.col)"
        :key="index">{{ item.title }}</span>
    </div>

    <draggable v-model="courseSets">
      <div class="course-table__tr" v-for="(course, courseIndex) in courseSets" :key="courseIndex">
        <div class="tr-content">
          <span class="course-table__td text-overflow"
            v-for="(item, index) in head"
            :class="[tdWidth(item.col), head[index].label === 'delete' ? 'delete': '' ]"
            @click="deleteItem(head[index].label === 'delete', courseIndex)">
            {{ course[head[index].label] || '移除' }}
          </span>
        </div>
      </div>
    </draggable>
  </div>
</template>

<script>
import draggable from 'vuedraggable';

export default {
  name: 'course-table',
  components: {
    draggable,
  },
  data () {
    return {
      head: [
        {
          col: 5,
          title: '课程名称',
          label: 'title',
        }, {
          col: 3,
          title: '商品价格',
          label: 'price',
        }, {
          col: 3,
          title: '创建时间',
          label: 'createTime',
        }, {
          col: 0,
          title: '操作',
          label: 'delete',
        }
      ],
      courseSets: [
        {
          title: '如何干一个产品经理, 如何干一个产品经理, 如何干一个产品经理如何干一个产品经理如何干一个产品经理',
          price: '3333.00', // 价格显示原价
          createTime: '2018-06-02 15:00',
        }, {
          title: '如何干一个程序员',
          price: '3.30',
          createTime: '2018-06-02 15:00',
        }, {
          title: '如何干一个测试',
          price: '0.01',
          createTime: '2018-06-02 15:00',
        }
      ]
    }
  },
  methods: {
    tdWidth(ratio) {
      return `td-col-${ratio}`
    },
    deleteItem(bool, index) {
      if (!bool) {
        return;
      }
      this.courseSets.splice(index, 1);
      console.log('deleted')
    }
  }
}
</script>

