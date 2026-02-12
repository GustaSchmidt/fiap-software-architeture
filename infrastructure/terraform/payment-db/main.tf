resource "aws_dynamodb_table" "payments" {
  name           = "fiap-payments"
  billing_mode   = "PAY_PER_REQUEST" # Free tier friendly (to pobre)
  hash_key       = "payment_id"

  attribute {
    name = "payment_id"
    type = "S"
  }

  attribute {
    name = "order_id"
    type = "N"
  }

  # Índice para buscar pagamento pelo ID do Pedido
  global_secondary_index {
    name               = "OrderIdIndex"
    hash_key           = "order_id"
    projection_type    = "ALL"
  }

  tags = {
    Environment = "production"
    Project     = "fiap-tech-challenge"
  }
}