import SwaggerUI from 'swagger-ui';

SwaggerUI({
  dom_id: '#swaggerId',
  url: "https://petstore.swagger.io/v2/swagger.json",
  presets: [
    SwaggerUI.presets.apis,
  ],
})