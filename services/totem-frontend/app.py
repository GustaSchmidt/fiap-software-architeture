import streamlit as st
import api_client as api

st.set_page_config(page_title="Totem Autoatendimento", layout="wide")

# Gerenciamento de Estado da Sessão (Session State)
if 'cliente' not in st.session_state:
    st.session_state['cliente'] = None
if 'etapa' not in st.session_state:
    st.session_state['etapa'] = 'identificacao'

# --- TELA 1: IDENTIFICAÇÃO ---
if st.session_state['etapa'] == 'identificacao':
    st.title("🍔 Bem-vindo ao FIAP Burger")
    st.subheader("Para começar, digite seu CPF")

    cpf_input = st.text_input("CPF (apenas números)")
    
    col1, col2 = st.columns(2)
    
    if col1.button("Buscar Cadastro"):
        cliente = api.buscar_cliente(cpf_input)
        if cliente:
            st.session_state['cliente'] = cliente
            st.session_state['etapa'] = 'cardapio'
            st.rerun()
        else:
            st.error("Cliente não encontrado. Cadastre-se ao lado.")

    with col2:
        with st.expander("Não tem cadastro?"):
            nome = st.text_input("Nome")
            email = st.text_input("Email")
            if st.button("Cadastrar e Entrar"):
                novo_cliente = api.criar_cliente(nome, cpf_input, email)
                if novo_cliente:
                    st.success("Cadastrado com sucesso!")
                    # Busca novamente para garantir os dados completos
                    st.session_state['cliente'] = api.buscar_cliente(cpf_input)
                    st.session_state['etapa'] = 'cardapio'
                    st.rerun()

# --- TELA 2: CARDÁPIO ---
elif st.session_state['etapa'] == 'cardapio':
    st.sidebar.title(f"Olá, {st.session_state['cliente']['nome']}")
    if st.sidebar.button("Sair"):
        st.session_state.clear()
        st.rerun()

    st.title("📋 Cardápio")
    
    # Filtro de Categoria
    categoria = st.selectbox("Selecione a Categoria", ["Lanche", "Bebida", "Acompanhamento", "Sobremesa"])
    
    produtos = api.listar_produtos(categoria)
    
    if not produtos:
        st.info("Nenhum produto nesta categoria.")
    
    # Grid de Produtos
    cols = st.columns(3)
    for index, produto in enumerate(produtos):
        with cols[index % 3]:
            st.markdown(f"### {produto['nome']}")
            st.write(f"R$ {produto['preco']}")
            st.caption(produto.get('descricao', ''))
            
            if st.button(f"Adicionar {produto['nome']}", key=produto['id']):
                sucesso = api.adicionar_item(
                    client_id=st.session_state['cliente']['id'],
                    produto_id=produto['id']
                )
                if sucesso:
                    st.toast(f"{produto['nome']} adicionado à sacola!")
                else:
                    st.error("Erro ao adicionar item.")

    st.markdown("---")
    if st.button("🛒 Ver Sacola e Finalizar"):
        st.session_state['etapa'] = 'sacola'
        st.rerun()

# --- TELA 3: SACOLA E CHECKOUT ---
elif st.session_state['etapa'] == 'sacola':
    st.title("🛒 Sua Sacola")
    
    itens = api.ver_sacola(st.session_state['cliente']['id'])
    
    if not itens:
        st.warning("Sua sacola está vazia.")
        if st.button("Voltar ao Cardápio"):
            st.session_state['etapa'] = 'cardapio'
            st.rerun()
    else:
        total = 0
        for item in itens:
            # Assumindo estrutura do response da sacola
            nome_prod = item.get('produto', {}).get('nome', 'Item')
            preco = float(item.get('produto', {}).get('preco', 0))
            qtd = item.get('quantidade', 1)
            subtotal = preco * qtd
            total += subtotal
            
            st.write(f"**{nome_prod}** - {qtd}x R$ {preco} = R$ {subtotal:.2f}")
        
        st.markdown(f"### Total: R$ {total:.2f}")
        
        col1, col2 = st.columns(2)
        if col1.button("⬅️ Continuar Comprando"):
            st.session_state['etapa'] = 'cardapio'
            st.rerun()
            
        if col2.button("✅ Finalizar Pedido (Pagamento)"):
            resultado = api.checkout(st.session_state['cliente']['id'])
            if resultado:
                st.session_state['pedido_final'] = resultado
                st.session_state['etapa'] = 'pagamento'
                st.rerun()
            else:
                st.error("Erro ao processar checkout.")

# --- TELA 4: PAGAMENTO ---
elif st.session_state['etapa'] == 'pagamento':
    st.title("💳 Pagamento")
    st.success("Pedido gerado com sucesso!")
    
    dados_pedido = st.session_state.get('pedido_final', {})
    st.json(dados_pedido) # Exibe retorno do checkout (provável QR Code Mercado Pago)
    
    st.info("Escaneie o QR Code acima para pagar.")
    
    if st.button("Iniciar Novo Atendimento"):
        st.session_state.clear()
        st.rerun()