package transport

import (
	"encoding/json"
	"net/http"

	"github.com/mercadopago/sdk-go/pkg/payment"
)

// 1. Definição da Interface
type PaymentUseCase interface {
	CreatePayment(amount float64, description, email string) (*payment.Response, error)
}

// 2. DTO (Data Transfer Object) - Exportado para ser visível nos testes
type PaymentRequest struct {
	Amount      float64 `json:"amount"`
	Description string  `json:"description"`
	Email       string  `json:"email"`
	OrderId     int     `json:"order_id"`
}

// 3. Definição da Struct Handler
type Handler struct {
	service PaymentUseCase
}

// 4. Construtor
func NewHandler(service PaymentUseCase) *Handler {
	return &Handler{
		service: service,
	}
}

// 5. Método CreatePayment
func (h *Handler) CreatePayment(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req PaymentRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid Payload", http.StatusBadRequest)
		return
	}

	resp, err := h.service.CreatePayment(req.Amount, req.Description, req.Email)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	
	response := map[string]interface{}{
		"payment_id": resp.ID,
		"status":     resp.Status,
	}

	// Verifica se existe QR Code na resposta e preenche
	if resp.PointOfInteraction.TransactionData.QRCode != "" {
		response["qr_code"] = resp.PointOfInteraction.TransactionData.QRCode
		response["qr_code_base64"] = resp.PointOfInteraction.TransactionData.QRCodeBase64
	}

	json.NewEncoder(w).Encode(response)
}

// 6. Método HandleWebhook
func (h *Handler) HandleWebhook(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}
	
	w.WriteHeader(http.StatusOK)
	w.Write([]byte(`{"status":"received"}`))
}