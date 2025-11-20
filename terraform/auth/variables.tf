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

variable "lambda_s3_key" {
  description = "A S3 key completa para o ficheiro .zip do código da Lambda."
  type        = string
}
