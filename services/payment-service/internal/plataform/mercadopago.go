package platform

import (
	"context"
	"fmt"
	"github.com/mercadopago/sdk-go/pkg/config"
	"github.com/mercadopago/sdk-go/pkg/payment"
	"github.com/google/uuid"
)

type MercadoPagoService struct {
	client payment.Client
}

func NewMercadoPagoService(accessToken string) *MercadoPagoService {
	cfg, _ := config.New(accessToken)
	client := payment.NewClient(cfg)
	return &MercadoPagoService{client: client}
}

func (s *MercadoPagoService) CreatePayment(amount float64, description, payerEmail string) (*payment.Response, error) {
	// Cria uma requisição de pagamento
	request := payment.Request{
		TransactionAmount: amount,
		Description:       description,
		PaymentMethodID:   "pix",
		Payer: &payment.PayerRequest{
			Email: payerEmail,
		},
		ExternalReference: uuid.New().String()
	}

	// Envia para o Mercado Pago
	resource, err := s.client.Create(context.Background(), request)
	if err != nil {
		return nil, fmt.Errorf("erro ao criar pagamento: %v", err)
	}

	return resource, nil
}