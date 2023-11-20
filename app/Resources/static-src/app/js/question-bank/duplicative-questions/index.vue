<template>
    <div id="duplicate-check">
       <div class="duplicate-head">
        <span class="duplicate-back">
            <a-icon type="left" />
            返回
        </span>
        <span class="duplicate-divider"></span>
        <span class="duplicate-title">【全部题目】试题查重</span>
       </div>
       <div class="duplicate-body flex">
        <div class="duplicate-question">
            <div class="duplicate-question-head">
                共有重复题目:<label class="duplicate-question-count">4</label>道
            </div>
            <duplicate-question-item 
                v-for="(item, index) in 5" 
                :active="index == activeKey"
                :id="index"
                :key="index"
                @changeOption="changeOption" />
        </div>
        <div class="duplicate-content">
            <div class="duplicate-content-title">题目对比</div>
            <div class="mt16 flex flex-nowrap">
                <duplicate-question-content class="mr16" />
                <duplicate-question-content />
            </div>
        </div>
       </div>
    </div>
</template>
<script>
import DuplicateQuestionItem from "./components/DuplicateQuestionItem.vue";
import DuplicateQuestionContent from "./components/DuplicateQuestionContent.vue";

export default {
    data(){
        return{
            activeKey: 0
        }
    },
    components:{
        DuplicateQuestionItem,
        DuplicateQuestionContent
    },
    methods:{
        changeOption(activeKey){
            this.activeKey = activeKey;
        }
    }
}
</script>