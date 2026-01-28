# Changelog

## 0.3.12 - 2026-01-28
- Implementa Firewall Rules com listagem, toggle, copy e reorder.
- Adiciona formulario de nova regra e layout inspirado no template.
- Atualiza logo da sidebar com badge ZID.

## 0.3.11 - 2026-01-28
- Adiciona mapeamentos estaticos de DHCP (MAC, IP, descricao).

## 0.3.10 - 2026-01-28
- Ajusta reload do nginx para garantir aplicacao do include (onereload + HUP).

## 0.3.9 - 2026-01-28
- Adiciona configuracao de gateway, DNS e leases no DHCP.
- Preenche configuracoes atuais do DHCP no formulario.

## 0.3.8 - 2026-01-27
- Corrige CSRF usando cookie de fallback para manter token entre requests.

## 0.3.7 - 2026-01-27
- Corrige admin-only para usuario admin local.
- Garante envio de cookies nas chamadas fetch para CSRF.

## 0.3.6 - 2026-01-27
- Corrige layout do login quando sidebar esta oculta.

## 0.3.5 - 2026-01-27
- Ajusta card de login para ficar mais largo e centralizado.

## 0.3.4 - 2026-01-27
- Corrige verificacao de admin usando privs reais do usuario.
- Remove referencia de marca original na UI de Servicos e topbar.

## 0.3.3 - 2026-01-27
- Ajusta pagina de Servicos para nao expor marca original.
- Corrige resposta admin-only em APIs.
- Lista interfaces reais no configurador de DHCP.

## 0.3.2 - 2026-01-27
- Adiciona configuracao de pool DHCP na pagina de Servicos.
- Ajusta status do DHCP para refletir processo real.

## 0.3.1 - 2026-01-27
- Adiciona controle de servico (start/stop/restart) para DHCP via API segura.
- Atualiza install.sh com sudoers para service.sh.
- Atualiza specs com regra de nao expor pfSense.

## 0.3 - 2026-01-27
- Adiciona sidebar com navegacao principal.
- Cria pagina de Servicos com status do DHCP via API.

## 0.2.9 - 2026-01-27
- Adiciona script de limpeza de bundles temporarios e padrao no specs.

## 0.2.8 - 2026-01-27
- Simplifica instalacao: executar install.sh dentro do bundle extraido.
- Atualiza README com fluxo de instalacao unico.

## 0.2.7 - 2026-01-27
- Adiciona script unico de instalacao automatizada (install-zid-ui.sh).

## 0.2.6 - 2026-01-27
- Atualiza README com instalacao, update, debug e checklist.

## 0.2.5 - 2026-01-27
- Implementa SQLite para auditoria e preferencias da UI.

## 0.2.4 - 2026-01-27
- Corrige bootstrap de auth para evitar tela de login do pfSense.
- Ajusta verificacao de sessao para usar session_auth sem authgui.

## 0.2.3 - 2026-01-27
- Cria tela de login customizada da ZID UI e rota de logout.
- Mantem autenticacao do pfSense sem expor a interface original.

## 0.2.2 - 2026-01-27
- Ajusta exibicao de CPU/Memoria no dashboard.
- Adiciona botao de logout na topbar.

## 0.2.1 - 2026-01-27
- Finaliza fase 6 com install/update e integrações de segurança.
- Ajusta specs com fluxo de update admin + CSRF.

## 0.2 - 2026-01-27
- Adiciona fluxo de update seguro (install.sh, update.sh, sudoers e endpoint admin).
- Implementa botao Update no dashboard e fluxo de execucao via API.

## 0.1.9 - 2026-01-27
- Adiciona Leaflet local e mapa funcional com polling de eventos.
- Exibe overlay de modo offline quando tiles falham.

## 0.1.8 - 2026-01-27
- Corrige endpoints API para carregar bootstrap antes das validacoes de auth.

## 0.1.7 - 2026-01-27
- Adiciona log de erros PHP dedicado para diagnostico dos endpoints da ZID UI.

## 0.1.6 - 2026-01-27
- Monta dashboard com KPIs, grid de widgets e placeholder do mapa.
- Adiciona polling por widget e endpoints base para log tail e eventos do mapa.

## 0.1.5 - 2026-01-27
- Adiciona core PHP para métricas/serviços/geo e endpoints API base (health/metrics).

## 0.1.4 - 2026-01-27
- Corrige roteamento para permitir acesso via /index.php na ZID UI.

## 0.1.3 - 2026-01-27
- Corrige include do fastcgi_params e ajuste do listen http2 no server block do nginx.

## 0.1.2 - 2026-01-27
- Adiciona mecanismo de include do nginx com geracao automatica do server block (porta 8444).
- Cria scripts base de boot (rc) e helper para garantir include idempotente.
- Atualiza specs com detalhes do bundle.

## 0.1.1 - 2026-01-27
- Adiciona estrutura inicial da ZID UI (bootstrap, router, layout e paginas base).
- Inclui CSS base com tokens visuais e assets iniciais.
- Adiciona script auxiliar para descobrir `fastcgi_pass` no pfSense.
- Documenta ajustes iniciais da nova UI em specs.
