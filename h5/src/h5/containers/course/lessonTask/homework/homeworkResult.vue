<template>
  <div class="testResults">
    <e-loading v-if="isLoading"/>
    <div v-if="result" ref="data" class="result-data">
      <div class="result-data__item">
        正确率
        <div v-if="isReadOver" class="result-data__bottom data-number-green data-medium"><span class="data-number">{{ result.rightRate }}</span>%
        </div>
        <div v-else class="result-data__bottom data-text-blue">待批阅</div>
      </div>
      <div class="result-data__item">
        做题用时
        <div class="result-data__bottom data-number-gray data-medium"><span class=" data-number">{{ usedTime }}</span>分钟
        </div>
      </div>
    </div>

    <div ref="tag" class="result-tag">
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-green"/>
        正确
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-orange"/>
        错误
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-gray"/>
        未作答
      </div>
      <div v-show="!isReadOver" class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-brown"/>
        待批阅
      </div>
    </div>

    <div :style="{height: calHeight}" class="result-subject">
      <div class="result-paner">
        <ul class="result-list">
          <li
            v-for="(item, index) in items"
            :class="[ 'result-list__item homework-number', `circle-${color[item.status]}`]"
            :key="index">{{ item.seq }}
          </li>
        </ul>
      </div>

      <div ref="footer" class="result-footer">
        <van-button
          :style="{marginRight: isReadOver ? '2vw' : 0}"
          class="result-footer__btn"
          type="primary"
          @click="viewAnalysis">查看解析
        </van-button>
        <van-button v-if="isReadOver" class="result-footer__btn" type="primary" @click="startHomework()">再做一次
        </van-button>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api'
import { mapState, mapMutations, mapActions } from 'vuex'
import * as types from '@/store/mutation-types'

import homeworkMixin from '@/mixins/lessonTask/homework.js'

export default {
  name: 'HomeworkResult',
  mixins: [homeworkMixin],
  data() {
    return {
      result: null,
      calHeight: null,
      items: null,
      title: null,
      color: { // 题号标签状态判断
        'right': 'green',
        'none': 'brown',
        'wrong': 'orange',
        'partRight': 'orange',
        'noAnswer': 'gray'
      }
    }
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user
    }),
    usedTime() {
      const timeInterval = parseInt(this.result.usedTime) || 0
      if (timeInterval < 60) {
        return 1
      }
      return Math.round(timeInterval / 60)
    },
    isReadOver() {
      return !!(this.result && this.result.status === 'finished')
    }
  },
  created() {
    this.gethomeworkResult()
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6'
    next()
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = ''
    next()
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    gethomeworkResult() {
      Api.homeworkResult({
        query: {
          homeworkId: this.$route.query.homeworkId,
          homeworkResultId: this.$route.query.homeworkResultId
        }
      })
        .then(res => {
          this.result = res
          this.setNavbarTitle(res.paperName)
          this.title = res.paperName
          this.interruption()
          this.formatData(res)
          this.calSubjectHeight()
        })
    },
    // 异常中断
    interruption() {
      this.canDoing(this.result, this.user.id)
        .then(() => {
          this.startHomework()
        })
        .catch(({ answer }) => {
          this.submitHomework(answer)
        })
    },
    formatData(res) {
      const items = []
      res.items.forEach(element => {
        if (element.type != 'material') {
          element.status = this.getStatus(element)
          items.push(element)
        }
        if (element.type == 'material') {
          element.subs.forEach((sub) => {
            sub.status = this.getStatus(sub)
            items.push(sub)
          })
        }
      })
      this.items = items
    },
    // 获取做题结果状态
    getStatus(element) {
      if (element.testResult && element.testResult.status) {
        return element.testResult.status
      } else {
        return 'noAnswer'
      }
    },
    startHomework() {
      this.$router.replace({
        name: 'homeworkDo',
        query: {
          targetId: this.$route.query.taskId,
          homeworkId: this.$route.query.homeworkId,
          courseId: this.$route.query.courseId
        },
        params: {
          KeepDoing: true
        }
      })
    },
    // 交作业
    submitHomework(answer) {
      const datas = {
        answer,
        homeworkId: this.$route.query.homeworkId,
        userId: this.user.id,
        homeworkResultId: this.$route.query.homeworkResultId
      }
      // 提交作业+跳转到结果页
      this.handHomeworkdo(datas)
        .then(res => {
          this.$router.replace({
            name: 'homeworkResult',
            query: {
              homeworkId: this.$route.query.homeworkId,
              homeworkResultId: this.$route.query.homeworkResultId,
              courseId: this.$route.query.courseId,
              taskId: this.$route.query.taskId
            }
          })
        })
        .catch((err) => {
          Toast.fail(err.message)
        })
    },
    calSubjectHeight() {
      this.$nextTick(() => {
        const dataHeight = this.$refs.data.offsetHeight + this.$refs.tag.offsetHeight + 46
        const allHeight = document.documentElement.clientHeight
        const footerHeight = this.$refs.footer.offsetHeight || 0
        const finalHeight = allHeight - dataHeight - footerHeight
        this.calHeight = `${finalHeight}px`
      })
    },
    viewAnalysis() {
      this.$router.push({
        name: 'homeworkAnalysis',
        query: {
          homeworkId: this.$route.query.homeworkId,
          homeworkResultId: this.$route.query.homeworkResultId,
          title: this.title
        }
      })
    }
  }
}
</script>

<style>

</style>
