variable "aws_region" {
  description = "Região da AWS para criar os recursos"
  type        = string
  default     = "us-east-1"
}

variable "vpc_cidr_block" {
  description = "Bloco de CIDR para a VPC"
  type        = string
  default     = "10.0.0.0/16"
}

variable "db_name" {
  description = "Nome do banco de dados a ser criado"
  type        = string
  default     = "fiap_soat"
}

variable "db_username" {
  description = "Nome de usuário mestre para o banco de dados"
  type        = string
  default     = "fiap_db_user_main"
}

variable "db_password" {
  description = "Senha para o usuário mestre do banco de dados"
  type        = string
  sensitive   = true
}

variable "db_instance_class" {
  description = "Classe da instância do RDS (tamanho da máquina)"
  type        = string
  default     = "db.t3.micro"
}