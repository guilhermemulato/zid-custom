Você é um engenheiro especialista em pfSense CE 2.8.1 (FreeBSD),
com profundo conhecimento do webConfigurator (HTML/PHP/CSS),
estrutura de temas e persistência após upgrades.

OBJETIVO
--------
Criar uma customização visual COMPLETA (Dark Premium)
para o pfSense 2.8.1, removendo a aparência padrão
e aplicando uma identidade visual moderna estilo
"console cloud / NGFW appliance".

O visual deve ser inspirado neste template:
Pode

ESCOPO PERMITIDO (IMPORTANTE)
-----------------------------
- NÃO criar fork do pfSense
- NÃO alterar lógica de firewall
- NÃO quebrar compatibilidade com upgrades
- Customização deve ser feita via:
  - Tema CSS custom
  - Assets próprios (SVG/PNG)
  - Script de reaplicação no boot

VERSÃO
------
pfSense CE 2.8.1

REQUISITOS TÉCNICOS
-------------------
1. Criar um novo tema chamado:
   zid-canvas

2. O tema deve ser implementado como:
   /usr/local/www/css/zid-canvas.css

3. O CSS deve:
   - Aplicar Dark Mode premium
   - Usar cards com bordas arredondadas
   - Aplicar sombras suaves
   - Usar blur (backdrop-filter) quando possível
   - Substituir cores claras por tons escuros
   - Melhorar tipografia (system-ui)
   - Remover aparência “legada” do pfSense

4. Estilizar obrigatoriamente:
   - Navbar superior
   - Menu lateral
   - Dashboard (widgets)
   - Panels / Cards
   - Tabelas
   - Formulários
   - Botões
   - Alerts
   - Página de login

5. Criar pasta de assets própria:
   /usr/local/www/zid-assets/

6. Criar pelo menos:
   - zid-mark.svg (ícone simples vermelho)
   - Preparar estrutura para logo ZID futura

7. O tema NÃO deve depender de CDN externa.

8. Ter um script de auto-update
    - Ao rodar o script, ele automaticamente baixa o arquivo do meu S3: https://s3.soulsolucoes.com.br/soul/portal/zid-cavas-latest.tar.gz, e desconpacta e faz a atualizacao dos arquivos, e criacao ou delecao de outros, caso aplicaveis. Com isso, nao precisamos ficar todo hora transferindo arquivos direto para o S3, apenas rodamos o update

PERSISTÊNCIA (MUITO IMPORTANTE)
-------------------------------
Atualizações do pfSense podem sobrescrever /usr/local/www,
portanto implemente persistência da seguinte forma:

1. Criar diretório persistente:
   /conf/zid-ui/css
   /conf/zid-ui/assets

2. Copiar o tema e assets para esses diretórios

3. Criar script:
   /conf/zid-ui/apply.sh

   Esse script deve:
   - Copiar zid-canvas.css para /usr/local/www/css/
   - Copiar assets para /usr/local/www/zid-assets/
   - Criar pastas se não existirem
   - Ser idempotente (pode rodar várias vezes)

4. O script deve ser compatível com execução via:
   - pacote Shellcmd
   - execução no boot

ENTREGÁVEIS
-----------
Você deve gerar:

1. Conteúdo completo do arquivo:
   /usr/local/www/css/zid-canvas.css

2. Conteúdo SVG de exemplo:
   /usr/local/www/zid-assets/zid-mark.svg

3. Conteúdo do script:
   /conf/zid-ui/apply.sh

4. Comandos shell necessários para:
   - Criar pastas
   - Copiar arquivos
   - Tornar script executável

ESTILO VISUAL
-------------
- Dark premium
- Fundo escuro com gradientes sutis
- Vermelho como cor primária (ZID)
- Aparência de produto NGFW moderno
- Não parecer pfSense “stock”

RESTRIÇÕES
----------
- Não usar bibliotecas JS externas
- Não alterar arquivos PHP core
- Não usar hacks frágeis
- Não usar paths inexistentes no pfSense

VALIDAÇÃO FINAL
---------------
Antes de finalizar, valide mentalmente:
- O tema aparece em System → General Setup → Theme
- O pfSense continua funcional
- O visual muda globalmente
- O tema reaplica após reboot

OUTPUT
------
Forneça apenas:
- Arquivos
- Scripts
- Comandos
Sem explicações longas ou texto extra.
