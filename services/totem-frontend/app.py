import streamlit as st
import api_client as api
import time

# --- CONFIGURAÇÃO DA PÁGINA ---
st.set_page_config(page_title="FIAP Totem", layout="wide", page_icon="🍔")

# --- CSS PERSONALIZADO (A MAGIA DO VISUAL) ---
st.markdown("""
<style>
    /* Fundo Dark Mode Global */
    .stApp {
        background-color: #121212;
        color: white;
    }
    
    /* Esconde menu padrão do Streamlit */
    #MainMenu {visibility: hidden;}
    footer {visibility: hidden;}
    header {visibility: hidden;}

    /* Títulos */
    h1, h2, h3 {
        font-family: 'Helvetica', sans-serif;
        color: #FFC72C !important; /* Amarelo Fast Food */
        text-align: center;
    }

    /* Input de Texto Gigante (Touch) */
    .stTextInput input {
        font-size: 30px;
        text-align: center;
        padding: 15px;
        border-radius: 12px;
        background-color: #272727;
        color: white;
        border: 2px solid #FFC72C;
    }

    /* Botões */
    .stButton button {
        width: 100%;
        height: 70px;
        font-size: 24px !important;
        font-weight: bold;
        border-radius: 15px;
        background-color: #FFC72C;
        color: #121212;
        border: none;
        transition: 0.3s;
    }
    .stButton button:hover {
        background-color: #e5b018;
        transform: scale(1.02);
        color: black;
    }
    
    /* Botão Secundário (Ex: Cancelar) */
    .btn-secondary button {
        background-color: #333333 !important;
        color: #ffffff !important;
    }

    /* Cards de Produto */
    div[data-testid="column"] {
        background-color: #1e1e1e;
        padding: 15px;
        border-radius: 15px;
        border: 1px solid #333;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }
    
    /* Abas de Categoria */
    .stTabs [data-baseweb="tab-list"] {
        gap: 10px;
        justify-content: center;
    }
    .stTabs [data-baseweb="tab"] {
        height: 60px;
        font-size: 20px;
        background-color: #272727;
        border-radius: 10px;
        color: white;
    }
    .stTabs [aria-selected="true"] {
        background-color: #FFC72C !important;
        color: black !important;
    }

</style>
""", unsafe_allow_html=True)

# --- ESTADO DA SESSÃO ---
if 'etapa' not in st.session_state:
    st.session_state['etapa'] = 'identificacao'
if 'cliente' not in st.session_state:
    st.session_state['cliente'] = None
if 'carrinho_atualizado' not in st.session_state:
    st.session_state['carrinho_atualizado'] = False

# --- FUNÇÕES AUXILIARES ---
def reiniciar():
    st.session_state['etapa'] = 'identificacao'
    st.session_state['cliente'] = None
    st.rerun()

def mudar_etapa(nova_etapa):
    st.session_state['etapa'] = nova_etapa
    st.rerun()

# ==========================================
# TELA 1: IDENTIFICAÇÃO (CPF)
# ==========================================
if st.session_state['etapa'] == 'identificacao':
    st.markdown("<h1>🍔 FIAP BURGER</h1>", unsafe_allow_html=True)
    st.markdown("<h3>Toque para se identificar</h3>", unsafe_allow_html=True)

    col1, col2, col3 = st.columns([1, 2, 1])
    with col2:
        cpf_input = st.text_input("CPF", placeholder="000.000.000-00", label_visibility="collapsed")
        
        st.write("") # Espaçamento
        
        if st.button("INICIAR PEDIDO ➔"):
            if not cpf_input:
                st.error("Por favor, digite seu CPF.")
            else:
                with st.spinner("Buscando cadastro..."):
                    cliente = api.buscar_cliente(cpf_input)
                    if cliente:
                        st.session_state['cliente'] = cliente
                        mudar_etapa('cardapio')
                    else:
                        st.warning("Cliente não encontrado. Use o App para cadastrar ou continue como convidado.")
                        # Simulação de criação rápida ou convidado
                        if st.button("Continuar como Visitante"):
                             # Cria um user temporário na API ou usa lógica de convidado
                             # Aqui chamaremos a criação para simplificar o fluxo do totem
                             novo = api.criar_cliente("Visitante", cpf_input, f"visitante{int(time.time())}@email.com")
                             if novo:
                                 st.session_state['cliente'] = novo
                                 mudar_etapa('cardapio')

