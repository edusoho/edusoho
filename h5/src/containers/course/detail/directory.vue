<template>
  <e-panel title="课程目录" class="directory" :hidde-title="hiddeTitle">
    <!-- 暂无学习任务 -->
    <div v-if="courseItems.length == 0" class="empty">暂无学习任务</div>
    <div class="directory-list" v-else>
      <div class="directory-list__item" v-for="(item, index) in chapters">
        <div class="directory-list__item-chapter"
          @click="item.show = !item.show"
          v-if="item.type === 'chapter'">
          <span class="text-overflow">第{{ item.number }}章：{{ item.title }}</span>
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
                <span>{{ task.task | taskType }}{{ task.task | filterTask }}</span>
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
        directoryArray: [],
        chapters: [],
        tasks: []
      }
    },
    filters:{
      filterNumber(task) {
        return task.task.isOptional === '1' ? '选修' : task.task.number
      }
    },
    watch: {
      selectedPlanId: {
        immediate: true,
        handler(v) {
          if (!this.details.courseItems.length) return;

          this.directoryArray =
            this.details.courseItems.map(item => {

            this.$set(item, 'show', true);

            if (item.type == 'task') {
              item['status'] = this.getCurrentStatus(item.task);
            }

            return item;
          })

          this.getTasks(this.directoryArray);
        }
      }
    },
    methods: {
      ...mapMutations('course', {
        setSourceType: types.SET_SOURCETYPE
      }),
      getTasks (data) {
        let temp = [];
        this.chapters = [];
        this.tasks = [];

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
          this.chapters.unshift({ show: true });
        }
      },
      getCurrentStatus (task) {
        if (Number(this.details.tryLookable)
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
      lessonCellClick (data) {
        const task = data.task;
        const details = this.details;

        !details.allowAnonymousPreview && this.$route.push({name: 'login'})

        if (!this.joinStatus
          && Number(details.tryLookable)
          && ['is-tryLook', 'is-free'].includes(data.status)) {
        // trylook and free video click
          switch (task.type) {
            case 'video':
            case 'audio':
              this.$router.push({
                name: 'course_try',
              })

              this.setSourceType({
                sourceType: task.type,
                taskId: task.id
              });
              break;
            case 'doc':
            case 'text':
            case 'ppt':
              this.$router.push({
                name: 'course_web',
                query: {
                  courseId: this.selectedPlanId,
                  taskId: task.id,
                  type: task.type,
                }
              })
              break;
            default:
              return Toast('请先加入课程');
          }
        } else {
          this.joinStatus ? this.showTypeDetail(task) : Toast('请先加入课程');
        }
        //join after click
      },
      showTypeDetail (task) {
        if (task.status !== 'published') {
          Toast('敬请期待');
          return;
        }
        switch(task.type) {
          case 'video':
            if (task.mediaSource === 'self') {
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
              query: {
                courseId: this.selectedPlanId,
                taskId: task.id,
                type: task.type
              }
            })
            break;
          case 'live':
            const nowDate = new Date()
            const endDate = new Date(task.endTime * 1000)
            const startDate = new Date(task.startTime * 1000)
            let replay = false
            if (nowDate > endDate) {
              if (task.activity.replayStatus == 'videoGenerated') {
                if (task.mediaSource === 'self') {
                  this.setSourceType({
                    sourceType: 'video',
                    taskId: task.id
                  })
                } else {
                  Toast('暂不支持此类型');
                }
                return;
              } else if (task.activity.replayStatus == 'ungenerated') {
                Toast('暂无回放');
                return
              } else {
                replay = true
              }
            }

            this.$router.push({
              name: 'live',
              query: {
                courseId: this.selectedPlanId,
                taskId: task.id,
                type: task.type,
                title: task.title,
                replay,
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
