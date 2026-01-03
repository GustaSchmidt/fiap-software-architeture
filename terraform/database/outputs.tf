output "db_endpoint" {
  description = "O endpoint (host) do banco de dados RDS"
  value       = aws_db_instance.default.endpoint
}

output "db_port" {
  description = "A porta do banco de dados RDS"
  value       = aws_db_instance.default.port
}

output "subnet_ids" {
  description = "List of IDs for the database subnets"
  value       = [aws_subnet.db_subnet_a.id, aws_subnet.db_subnet_b.id]
}