# ==========================================
# TELA 2: CARDÁPIO (GRID DE PRODUTOS)
# ==========================================
elif st.session_state['etapa'] == 'cardapio':
    # Header com Info do Cliente
    c1, c2 = st.columns([3, 1])
    with c1:
        st.markdown(f"### Olá, {st.session_state['cliente']['nome']}!")
    with c2:
        if st.button("🛒 Ver Carrinho"):
            mudar_etapa('carrinho')

    st.divider()

    # Abas de Categoria
    categorias = ["LANCHE", "ACOMPANHAMENTO", "BEBIDA", "SOBREMESA"]
    abas = st.tabs([f"🍔 {c}" for c in categorias])

    for i, categoria in enumerate(categorias):
        with abas[i]:
            produtos = api.listar_produtos(categoria)
            
            if not produtos:
                st.info("Nenhum produto nesta categoria.")
            else:
                # Grid de Produtos (3 por linha)
                cols = st.columns(3)
                for idx, prod in enumerate(produtos):
                    with cols[idx % 3]:
                        st.markdown(f"**{prod['nome']}**")
                        st.markdown(f"<span style='color:#FFC72C; font-size:20px'>R$ {float(prod['preco']):.2f}</span>", unsafe_allow_html=True)
                        st.caption(prod.get('descricao', 'Sem descrição'))
                        
                        # Botão Adicionar com chave única
                        if st.button("ADICIONAR +", key=f"add_{prod['id']}"):
                            sucesso = api.adicionar_item(st.session_state['cliente']['id'], prod['id'], 1)
                            if sucesso:
                                st.toast(f"{prod['nome']} adicionado!", icon="✅")
                            else:
                                st.error("Erro ao adicionar.")
                    
                    # Quebra de linha visual no grid a cada 3 itens
                    if (idx + 1) % 3 == 0:
                        st.write("")

    st.write("---")
    if st.button("CANCELAR PEDIDO", type="primary"):
        reiniciar()

# ==========================================
# TELA 3: CARRINHO & CHECKOUT
# ==========================================
elif st.session_state['etapa'] == 'carrinho':
    st.markdown("<h1>🛒 Seu Pedido</h1>", unsafe_allow_html=True)
    
    itens = api.ver_sacola(st.session_state['cliente']['id'])
    
    if not itens:
        st.warning("Seu carrinho está vazio.")
        if st.button("🔙 Voltar ao Cardápio"):
            mudar_etapa('cardapio')
    else:
        total = 0.0
        
        # Lista de Itens (Estilizada)
        for item in itens:
            # Tratamento robusto para estrutura do JSON
            prod = item.get('produto', {})
            nome = prod.get('nome', 'Item desconhecido')
            preco = float(prod.get('preco', 0))
            qtd = item.get('quantidade', 1)
            subtotal = preco * qtd
            total += subtotal
            
            # Linha do item
            col_nome, col_qtd, col_val = st.columns([3, 1, 1])
            col_nome.markdown(f"**{nome}**")
            col_qtd.markdown(f"x{qtd}")
            col_val.markdown(f"R$ {subtotal:.2f}")
            st.divider()

        # Totalizador
        st.markdown(f"<h2 style='text-align:right'>TOTAL: R$ {total:.2f}</h2>", unsafe_allow_html=True)
        
        c1, c2 = st.columns(2)
        with c1:
            if st.button("🔙 Adicionar mais itens"):
                mudar_etapa('cardapio')
        with c2:
            if st.button("✅ FINALIZAR E PAGAR"):
                with st.spinner("Gerando pedido..."):
                    resultado = api.checkout(st.session_state['cliente']['id'])
                    if resultado:
                        st.session_state['pedido_final'] = resultado
                        mudar_etapa('sucesso')
                    else:
                        st.error("Erro ao processar checkout. Tente novamente.")

# ==========================================
# TELA 4: SUCESSO
# ==========================================
elif st.session_state['etapa'] == 'sucesso':
    pedido = st.session_state.get('pedido_final', {})
    id_pedido = pedido.get('pedido', {}).get('id', '???') if 'pedido' in pedido else pedido.get('id', '???')
    
    st.balloons()
    st.markdown("<h1>✅ PEDIDO CONFIRMADO!</h1>", unsafe_allow_html=True)
    
    st.markdown(f"""
    <div style="background:#28a745; padding:20px; border-radius:15px; text-align:center; margin: 20px 0;">
        <h2 style="color:white !important; margin:0;">SENHA</h2>
        <h1 style="color:white !important; font-size: 80px; margin:0;">#{id_pedido}</h1>
    </div>
    """, unsafe_allow_html=True)
    
    st.markdown("### Dirija-se ao caixa ou painel para pagamento.")
    
    st.write("")
    st.write("")
    
    if st.button("Fazer Novo Pedido"):
        reiniciar()