// Usaremos o driver do PostgreSQL para conectar ao banco de dados.
const { Client } = require('pg'); 
// Biblioteca para gerar o token JWT.
const jwt = require('jsonwebtoken');

exports.handler = async (event) => {
    // Pega o CPF do corpo da requisição.
    const { cpf } = JSON.parse(event.body);

    // Validação básica do CPF.
    if (!cpf || !/^\d{11}$/.test(cpf)) {
        return {
            statusCode: 400,
            body: JSON.stringify({ message: 'CPF inválido. Forneça um CPF com 11 dígitos.' }),
        };
    }

    
    // Configurações de conexão com o banco de dados.
    const dbConfig = {
        host: process.env.DB_HOST,
        port: process.env.DB_PORT,
        user: process.env.DB_USERNAME,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_DATABASE,
        ssl: {
            rejectUnauthorized: false 
        }
    };

    const client = new Client(dbConfig);
    let clientId;

    try {
        await client.connect();

        // Cria tabela se nao existir
        await client.query(`
            CREATE TABLE IF NOT EXISTS clients (
                id SERIAL PRIMARY KEY,
                cpf VARCHAR(14) UNIQUE NOT NULL,
                nome VARCHAR(255) NULL,       -- Permitir NULL pois a Lambda não envia (app que se vire para garantir isso)
                sobrenome VARCHAR(255) NULL,  -- Permitir NULL
                email VARCHAR(255) NULL,      -- Permitir NULL
                senha VARCHAR(255) NULL,      -- Permitir NULL
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        `);

        // 1. Verifica se o cliente com o CPF fornecido já existe.
        const result = await client.query('SELECT id FROM clients WHERE cpf = $1', [cpf]);

        if (result.rows.length > 0) {
            // Cliente já existe, pega o ID.
            clientId = result.rows[0].id;
        } else {
            // 2. Se não existir, cria um novo cliente.
            // Aqui, estamos inserindo apenas o CPF. Você pode adicionar nome, email, etc.
            const insertResult = await client.query(
                'INSERT INTO clients (cpf, created_at, updated_at) VALUES ($1, NOW(), NOW()) RETURNING id',
                [cpf]
            );
            clientId = insertResult.rows[0].id;
        }

    } catch (error) {
        console.error('Erro no banco de dados:', error);
        return {
            statusCode: 500,
            body: JSON.stringify({ message: 'Erro interno do servidor.' }),
        };
    } finally {
        await client.end();
    }

    // 3. Gera o token JWT.
    
    const token = jwt.sign(
        { 
            clientId: clientId,
            cpf: cpf 
        }, 
        process.env.JWT_SECRET,
        { expiresIn: '1h' }
    );

    // Retorna o token para o cliente.
    return {
        statusCode: 200,
        body: JSON.stringify({ 
            message: 'Autenticação bem-sucedida!',
            token: token 
        }),
    };
};
