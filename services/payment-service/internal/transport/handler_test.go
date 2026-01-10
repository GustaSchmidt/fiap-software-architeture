package transport

import (
	"bytes"
	"encoding/json"
	"errors"
	"net/http"
	"net/http/httptest"
	"testing"

	"github.com/mercadopago/sdk-go/pkg/payment"
)

// ============================================================================
// 1. MOCK do Serviço de Pagamento
// ============================================================================
type MockPaymentService struct {
	// Podemos configurar o mock para retornar erro ou sucesso
	ShouldError bool
}

func (m *MockPaymentService) CreatePayment(amount float64, description, email string) (*payment.Response, error) {
	if m.ShouldError {
		return nil, errors.New("erro de conexão com mercado pago")
	}

	return &payment.Response{
		ID:     123456789,
		Status: "pending",
		PointOfInteraction: payment.PointOfInteractionResponse{
			TransactionData: payment.TransactionDataResponse{
				QRCode:       "00020126580014BR.GOV.BCB.PIX...",
				QRCodeBase64: "base64image...",
			},
		},
	}, nil
}

// ============================================================================
// TESTES
// ============================================================================
func TestCreatePayment_Success(t *testing.T) {
	mockService := &MockPaymentService{ShouldError: false}
	handler := NewHandler(mockService)

	payload := PaymentRequest{
		Amount:      100.50,
		Description: "Pedido Teste",
		Email:       "teste@email.com",
		OrderId:     1,
	}
	body, _ := json.Marshal(payload)

	// Simula a Requisição (Act)
	req, _ := http.NewRequest("POST", "/api/pay", bytes.NewBuffer(body))
	rr := httptest.NewRecorder() // "Gravador" de resposta

	// Chama o método diretamente
	handler.CreatePayment(rr, req)

	// Verificações (Assert)
	if status := rr.Code; status != http.StatusCreated {
		t.Errorf("handler retornou código errado: recebido %v esperado %v", status, http.StatusCreated)
	}

	var response map[string]interface{}
	json.Unmarshal(rr.Body.Bytes(), &response)

	if response["status"] != "pending" {
		t.Errorf("status inesperado no json: recebido %v", response["status"])
	}
	
	// Verifica se o ID do pagamento fake veio (float64 é como o JSON unmarshal números)
	if response["payment_id"].(float64) != 123456789 {
		t.Errorf("payment_id inesperado")
	}
}

func TestCreatePayment_InvalidMethod(t *testing.T) {
	mockService := &MockPaymentService{}
	handler := NewHandler(mockService)

	// Tenta fazer um GET em vez de POST
	req, _ := http.NewRequest("GET", "/api/pay", nil)
	rr := httptest.NewRecorder()

	handler.CreatePayment(rr, req)

	if status := rr.Code; status != http.StatusMethodNotAllowed {
		t.Errorf("esperava 405 Method Not Allowed, recebeu %v", status)
	}
}

func TestCreatePayment_ServiceError(t *testing.T) {
	// Configura o Mock para FALHAR
	mockService := &MockPaymentService{ShouldError: true}
	handler := NewHandler(mockService)

	payload := PaymentRequest{Amount: 50.00, Email: "fail@test.com"}
	body, _ := json.Marshal(payload)

	req, _ := http.NewRequest("POST", "/api/pay", bytes.NewBuffer(body))
	rr := httptest.NewRecorder()

	handler.CreatePayment(rr, req)

	// Espera erro 500
	if status := rr.Code; status != http.StatusInternalServerError {
		t.Errorf("esperava 500 Internal Server Error, recebeu %v", status)
	}
}

func TestCreatePayment_InvalidJSON(t *testing.T) {
	mockService := &MockPaymentService{}
	handler := NewHandler(mockService)

	// Envia JSON quebrado
	req, _ := http.NewRequest("POST", "/api/pay", bytes.NewBuffer([]byte("{invalid-json")))
	rr := httptest.NewRecorder()

	handler.CreatePayment(rr, req)

	if status := rr.Code; status != http.StatusBadRequest {
		t.Errorf("esperava 400 Bad Request, recebeu %v", status)
	}
}