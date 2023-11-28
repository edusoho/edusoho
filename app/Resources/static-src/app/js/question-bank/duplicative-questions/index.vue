<template>
    <div id="duplicate-check">
       <div class="duplicate-head">
        <span class="duplicate-back" @click="goBack">
            <a-icon type="left" />
            返回
        </span>
        <span class="duplicate-divider"></span>
        <span class="duplicate-title">【全部题目】试题查重</span>
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
                :title="item.material"
                @changeOption="changeOption" />
        </div>
        <div class="duplicate-content">
            <div class="duplicate-content-title">题目对比</div>
            <div v-if="isHaveRepeat" class="mt16 flex flex-nowrap">
                <duplicate-question-content class="mr16" />
                <duplicate-question-content />
            </div>
            <div v-else>
                数据为空
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
          isHaveRepeat: true,
          isShowGuide: false,
          questionData:[
            {
                material: '',
                frequency: ''
            }
          ]
        }
    },
    components:{
        DuplicateQuestionItem,
        DuplicateQuestionContent
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
        async changeOption(activeKey=0){
            this.activeKey = activeKey;
            let formData = new FormData();
            formData.append('material', this.questionData[activeKey].material);
            await Repeat.getRepeatQuestionInfo($("[name=questionBankId]").val(), formData).then(res => {
                console.log(res)
            })
        },
        goBack() {
            window.history.back();
        },
        async getData() {
            await Repeat.getRepeatQuestion($("[name=questionBankId]").val(), { categoryId: $("[name=categoryId]").val() }).then(res => {
                this.questionData = res
      });
        }
    }
}
</script>