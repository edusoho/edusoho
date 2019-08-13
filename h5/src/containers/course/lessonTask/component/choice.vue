<template>
  <div class="subject">
    <div class="subject-stem">
			<span class="subject-stem__order">{{ choice.id }}、</span>
			<div class="subject-stem__content" v-html="choice.stem"></div>
		</div>
		<van-checkbox-group v-model="result">
			<van-checkbox class="subject-option"
				v-for="(item, index) in choice.metas.choices"
				:key="item"
				:name="item"
			>
				<div class="subject-option__content" v-html="item"></div>
				<span
					slot="icon"
					slot-scope="props"
					class="subject-option__order subject-option__order--square"
				>{{ index|filterOrder }}</span>
			</van-checkbox>
		</van-checkbox-group>
  </div>
</template>

<script>
export default {
  name: 'choice',
  data() {
    return {
      choice: {
				"id": "5",
				"type": "choice",
				"stem": "<p>测试多选题</p>\r\n",
				"score": "2.0",
				"metas": {
						"choices": [
							"<p>选项A</p>\n",
							"<p>选项B</p>\n",
							"<p>选项C</p>\n",
							"<p>选项D</p>\n"
						]
				},
				"categoryId": "0",
				"difficulty": "normal",
				"target": "course-20",
				"courseId": "0",
				"lessonId": "0",
				"parentId": "0",
				"subCount": "0",
				"finishedTimes": "0",
				"passedTimes": "0",
				"createdUserId": "2",
				"updatedUserId": "2",
				"courseSetId": "20",
				"seq": "2",
				"missScore": "0.0"
      },
      result: [],
    }
  },
  methods: {
  },
  filters: {
    filterOrder(index) {
      const arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
      return arr[index];
    },
  },

}
</script>