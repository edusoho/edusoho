<template>
  <e-panel title="课程目录" class="directory layout-col">
    <!-- 暂无学习任务 -->

    <div class="directory-list">
      <div class="directory-list__item" v-for="(item, index) in chapters">

        <div class="directory-list__item-chapter" 
          @click="item.show = !item.show" 
          v-if="item.type === 'chapter'">
          <span>第{{ item.number }}章：{{ item.title }}</span> 
          <i :class="[ item.show ? 'icon-packup': 'icon-unfold']"></i>
        </div>

        <div :class="['directory-list__item-unit', 
          {'unit-show': item.show}]"
          v-for="task in tasks[index]">

          <div class="lesson-cell__unit" v-if="task.type === 'unit'">
            第{{ task.number }}节：{{ task.title }}
          </div>

          <div :class="['box', {'show-box': item.show}]" 
            v-if="task.type === 'task'">
            <div class="lesson-cell">
              <span class="lesson-cell__number">{{ task | filterNumber }}</span>
              <div class="lesson-cell__content">
                <span>{{ task.title }}</span>
                <span>{{ task.task.type | taskType }}{{ task.task | filterTask }}</span>
              </div>
              <!-- TODO 试看、免费状态修改 -->
              <div :class="['lesson-cell__status', {'is-free': task.task.isFree}]">试看</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </e-panel>
</template>
<script>
  export default {
    props: ['courseItem'],
    data() {
      return {
        directoryArray: this.courseItem,
        chapters: [],
        tasks: []
      }
    },
    filters:{
      filterNumber(task) {
        return Number(task.seq) ? `${task.number}-${task.seq}` : `${task.number}`
      }
    },
    created() {
      this.directoryArray.map(item => {
        this.$set(item, 'show', true);
      })
      this.getTasks(this.directoryArray);
    },
    methods: {
      getTasks(data) {
        let temp = [];

        data.forEach(item => {
          if (item.type !== 'chapter') {
            temp.push(item);
          } else {
            if(temp.length > 0) {
              this.tasks.push([].concat(temp));
              temp = [];
            }else if(this.chapters.length > 0) {
              this.tasks.push([]);
            }

            this.chapters.push(item);
          }
        })

        const last = data.length - 1;

        if (data[last].type !== 'chapter') {
          this.tasks.push(temp);
        }
        console.log('chapters', this.chapters, 'tasks', this.tasks);
      }
    }
  }
</script>
