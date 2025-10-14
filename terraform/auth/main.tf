# Configura o Terraform Cloud para o workspace de autenticação
terraform {
  cloud {
    organization = "FIAP-SOAT-ORG"
    workspaces {
      name = "fiap-soat-auth" # CORRIGIDO: Aponta para o workspace correto
    }
  }
}

# Configura o provedor da AWS
provider "aws" {
  region = "us-east-1" # Você pode mover isso para um variables.tf depois
}

# --- Variáveis de Entrada ---

variable "lambda_code_version" {
  description = "A versão do código (commit SHA) a ser implantada, vinda do CI/CD."
  type        = string
}

variable "function_name" {
  description = "O nome da função Lambda."
  type        = string
  default     = "auth-cpf-lambda"
}

variable "api_name" {
  description = "O nome do API Gateway."
  type        = string
  default     = "auth-api"
}

# --- Recursos de Armazenamento de Artefatos ---

# Bucket S3 para armazenar os pacotes de código da Lambda
resource "aws_s3_bucket" "lambda_artifacts" {
  # Constrói um nome de bucket único usando o ID da conta AWS
  bucket = "fiap-soat-lambda-code-artifacts"

  tags = {
    Name = "Lambda Artifacts Storage"
  }
}

# Obtém o ID da conta da AWS para garantir um nome de bucket único
data "aws_caller_identity" "current" {}


# --- Recursos da Lambda e IAM ---

# Cria a role (permissão) de execução para a Lambda
resource "aws_iam_role" "lambda_exec_role" {
  name = "${var.function_name}-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [{
      Action    = "sts:AssumeRole",
      Effect    = "Allow",
      Principal = { Service = "lambda.amazonaws.com" }
    }]
  })
}

# Anexa a política básica de execução da Lambda à role criada
resource "aws_iam_role_policy_attachment" "lambda_policy" {
  role       = aws_iam_role.lambda_exec_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSLambdaBasicExecutionRole"
}

# Cria a função Lambda, apontando para o código no S3
resource "aws_lambda_function" "auth_lambda" {
  function_name = var.function_name
  handler       = "index.handler"
  runtime       = "nodejs18.x"
  role          = aws_iam_role.lambda_exec_role.arn

  # Aponta para o código no bucket S3, usando a versão passada pelo workflow
  s3_bucket = aws_s3_bucket.lambda_artifacts.id
  s3_key    = "lambda-auth-${var.lambda_code_version}.zip"

  # Você irá preencher estas variáveis no Terraform Cloud
  environment {
    variables = {
      DB_HOST     = "DEFINIR_NO_TERRAFORM_CLOUD"
      DB_PORT     = "5432"
      DB_DATABASE = "DEFINIR_NO_TERRAFORM_CLOUD"
      DB_USERNAME = "DEFINIR_NO_TERRAFORM_CLOUD"
      DB_PASSWORD = "DEFINIR_NO_TERRAFORM_CLOUD"
      JWT_SECRET  = "DEFINIR_NO_TERRAFORM_CLOUD"
    }
  }
}


# --- Recursos do API Gateway ---

# Cria o API Gateway (HTTP API)
resource "aws_apigatewayv2_api" "http_api" {
  name          = var.api_name
  protocol_type = "HTTP"
}

# Cria a integração entre o API Gateway e a Lambda
resource "aws_apigatewayv2_integration" "lambda_integration" {
  api_id           = aws_apigatewayv2_api.http_api.id
  integration_type = "AWS_PROXY"
  integration_uri  = aws_lambda_function.auth_lambda.invoke_arn
}

# Cria a rota POST /auth/cpf
resource "aws_apigatewayv2_route" "auth_route" {
  api_id    = aws_apigatewayv2_api.http_api.id
  route_key = "POST /auth/cpf"
  target    = "integrations/${aws_apigatewayv2_integration.lambda_integration.id}"
}

# Dá permissão para o API Gateway invocar a função Lambda
resource "aws_lambda_permission" "api_gw_permission" {
  statement_id  = "AllowAPIGatewayInvoke"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.auth_lambda.function_name
  principal     = "apigateway.amazonaws.com"
  source_arn    = "${aws_apigatewayv2_api.http_api.execution_arn}/*/*"
}