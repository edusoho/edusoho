import SwaggerUI from 'swagger-ui';

SwaggerUI({
  dom_id: '#swaggerId',
  url: `/swagger-api.yaml?${new Date().getTime()}`,
  presets: [
    SwaggerUI.presets.apis,
  ],
})