output "api_endpoint_url" {
  description = "URL base do API Gateway"
  value       = aws_apigatewayv2_api.http_api.api_endpoint
}