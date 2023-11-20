<template>
    <div class="duplicate-question-item" @click="changeOption" :class="active ? 'duplicate-question-active' : ''">
        <div class="duplicate-question-title">
            《诗经》的表现手法归纳为赋、比、兴，其中（ ）的手法是铺
        </div>
        <span class="duplicate-question-check-count">10次</span>
    </div>
</template>
<script>
export default {
    props: {
        // 题干
        active: {
            type: Boolean,
            default: false
        },
        id: {
            type: Number,
            default: 0
        },
    },
    methods: {
        // 切换课程选项
        changeOption() {
            this.$emit('changeOption', this.id);
        }
    }
}
</script>