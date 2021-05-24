import _ from 'lodash';
import BaseService from '../BaseService';

const baseUrl = '/api/upload_token';
const baseService = new BaseService({ baseUrl });

export const UploadToken = _.assignIn(baseService, {

});
