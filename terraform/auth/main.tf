# Configura terraform cloud
terraform { 
  cloud { 
    
    organization = "FIAP-SOAT-ORG" 

    workspaces { 
      name = "fiap-soat-database" 
    } 
  } 
}

# Configura o provedor da AWS
provider "aws" {
  region = var.aws_region
}

# 1. Empacota o código da Lambda em um arquivo .zip
data "archive_file" "lambda_zip" {
  type        = "zip"
  source_dir  = "../../lambda_auth_cpf/" # Caminho para a pasta da Lambda
  output_path = "lambda_auth_cpf.zip"
}

# 2. Cria a role de execução para a Lambda
resource "aws_iam_role" "lambda_exec_role" {
  name = "${var.function_name}-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [{
      Action = "sts:AssumeRole",
      Effect = "Allow",
      Principal = {
        Service = "lambda.amazonaws.com"
      }
    }]
  })
}

# Anexa a política básica de execução da Lambda à role criada
resource "aws_iam_role_policy_attachment" "lambda_policy" {
  role       = aws_iam_role.lambda_exec_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSLambdaBasicExecutionRole"
}

# 3. Cria a função Lambda
resource "aws_lambda_function" "auth_lambda" {
  function_name    = var.function_name
  filename         = data.archive_file.lambda_zip.output_path
  source_code_hash = data.archive_file.lambda_zip.output_base64sha256
  
  handler = "index.handler" # Arquivo 'index.js', função 'handler'
  runtime = "nodejs18.x"
  role    = aws_iam_role.lambda_exec_role.arn

  
  environment {
    variables = {
      DB_HOST     = "SEU_DB_HOST"
      DB_PORT     = "5432"
      DB_DATABASE = "SEU_DB_DATABASE"
      DB_USERNAME = "SEU_DB_USERNAME"
      DB_PASSWORD = "SEU_DB_PASSWORD"
      JWT_SECRET  = "SEU_JWT_SECRET_SUPER_SECRETO"
    }
  }
}

# 4. Cria o API Gateway (HTTP API, que é mais simples e barata)
resource "aws_apigatewayv2_api" "http_api" {
  name          = var.api_name
  protocol_type = "HTTP"
}

# 5. Cria a integração entre o API Gateway e a Lambda
resource "aws_apigatewayv2_integration" "lambda_integration" {
  api_id           = aws_apigatewayv2_api.http_api.id
  integration_type = "AWS_PROXY"
  integration_uri  = aws_lambda_function.auth_lambda.invoke_arn
}

# 6. Cria a rota POST /auth/cpf
resource "aws_apigatewayv2_route" "auth_route" {
  api_id    = aws_apigatewayv2_api.http_api.id
  route_key = "POST /auth/cpf"
  target    = "integrations/${aws_apigatewayv2_integration.lambda_integration.id}"
}

# 7. Dá permissão para o API Gateway invocar a função Lambda
resource "aws_lambda_permission" "api_gw_permission" {
  statement_id  = "AllowAPIGatewayInvoke"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.auth_lambda.function_name
  principal     = "apigateway.amazonaws.com"

  source_arn = "${aws_apigatewayv2_api.http_api.execution_arn}/*/*"
}