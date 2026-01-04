output "db_endpoint" {
  description = "O endpoint (host) do banco de dados RDS"
  value       = aws_db_instance.default.endpoint
}

output "db_host" {
  description = "O endereço (host) do banco de dados RDS (apenas DNS, sem porta)"
  value       = aws_db_instance.default.address
}

output "db_port" {
  description = "A porta do banco de dados RDS"
  value       = aws_db_instance.default.port
}

output "db_name" {
  description = "Nome do banco de dados"
  value       = aws_db_instance.default.db_name
}

output "db_username" {
  description = "Usuário master do banco"
  value       = aws_db_instance.default.username
}

output "db_password" {
  description = "Senha master do banco (Sensível)"
  value       = aws_db_instance.default.password
  sensitive   = true
}


# --- Outros Outputs tenho que mudar essa bucha pra rede ser criada a parte (outro TF)---

output "subnet_ids" {
  description = "List of IDs for the database subnets"
  value       = [aws_subnet.db_subnet_a.id, aws_subnet.db_subnet_b.id]
}