import SwaggerUI from 'swagger-ui';

SwaggerUI({
  dom_id: '#swaggerId',
  url: "/index.yaml",
  presets: [
    SwaggerUI.presets.apis,
  ],
})