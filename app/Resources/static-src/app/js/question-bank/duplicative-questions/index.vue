<template>
    <div id="duplicate-check">
       <div class="duplicate-head flex items-center">
        <span class="duplicate-back" @click="goBack">
            <a-icon type="left" />
            <a-icon type="swap-left" />
            返回
        </span>
        <span class="duplicate-divider"></span>
        <span class="duplicate-title flex items-center"><span class="bankName flex items-center">【<span class="msg">{{ categoryName }}</span>】</span>试题查重</span>
       </div>
       <div class="duplicate-body flex">
        <div class="duplicate-question">
            <div class="duplicate-question-head">
                共有重复题目:<label class="duplicate-question-count">{{ questionData.length }}</label>道
                <div v-show="isShowGuide" class="duplicate-question-item duplicate-question-active">
                    <div class="duplicate-question-title">
                        {{ questionData[0].material }}
                    </div>
                    <span class="duplicate-question-check-count">{{ questionData[0].frequency }}次</span>
                </div>
            </div>
            <duplicate-question-item 
                v-for="(item, index) in questionData" 
                :active="index == activeKey"
                :id="index"
                :key="index"
                :count="item.frequency"
                :title="item.displayMaterial"
                @changeOption="changeOption" />
        </div>
        <div class="duplicate-content">
            <div class="duplicate-content-title">题目对比</div>
            <div v-if="isHaveRepeat" class="mt16 flex flex-nowrap">
                <duplicate-question-content
                    v-if="questionContentList[oneIndex]"
                    @changeOption="changeOption"
                    @getData="getData"
                    @changeQuestion="changeQuestion"
                    @startQuestion="startQuestion"
                    type="one"
                    :activeIndex="oneIndex"
                    :activeKey="activeKey"
                    :nextIndex="twoIndex"
                    :questionContent="questionContentList[oneIndex]" 
                    :count="questionContentList.length"
                    class="mr16" />
                <duplicate-question-content
                    v-if="questionContentList[twoIndex]"
                    @changeOption="changeOption"
                    @getData="getData"
                    @changeQuestion="changeQuestion"
                    @startQuestion="startQuestion"
                    type="two"
                    :activeIndex="twoIndex"
                    :activeKey="activeKey"
                    :nextIndex="oneIndex"
                    :questionContent="questionContentList[twoIndex]" 
                    :count="questionContentList.length" />
            </div>
            <div v-else class="no-data text-center">
                <img class="no-data-img" src="/static-dist/app/img/question-bank/noduplicative.png" />
                <div class="no-data-content">暂无重复题目</div>
                <button class="return-btn">返回列表</button>
            </div>
        </div>
       </div>
    </div>
</template>
<script>
import DuplicateQuestionItem from "./components/DuplicateQuestionItem.vue";
import DuplicateQuestionContent from "./components/DuplicateQuestionContent.vue";
import 'store';
import { Repeat } from 'common/vue/service';


export default {
    data(){
        return{
            activeKey: 0,
            introOption: {
                prevLabel: '上一步',
                nextLabel: '下一步(1/2)',
                skipLabel: '跳过',
                doneLabel: '我知道了(2/2)',
                showBullets: false,
                showStepNumbers: false,
                exitOnEsc: false,
                exitOnOverlayClick: false,
                tooltipClass: 'duplicate-intro',
                steps: [],
          },
          oneIndex: 0,
          twoIndex: 1,
          isHaveRepeat: true,
          isShowGuide: false,
          questionData: [
            {
                material: '',
                frequency: '',
                displayMaterial: ''
            }
          ],
          questionContentList: [
            {
                analysis: '',
                category_name: '',
                type: '',
            }
          ]
        }
    },
    components:{
        DuplicateQuestionItem,
        DuplicateQuestionContent
    },
    watch:{
    },
    computed:{
        categoryName() {
            if ($("[name=categoryName]").val()) {
                return $("[name=categoryName]").val()
            }

            if ($("[name=categoryId]").val() === '') {
                return '全部题目'
            }

            return '未分类'
        }
    },
    async mounted() {
        await this.getData()
        await this.changeOption()
        if(!store.get('QUESTION_IMPORT_INTRO')) { 
            this.isShowGuide = true

            this.$nextTick(() => {
                this.initGuide()
            })
        }
    },
    methods:{
        initGuide() {
            let that = this;
            this.isShowGuide = true
            that.introOption.steps = [{
                element: '.duplicate-question-head',
                intro: Translator.trans('upgrade.cloud.capabilities.to.experience'),
                position: 'bottom',
            },
            {
                element: '.question-num',
                intro: Translator.trans('upgrade.cloud.capabilities.to.experience'),
                position: 'bottom',
            }],
            introJs()
                .setOptions(that.introOption)
                .start().onchange(function() {
                    that.isShowGuide = false
                    document.querySelectorAll('.introjs-skipbutton')[0].style.display = 'inline-block'
                    document.querySelectorAll('.introjs-prevbutton')[0].style.display = 'none'
                    }).oncomplete(function() {
                        store.set('DUPLICATE_IMPORT_INTRO', true);
                    })
        },
        changeQuestion(type, index) {
            this[`${type}Index`] = index;
        }, 
        async changeOption(activeKey=0){
            const that = this;
            that.activeKey = activeKey;
            let formData = new FormData();
            formData.append('material', that.questionData[activeKey].material);
            await Repeat.getRepeatQuestionInfo($("[name=questionBankId]").val(), formData).then(async res => {
                // if(res) {
                //     that.$error({
                //         title: '题目不存在',
                //         content: '该题目可能在题目管理页进行了编辑',
                //         okText: '确认',
                //         async onOk() {
                //             await that.getData()
                //             await that.changeOption()
                //         }
                //     });        
                // }

                that.questionContentList = res;
                that.questionData[activeKey].frequency = res.length.toString();
            })
        },
        goBack() {
            window.history.back();
        },
        async getData() {
            await Repeat.getRepeatQuestion($("[name=questionBankId]").val(), { categoryId: $("[name=categoryId]").val() }).then(res => {
                this.questionData = res
            });
        },
        startQuestion() {
            this.activeKey = 0
        }
    }
}
</script>