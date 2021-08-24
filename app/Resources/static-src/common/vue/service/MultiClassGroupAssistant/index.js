import { apiClient } from 'common/vue/service/api-client.js';

export default {
  editGroupAssistant ({ query = {}, params = {}, data = {} } = {}) {
    return apiClient.patch(`/api/multi_class/${query.multiClassId}/group_assistant/${query.assistantId}`,   data  )
  },
}