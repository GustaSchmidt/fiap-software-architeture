package platform

import (
    "time"
)

type PaymentEntity struct {
    PaymentID     string    `json:"payment_id" dynamodbav:"payment_id"` // PK
    OrderID       int       `json:"order_id" dynamodbav:"order_id"`     // GSI
    Status        string    `json:"status" dynamodbav:"status"`
    Amount        float64   `json:"amount" dynamodbav:"amount"`
    QrCode        string    `json:"qr_code" dynamodbav:"qr_code"`
    CreatedAt     time.Time `json:"created_at" dynamodbav:"created_at"`
    UpdatedAt     time.Time `json:"updated_at" dynamodbav:"updated_at"`
}