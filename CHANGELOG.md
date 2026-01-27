# Changelog

## 0.4.6 - 2026-01-26
- CORREÇÃO CRÍTICA: Corrige botão "+" (Available Widgets) que não funcionava.
- Torna seletor CSS `.collapse` mais específico para não afetar painéis do Dashboard.
- Painel de widgets disponíveis agora abre/fecha corretamente.
- CSS agora afeta apenas collapse no sidebar/nav, não em toda a página.

## 0.4.5 - 2026-01-26
- CORREÇÃO CRÍTICA: Adiciona min-height de 600px nas colunas do Dashboard.
- Corrige problema de drag-and-drop entre colunas.
- Widgets agora podem ser arrastados entre coluna 1 e coluna 2 sem problemas.
- Resolve issue onde colunas vazias ou com poucos widgets não tinham área suficiente para drop.

## 0.4.4 - 2026-01-26
- Melhora destaque visual do botão "Salvar" ordem dos widgets no Dashboard.
- Adiciona animação pulse e indicador vermelho no botão quando há alterações não salvas.
- Botão de salvar agora aparece em verde vibrante (#10b981) com efeito glow.
- Adiciona tooltip visual (badge vermelho) para indicar mudanças pendentes.
- Melhora UX: usuário agora vê claramente quando precisa salvar as mudanças de posição dos widgets.

## 0.4.3 - 2026-01-26
- Corrige layout dos widgets do Dashboard para respeitar configuração de colunas.
- Adiciona sistema de grid Bootstrap para organização dos widgets.
- Implementa suporte para 2, 3 e 4 colunas de widgets conforme configuração.
- Adiciona responsividade mobile (100% largura em telas pequenas).
- Corrige float e clearfix para alinhamento correto dos widgets.

## 0.4.2 - 2026-01-26
- Limita altura do painel de Notices (alertas PHP) com max-height: 200px.
- Adiciona scroll automático para alertas longos.
- Compacta exibição de stack traces e erros PHP.
- Melhora formatação de código dentro de alertas.
- Adiciona botão collapse para painel de notices.
- Reduz tamanho de fonte em alertas para melhor densidade.

## 0.4.1 - 2026-01-26
- Corrige menu lateral expandido por padrão.
- Adiciona collapse/expand automático nos submenus.
- Melhora espaçamento e compactação do menu lateral.
- Adiciona ícones de expand/collapse nos itens do menu.
- Otimiza visualização do sidebar para melhor usabilidade.

## 0.4 - 2026-01-26
- Adiciona widget GeoBlocked Map para visualização de bloqueios geográficos.
- Integra Leaflet.js para mapas interativos com tema dark.
- Implementa marcadores animados com efeito pulse nos países bloqueados.
- Adiciona estatísticas em tempo real: total de bloqueios e países bloqueados.
- Cria Top 5 ranking de países com mais bloqueios.
- Atualização automática a cada 30 segundos via AJAX.
- Adiciona estilos customizados para Leaflet no CSS principal.
- Inclui arquivo JSON de exemplo com dados de bloqueio.
- Documentação completa de instalação e personalização do widget.

## 0.3 - 2026-01-26
- Reescrita completa do CSS para compatibilidade total com pfSense CE 2.8.1.
- Remove modificações estruturais (sidebar fixa, margins) que quebravam o layout.
- Implementa apenas estilização visual (cores, tipografia, componentes).
- Adiciona suporte completo a: panels, tabelas, botões, formulários, alerts, modals.
- Melhora scrollbar customizada e grid background sutil.
- Mantém fontes locais (Plus Jakarta Sans + JetBrains Mono).
- Corrige responsividade para mobile, tablet e desktop.

## 0.2 - 2026-01-26
- Reestrutura a navegacao para sidebar fixa, aproximando o template.

## 0.1.9 - 2026-01-26
- Ajusta cores do menu (navlnk/dropdown) e links dos widgets.

## 0.1.8 - 2026-01-26
- Ajusta tipografia e contraste do menu lateral e headers de widgets.
- Refina tabelas e listagens para melhor legibilidade.

## 0.1.7 - 2026-01-26
- Remove logo SVG (svg#logo) na navbar para eliminar watermark remanescente.

## 0.1.6 - 2026-01-26
- Reforca bloqueio do watermark pfSense em mais containers e pseudo-elementos.
- Update.sh agora informa status do download e aplicacao.

## 0.1.5 - 2026-01-26
- Forca remocao total do watermark/logo e grid de fundo.
- Update.sh agora informa status do download e aplicacao.

## 0.1.4 - 2026-01-26
- Remove o watermark/logo de fundo do pfSense para layout limpo.

## 0.1.3 - 2026-01-26
- Ajusta bundle para sempre gerar pasta raiz (zid-ui/) na descompactacao.

## 0.1.2 - 2026-01-26
- Adiciona setup.sh para instalacao rapida no pfSense.

## 0.1.1 - 2026-01-26
- Implementa o tema zid-canvas com tokens, tipografia local e grid de fundo.
- Adiciona assets locais (svg e fontes) sem dependencia externa.
- Cria scripts apply.sh e update.sh para persistencia e update.

## 0.1 - 2026-01-26
- Atualiza o plano final alinhado ao template de referencia.
- Registra estrutura de entregaveis, bundle e scripts.
- Define versao inicial e requisitos de design.
