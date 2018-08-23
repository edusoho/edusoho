import * as types from '@admin/store/mutation-types';
import Api from '@admin/api';

export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

