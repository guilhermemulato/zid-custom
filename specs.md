# zid-custom - specs

## Visao geral
- Objetivo: customizacao visual completa (dark premium) do pfSense CE 2.8.1.
- Tema: `zid-canvas`.
- Restricoes: sem CDN externa, sem alterar PHP core, sem JS externo.

## Template de referencia
- URL: https://firewall-canvas--guilhermemulato.replit.app
- Direcao visual: glass panels, grid background sutil, tipografia moderna, acento vermelho, paines elevados.

## Entregaveis esperados
- CSS: `/usr/local/www/css/zid-canvas.css`
- Assets: `/usr/local/www/zid-assets/` (inclui `zid-mark.svg` e fontes locais)
- Persistencia: `/conf/zid-ui/css` e `/conf/zid-ui/assets`
- Scripts: `/conf/zid-ui/apply.sh` e `/conf/zid-ui/update.sh`

## Versao
- Versao atual: `0.4.6`

## Status atual (2026-01-26)
- **Widget GeoBlocked Map** implementado com Leaflet.js para visualização de bloqueios geográficos.
- Mapa interativo com tema dark integrado ao zid-canvas.
- Marcadores animados, popups informativos e atualização em tempo real via AJAX.
- CSS completamente reescrito para compatibilidade total com pfSense CE 2.8.1.
- Estilização visual aplicada sem quebrar a estrutura HTML nativa do pfSense.
- Suporte completo a componentes: panels, tabelas, botões, formulários, alerts, modals, badges.
- Tipografia local (Plus Jakarta Sans + JetBrains Mono) carregando corretamente.
- Grid background sutil, scrollbar customizada e paleta de cores dark premium implementadas.
- Watermark/logo do pfSense removido.
- Responsividade funcional para mobile, tablet e desktop.

## Scripts (setup/update/apply)
- `apply.sh`: copia `zid-canvas.css` e assets de `/conf/zid-ui` para `/usr/local/www` (idempotente).
- `setup.sh`: instala um bundle local em `/conf/zid-ui` e aplica o tema.
  - Uso: `sh setup.sh` (ou `sh setup.sh /caminho/do/bundle`).
- `update.sh`: baixa o bundle `zid-cavas-latest.tar.gz` do S3, extrai, copia para `/conf/zid-ui` e executa `apply.sh`.

## Bundle
- Gerar `zid-cavas-latest.tar.gz` ao final de cada entrega com os arquivos necessarios.
- O bundle deve extrair dentro de uma pasta raiz `zid-ui/` para evitar arquivos soltos.
