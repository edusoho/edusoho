import SwaggerUI from 'swagger-ui';

SwaggerUI({
  dom_id: '#swaggerId',
  url: `/index.yaml?${new Date().getTime()}`,
  presets: [
    SwaggerUI.presets.apis,
  ],
})