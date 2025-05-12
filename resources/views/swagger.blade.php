<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />
    <style>
      html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
      
      *, *:before, *:after { box-sizing: inherit; }
      
      body { margin: 0; background: #fafafa; }
      
      .swagger-ui .opblock .opblock-summary-description {
        color: #3b4151;
        font-family: sans-serif;
        font-size: 13px;
        word-break: break-word;
        margin-left: 20px;
      }
      .opblock-summary-method{
        margin-right: 10px;
      }
    </style>
  </head>
  <body>
    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" defer></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js" defer></script>

    <script>
    window.onload = function() {
        const ui = SwaggerUIBundle({
        url: "/swagger",
        dom_id: '#swagger-ui',
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        layout: "StandaloneLayout",
        });
    };
    </script>

  </body>
</html>
