### 1. APIs e Regras de Negócio

Esta seção descreve as APIs desenvolvidas e as regras de negócio associadas, conforme os requisitos do projeto.

#### API de Checkout
O processo de checkout é iniciado no endpoint da `Sacola`. Ao processar o checkout, o sistema cria um pedido e gera as informações de pagamento (via PIX) para o cliente.

#### Status do Pedido
Para verificar o status do pagamento, a consulta é realizada através do endpoint de `status do pedido`, que deve ser acessado por `polling`. Optamos por esta abordagem em vez de um `webhook` tradicional para evitar a exposição de um endpoint diretamente à internet, o que pode reduzir a superfície de ataque a scans de vulnerabilidade. Estamos cientes de que o `polling` possui um custo computacional mais elevado, mas consideramos esta uma decisão de segurança estratégica para o cenário do negócio.

#### Atualização de Status do Pedido
A API para atualização de status de pedido já existe e está pronta para uso. O serviço de pedido implementa regras de negócio robustas: a atualização do status somente é permitida se o pagamento do pedido tiver sido aprovado. Esta regra garante que a cozinha só inicie a preparação do pedido após a confirmação do pagamento, evitando perdas e garantindo a integridade do fluxo de trabalho.