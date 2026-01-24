import os
from flask import Flask, render_template, jsonify, request
from pymongo import MongoClient
from datetime import datetime
from bson.json_util import dumps
import json

app = Flask(__name__)

# Conexão com o serviço definido no Kubernetes
MONGO_URI = os.environ.get('MONGO_URI', 'mongodb://mongodb-service:27017/')
client = MongoClient(MONGO_URI)
db = client['totem_db']
history_collection = db['historico_atendimentos']

@app.route('/')
def index():
    return render_template('index.html')


@app.route('/api/historico', methods=['POST'])
def save_history():
    data = request.json
    record = {
        "pedido_id": data.get("pedido_id"),
        "cliente": data.get("cliente"),
        "data_hora": datetime.utcnow(),
        "acao": "Finalizado no Totem"
    }
    history_collection.insert_one(record)
    return jsonify({"status": "sucesso"}), 201

@app.route('/api/historico', methods=['GET'])
def get_history():
    logs = list(history_collection.find().sort("data_hora", -1).limit(10))
    return json.loads(dumps(logs))

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=80)