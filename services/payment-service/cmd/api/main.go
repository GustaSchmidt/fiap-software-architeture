package main

import (
	"log"
	"net/http"
	"os"

	"github.com/joho/godotenv" // Importar o pacote
	"github.com/gustaschmidt/fiap-payment-service/internal/platform"
	"github.com/gustaschmidt/fiap-payment-service/internal/transport"
)

func main() {
	err := godotenv.Load()
	if err != nil {
		log.Println("Aviso: Arquivo .env não encontrado, usando variáveis de ambiente do sistema.")
	}

	// 2. Configuração
	mpAccessToken := os.Getenv("MP_ACCESS_TOKEN")
	if mpAccessToken == "" {
		log.Fatal("❌ ERRO CRÍTICO: A variável de ambiente MP_ACCESS_TOKEN é obrigatória.")
	}

	// 3. Inicialização das Dependências (Services)
	mpService := platform.NewMercadoPagoService(mpAccessToken)
	
	// 4. Inicialização do Handler (Transport Layer)
	handler := transport.NewHandler(mpService)

	// 5. Definição das Rotas
	http.HandleFunc("/api/pay", handler.CreatePayment)
	http.HandleFunc("/api/webhook", handler.HandleWebhook)

	// 6. Start do Servidor
	log.Println("🚀 Payment Service rodando na porta 8081")
	// ListenAndServe é uma função que BLOQUEIA o terminal (fica escutando). Isso é normal.
	if err := http.ListenAndServe(":8081", nil); err != nil {
		log.Fatal(err)
	}
}