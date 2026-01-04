variable "aws_region" {
  description = "Região da AWS para criar os recursos"
  type        = string
  default     = "us-east-1" 
}

variable "function_name" {
  description = "Nome da função Lambda"
  type        = string
  default     = "auth-cpf-lambda"
}

variable "api_name" {
  description = "Nome do API Gateway"
  type        = string
  default     = "auth-api"
}

variable "lambda_code_version" {
  description = "A versão do código (commit SHA) a ser implantada, vinda do CI/CD."
  type        = string
  default     = "latest"
}

variable "jwt_secret" {
  description = "Chave secreta para assinar os tokens JWT"
  type        = string
  sensitive   = true
}