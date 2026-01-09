package main

import (
	"log"
	"net/http"
	"os"

	"github.com/gustaschmidt/fiap-payment-service/internal/platform"
	"github.com/gustaschmidt/fiap-payment-service/internal/transport"
)

func main() {
	// 1. Configuração
	mpAccessToken := os.Getenv("MP_ACCESS_TOKEN")
	if mpAccessToken == "" {
		log.Fatal("ERRO: A variável de ambiente MP_ACCESS_TOKEN é obrigatória.")
	}

	// 2. Inicialização das Dependências (Services)
	mpService := platform.NewMercadoPagoService(mpAccessToken)
	
	// 3. Inicialização do Handler (Transport Layer)
	handler := transport.NewHandler(mpService)

	// 4. Definição das Rotas
	http.HandleFunc("/api/pay", handler.CreatePayment)
	http.HandleFunc("/api/webhook", handler.HandleWebhook)

	// 5. Start do Servidor
	log.Println("🚀 Payment Service rodando na porta 8081")
	if err := http.ListenAndServe(":8081", nil); err != nil {
		log.Fatal(err)
	}
}