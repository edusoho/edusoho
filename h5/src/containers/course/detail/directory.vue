<template>
  <e-panel title="课程目录" class="directory" :hidde-title="hiddeTitle">
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
              <div class="lesson-cell__content" @click="lessonCellClick(task)">
                <span>{{ task.title }}</span>
                <span>{{ task.task.type | taskType }}{{ task.task | filterTask }}</span>
              </div>
              <div :class="['lesson-cell__status', task.status]">
                {{ filterTaskStatus(task) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </e-panel>
</template>
<script>
  import { mapState, mapMutations } from 'vuex';
  import { Toast } from 'vant';
  import * as types from '@/store/mutation-types';

  export default {
    props: {
      courseItems: {
        type: Array,
        default: () => ([])
      },
      tryLookable: {
        type: String,
        default: ''
      },
      hiddeTitle: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      ...mapState('course', {
        joinStatus: state => state.joinStatus,
        selectedPlanId: state => state.selectedPlanId,
        details: state => state.details
      })
    },
    data() {
      return {
        directoryArray: this.courseItems,
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
      if (this.directoryArray.length > 0) {
        this.directoryArray.map(item => {
          this.$set(item, 'show', true);
          if (item.type == 'task') {
            item['status'] = this.getCurrentStatus(item.task);
          }
      })
        this.getTasks(this.directoryArray);
      }
    },
    methods: {
      ...mapMutations('course', {
        setSourceType: types.SET_SOURCETYPE
      }),
      getTasks (data) {
        let temp = [];

        data.forEach(item => {
          if (item.type !== 'chapter') {
            temp.push(item);
          } else {
            if (temp.length > 0) {
              this.tasks.push([].concat(temp));
              temp = [];
            }else if (this.chapters.length > 0) {
              this.tasks.push([]);
            }

            this.chapters.push(item);
          }
        })

        const last = data.length - 1;

        if (data[last].type !== 'chapter') {
          this.tasks.push(temp);
        }

        if (data[0].type !== 'chapter') {
          this.chapters.unshift({show:true});
        }
        console.log('chapters', this.chapters, 'tasks', this.tasks);
      },
      getCurrentStatus (task) {
        if (Number(this.tryLookable)
          && task.type === 'video'
          && task.activity.mediaStorage) {
          return 'is-tryLook';
        } else if (Number(task.isFree)) {
          return 'is-free';
        }
        return '';
      },
      filterTaskStatus (task){
        if (task.status === 'is-tryLook') {
          return '试看';
        } else if (task.status === 'is-free') {
          return '免费';
        }

        return '';
      },
      lessonCellClick (task) {
        // trylook and free video click
        if (!this.joinStatus && !!this.tryLookable) {
          if (task.task.type === 'video' || task.task.type === 'audio') {
            this.$router.push({
              name: 'course_try'
            })
            this.setSourceType('video');
          } else if (['doc', 'ppt'].includes(task.task.type)) {
            this.$router.push({
              name: 'course_web'
            })
          } else {
            Toast('请先加入课程');
          }
        } else {
          this.showTypeDetail(task.task);
        }
        //join after click
      },
      showTypeDetail (task) {
        switch(task.type) {
          case 'video':
            if (task.mediaSource == 'self') {
              this.setSourceType({
                sourceType: 'video',
                taskId: task.id
              })
            } else {
              Toast('暂不支持此类型');
            }
            break;
          case 'audio':
            this.setSourceType({
              sourceType: 'audio',
              taskId: task.id
            })
            break;
          case 'text':
          case 'ppt':
          case 'doc':
            this.$router.push({
              name: 'course_web',
              params: {
                courseId: this.selectedPlanId,
                taskId: task.id,
                type: task.type
              }
            })
            break;
          default:
            Toast('暂不支持此类型');
        }
      }
    }
  }
</script>
