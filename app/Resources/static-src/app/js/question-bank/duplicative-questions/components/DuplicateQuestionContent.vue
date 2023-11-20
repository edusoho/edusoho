<template>
    <div class="duplicate-question-content">
        <div>
            <button v-for="(item, index) in 20" :key="index" class="numbering">{{ item }}</button>
        </div>
        <div class="question-content mt8">
            <div class="question-head">
                <div class="question-head-item" v-for="(item, index) in headInfo" :key="index">
                    <div class="question-small-title">{{ item.title }}</div>
                    <div class="question-small-content">{{ item.content }}</div>
                </div>
            </div>
            <div class="question-body">
                <question-item />
            </div>
            <div class="question-foot">
                <a-button class="mr10">编辑</a-button>
                <a-button>删除</a-button>
            </div>

        </div>
    </div>
</template>
<script>
import QuestionItem from './Item'
export default {
    data() {
        return {
            headInfo: [
                {
                    title: '分类',
                    content: '杭州'
                },
                {
                    title: '类型',
                    content: '单选题'
                },
                {
                    title: '难度',
                    content: '一般'
                },
                {
                    title: '更新日期',
                    content: '2023-9-27'
                }
            ]
        }
    },
    components: {
        QuestionItem
    }
}
</script>