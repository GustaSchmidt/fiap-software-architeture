package transport

import (
	"encoding/json"
	"net/http"
	"github.com/gustaschmidt/fiap-payment-service/internal/platform"
)

// Request Body que esperamos receber do Laravel
type PaymentRequest struct {
	Amount      float64 `json:"amount"`
	Description string  `json:"description"`
	Email       string  `json:"email"`
	OrderId     int     `json:"order_id"` // ID do pedido para vincular
}

// Handler agrupa as dependências necessárias (no caso, o serviço do MP)
type Handler struct {
	mpService *platform.MercadoPagoService
}

// NewHandler cria uma nova instância do Handler
func NewHandler(service *platform.MercadoPagoService) *Handler {
	return &Handler{
		mpService: service,
	}
}

// CreatePayment lida com a criação de pagamentos (POST /api/pay)
func (h *Handler) CreatePayment(w http.ResponseWriter, r *http.Request) {
	// 1. Validação de Método
	if r.Method != http.MethodPost {
		http.Error(w, "Método não permitido", http.StatusMethodNotAllowed)
		return
	}

	// 2. Decodificar o JSON
	var req PaymentRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Payload inválido", http.StatusBadRequest)
		return
	}

	// 3. Chamar a Camada de Negócio/Plataforma
	resp, err := h.mpService.CreatePayment(req.Amount, req.Description, req.Email)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	// 4. Retornar a resposta JSON
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"payment_id":     resp.ID,
		"qr_code":        resp.PointOfInteraction.TransactionData.QrCode,
		"qr_code_base64": resp.PointOfInteraction.TransactionData.QrCodeBase64,
		"status":         resp.Status,
	})
}

// HandleWebhook recebe as notificações do Mercado Pago (via Proxy do Laravel)
func (h *Handler) HandleWebhook(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Método não permitido", http.StatusMethodNotAllowed)
		return
	}

	// Aqui você processaria o JSON recebido
	// var notification map[string]interface{}
	// json.NewDecoder(r.Body).Decode(&notification)

	// Lógica de buscar o status atualizado no MP e salvar no banco local viria aqui...
	
	w.WriteHeader(http.StatusOK)
	w.Write([]byte(`{"status":"received"}`))
}