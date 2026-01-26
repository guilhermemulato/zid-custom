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
- Versao atual: `0.2`

## Bundle
- Gerar `zid-cavas-latest.tar.gz` ao final de cada entrega com os arquivos necessarios.
- O bundle deve extrair dentro de uma pasta raiz `zid-ui/` para evitar arquivos soltos.
