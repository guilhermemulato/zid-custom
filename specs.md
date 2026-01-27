# zid-custom - specs

## Visao geral
- Objetivo: customizacao visual completa (dark premium) do pfSense CE 2.8.1.
- Tema: `zid-canvas`.
- Restricoes: sem CDN externa, sem alterar PHP core, sem JS externo.
- Nova UI: `ZID UI` em porta fixa 8444, reaproveitando nginx/php-fpm e auth do pfSense.
- Atualizacao de widgets: MVP via polling (SSE fica fora do escopo inicial).
- Update seguro: endpoint admin + CSRF chama `/usr/local/etc/zid-ui/update.sh` via sudoers restrito.
- Sidebar: menu principal com Servicos, Firewall e VPN.
- Nunca exibir em nenhuma tela da ZID UI que existe pfSense por tras (branding ou nomenclatura).

## Template de referencia
- URL: https://firewall-canvas--guilhermemulato.replit.app
- Direcao visual: glass panels, grid background sutil, tipografia moderna, acento vermelho, paines elevados.

## Acessos para testes
- URL pfSense (web): `https://172.25.200.53:10443`
  - Usuario: `admin`
  - Senha: `pfsense`
- SSH pfSense:
  - IP: `172.25.200.53`
  - Porta: `22`
  - Usuario: `root`
  - Senha: `pfsense`

## Bundle
- Gerar `zid-cavas-latest.tar.gz` ao final de cada entrega com os arquivos necessarios.
- O bundle deve extrair dentro de uma pasta raiz `zid-ui/` para evitar arquivos soltos.
- Incluir tambem scripts em `etc/` e `sbin/` quando existirem.
- Limpar pastas temporarias `bundle_zid_ui_*` ao final de cada implementacao (use `./clean-bundles.sh`).
