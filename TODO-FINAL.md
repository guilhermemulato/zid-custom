Fase 0 — Base, versao e persistencia

- Objetivo: preparar estrutura persistente e versionamento inicial.
- Acoes:
  - Criar `/conf/zid-ui/css` e `/conf/zid-ui/assets`.
  - Criar `CHANGELOG.md` e `VERSION` (versao inicial: 0.1).
  - Garantir que `specs.md` esteja atualizado com o link do template.
- Comandos:

  mkdir -p /conf/zid-ui/css /conf/zid-ui/assets

- Saida esperada: diretorios persistentes e controle de versao prontos.

Fase 1 — Direcao visual (alinhada ao template)

- Objetivo: alinhar o tema ao visual do template informado.
- Guideline do template:
  - Fundo muito escuro (azul grafite), grid sutil e glow leve.
  - Painels glassmorphism (blur + borda discreta).
  - Acento vermelho forte e secundario esverdeado para status.
  - Tipografia moderna sans + mono para metadados.
  - Layout com cards elevados, gaps generosos e linhas finas.
- Resultado esperado: mapa de tokens e componentes a estilizar.

Fase 2 — Tokens e base CSS

- Objetivo: definir tokens e base global do CSS no `zid-canvas.css`.
- Acoes:
  - Criar variaveis de cor (bg, panel, border, primary, accent, muted, success, warning, danger).
  - Definir tipografia local (ex.: Plus Jakarta Sans + JetBrains Mono hospedadas localmente em `/conf/zid-ui/assets/fonts`).
  - Configurar fundos com gradiente + grid sutil (sem imagens externas).
  - Setar radius, sombras e blur padronizados.
- Resultado esperado: `:root` completo e reset base coerente.

Fase 3 — Componentes obrigatorios

- Objetivo: aplicar o design aos componentes-chave do pfSense.
- Itens obrigatorios:
  - Navbar superior: gradiente escuro, borda fina e hover vermelho.
  - Menu lateral: itens com hover, ativo destacado e iconografia alinhada.
  - Dashboard/widgets: cards com glass panel e sombra suave.
  - Panels/boxes/modals: borda suave + blur opcional.
  - Tabelas: head em caps, linhas alternadas e borda sutil.
  - Formularios: inputs escuros, foco com glow vermelho.
  - Botoes: primary com gradiente vermelho, secondary neutro.
  - Alerts: cores status (success/warn/danger/info) com fundo translucido.
  - Login: central, elegante, com painel glass e logo.

Fase 4 — Assets e identidade

- Objetivo: garantir assets locais e identidade visual.
- Acoes:
  - Criar `zid-mark.svg` (vermelho, simples) em `/conf/zid-ui/assets`.
  - Reservar `logo.svg` (placeholder).
  - Incluir fontes locais e declarar `@font-face` no CSS.
- Resultado esperado: assets locais prontos, sem CDN externa.

Fase 5 — Scripts de persistencia e update

- Objetivo: garantir re-aplicacao e update automatico via S3.
- Acoes:
  - `apply.sh`: copiar CSS e assets para `/usr/local/www`, criando pastas se necessario.
  - `update.sh`: baixar `zid-cavas-latest.tar.gz`, extrair e aplicar.
  - Garantir idempotencia dos scripts.

Fase 6 — Bundle e distribuicao

- Objetivo: gerar bundle final com todos os arquivos necessarios.
- Acoes:
  - Montar estrutura:
    - `css/zid-canvas.css`
    - `assets/` (svg, fonts)
    - `apply.sh`
    - `update.sh`
    - `CHANGELOG.md`
    - `VERSION`
  - Gerar `zid-cavas-latest.tar.gz`.
- Comando sugerido:

  tar -czf zid-cavas-latest.tar.gz css assets apply.sh update.sh CHANGELOG.md VERSION

Fase 7 — Validacao visual e funcional

- Objetivo: validar aplicacao e persistencia no pfSense.
- Passos:
  - System -> General Setup -> Theme: selecionar `zid-canvas`.
  - Validar telas: Dashboard, Status, Firewall Rules, Forms, Alerts, Login.
  - Executar `sh /conf/zid-ui/apply.sh` e reboot para persistencia.
  - Executar `sh /conf/zid-ui/update.sh` para validar update.

Fase 8 — Changelog e versao

- Objetivo: registrar entrega e bump de versao.
- Acoes:
  - Atualizar `CHANGELOG.md` com esta entrega.
  - Versao atual: 0.1 (primeira entrega).
