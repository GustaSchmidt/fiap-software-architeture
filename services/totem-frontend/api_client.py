import requests
import os
from dotenv import load_dotenv

load_dotenv()

BASE_URL = os.getenv("API_URL", "http://localhost:8000/api")
API_KEY = os.getenv("API_KEY", "sua-chave-secreta")

headers = {
    "x-api-key": API_KEY, # Ajuste conforme o nome do header esperado pelo EnsureApiKeyIsValid
    "Content-Type": "application/json",
    "Accept": "application/json"
}

def buscar_cliente(cpf):
    """
    Busca cliente por CPF.
    Rota: POST /client/search_cpf
    """
    try:
        response = requests.post(f"{BASE_URL}/client/search_cpf", json={"cpf": cpf}, headers=headers)
        if response.status_code == 200:
            return response.json()
        return None
    except Exception as e:
        print(f"Erro ao conectar: {e}")
        return None

def criar_cliente(nome, cpf, email):
    """
    Cria um novo cliente.
    Rota: POST /client/create
    """
    payload = {"nome": nome, "cpf": cpf, "email": email}
    response = requests.post(f"{BASE_URL}/client/create", json=payload, headers=headers)
    return response.json() if response.status_code == 201 else None

def listar_produtos(categoria=None):
    """
    Lista produtos. Note que a rota é POST e o controller lê query param.
   
    Rota: POST /product/category_list
    """
    url = f"{BASE_URL}/product/category_list"
    if categoria:
        url += f"?categoria={categoria}" # Passando como query string para satisfazer o controller
    
    response = requests.post(url, headers=headers)
    return response.json() if response.status_code == 200 else []

def adicionar_item(client_id, produto_id, quantidade=1):
    """
    Adiciona item à sacola.
    Rota: POST /sacola/add
    """
    payload = {
        "client_id": client_id,
        "produto_id": produto_id,
        "quantidade": quantidade
    }
    response = requests.post(f"{BASE_URL}/sacola/add", json=payload, headers=headers)
    return response.status_code == 200

def ver_sacola(client_id):
    """
    Vê itens da sacola.
    Rota: GET /sacola/client/{id}
    """
    response = requests.get(f"{BASE_URL}/sacola/client/{client_id}", headers=headers)
    return response.json().get('data', []) if response.status_code == 200 else []

def checkout(client_id):
    """
    Finaliza o pedido.
    Rota: POST /sacola/checkout
    """
    response = requests.post(f"{BASE_URL}/sacola/checkout", json={"client_id": client_id}, headers=headers)
    return response.json() if response.status_code == 200 else None