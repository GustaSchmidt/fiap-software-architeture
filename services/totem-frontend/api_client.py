import requests
import os
from dotenv import load_dotenv

load_dotenv()

BASE_URL = os.getenv("API_URL", "http://localhost:8000/api")
API_KEY = os.getenv("API_KEY", "sua-chave-secreta-123") 

HEADERS = {
    "x-api-key": API_KEY,
    "Content-Type": "application/json",
    "Accept": "application/json"
}

def buscar_cliente(cpf):
    """Busca cliente por CPF."""
    try:
        # Remove formatação do CPF se houver
        cpf_limpo = ''.join(filter(str.isdigit, cpf))
        response = requests.post(f"{BASE_URL}/client/search_cpf", json={"cpf": cpf_limpo}, headers=HEADERS, timeout=5)
        if response.status_code == 200:
            return response.json()
        return None
    except Exception as e:
        print(f"Erro ao buscar cliente: {e}")
        return None

def criar_cliente(nome, cpf, email):
    """Cria novo cliente."""
    try:
        payload = {"nome": nome, "sobrenome": "Totem", "cpf": cpf, "email": email, "senha": "totem_default"} # Ajuste campos conforme seu Request
        response = requests.post(f"{BASE_URL}/client/create", json=payload, headers=HEADERS, timeout=5)
        return response.json() if response.status_code == 201 else None
    except Exception:
        return None

def listar_produtos(categoria=None):
    """Lista produtos por categoria."""
    try:
        url = f"{BASE_URL}/product/category_list"
        if categoria:
            url += f"?categoria={categoria}"
        
        response = requests.post(url, headers=HEADERS, timeout=5)
        return response.json() if response.status_code == 200 else []
    except Exception:
        return []

def adicionar_item(client_id, produto_id, quantidade=1):
    """Adiciona item à sacola."""
    try:
        payload = {
            "client_id": client_id,
            "produto_id": produto_id,
            "quantidade": quantidade
        }
        response = requests.post(f"{BASE_URL}/sacola/add", json=payload, headers=HEADERS, timeout=5)
        return response.status_code == 200
    except Exception:
        return False

def ver_sacola(client_id):
    """Retorna itens da sacola."""
    try:
        response = requests.get(f"{BASE_URL}/sacola/client/{client_id}", headers=HEADERS, timeout=5)
        data = response.json()
        # Ajuste para pegar a lista correta dependendo da resposta da sua API (data ou root)
        return data.get('data', []) if isinstance(data, dict) else data
    except Exception:
        return []

def checkout(client_id):
    """Finaliza o pedido."""
    try:
        payload = {"client_id": client_id}
        response = requests.post(f"{BASE_URL}/sacola/checkout", json=payload, headers=HEADERS, timeout=10)
        if response.status_code == 200:
            return response.json()
        return None
    except Exception:
        return None