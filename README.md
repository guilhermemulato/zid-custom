# ZID Canvas Theme - pfSense CE 2.8.1

Tema dark premium para pfSense CE 2.8.1 com design moderno, tipografia customizada e componentes visuais avançados.

![Version](https://img.shields.io/badge/version-0.4-blue)
![pfSense](https://img.shields.io/badge/pfSense-2.8.1-orange)
![License](https://img.shields.io/badge/license-MIT-green)

## 🎨 Características

### Design System
- ✅ **Paleta dark premium** com cores modernas e contrastantes
- ✅ **Tipografia local**: Plus Jakarta Sans + JetBrains Mono
- ✅ **Grid background sutil** com efeito radial
- ✅ **Scrollbar customizada** integrada ao tema
- ✅ **Componentes completos**: panels, tabelas, botões, formulários, alerts, modals
- ✅ **Responsivo**: mobile, tablet e desktop

### Widget GeoBlocked Map 🗺️
- ✅ **Mapa interativo** usando Leaflet.js
- ✅ **Tema dark** integrado com zid-canvas
- ✅ **Marcadores animados** com efeito pulse
- ✅ **Estatísticas em tempo real**: total de bloqueios, países bloqueados
- ✅ **Top 5 países** com mais bloqueios
- ✅ **Atualização automática** a cada 30 segundos via AJAX
- ✅ **Popups informativos** ao clicar nos marcadores

## 📦 Instalação

### Pré-requisitos
- pfSense CE 2.8.1
- Acesso SSH ao firewall
- Permissões de root

### Opção 1: Instalação Local

```bash
# 1. Copiar bundle para o pfSense
scp zid-cavas-latest.tar.gz root@<IP-PFSENSE>:/tmp/

# 2. SSH no pfSense
ssh root@<IP-PFSENSE>

# 3. Extrair e instalar
cd /tmp
tar -xzf zid-cavas-latest.tar.gz
cd zid-ui
sh setup.sh
```

### Opção 2: Atualização Remota (S3)

```bash
# SSH no pfSense
ssh root@<IP-PFSENSE>

# Executar script de atualização
sh /conf/zid-ui/update.sh
```

### Ativar o Tema

1. Acesse **System → General Setup**
2. Em **Theme**, selecione **`zid-canvas`**
3. Clique em **Save**
4. Recarregue a página (F5)

### Instalar Widget GeoBlocked Map

1. Copiar widget para o diretório correto:
```bash
cp /conf/zid-ui/widgets/geomap.widget.php /usr/local/www/widgets/widgets/
chmod 644 /usr/local/www/widgets/widgets/geomap.widget.php
```

2. No Dashboard do pfSense:
   - Clique em **"Available Widgets"**
   - Marque **"GeoBlocked Map"**
   - Clique em **"Save Settings"**
   - Arraste o widget para posicioná-lo

## 📁 Estrutura do Projeto

```
zid-custom/
├── css/
│   └── zid-canvas.css              # CSS principal (890+ linhas)
├── assets/
│   ├── fonts/                      # Fontes locais
│   │   ├── PlusJakartaSans-*.ttf
│   │   └── JetBrainsMono-*.ttf
│   ├── zid-mark.svg
│   └── logo.svg
├── widgets/
│   ├── geomap.widget.php           # Widget de mapa GeoBlocked
│   └── README.md                   # Documentação do widget
├── data/
│   └── geoblocks-example.json      # Dados de exemplo
├── apply.sh                        # Aplica tema
├── setup.sh                        # Instalação inicial
├── update.sh                       # Atualização remota
├── specs.md                        # Especificações v0.4
├── CHANGELOG.md                    # Histórico de mudanças
├── VERSION                         # 0.4
└── zid-cavas-latest.tar.gz        # Bundle (209 KB)
```

## 🎯 Componentes Estilizados

### Interface Padrão
- ✅ Navbar/Header com dropdown menus
- ✅ Panels/Cards com variantes (primary, success, warning, danger, info)
- ✅ Tabelas responsivas com hover states
- ✅ Botões (default, primary, success, warning, danger, info)
- ✅ Formulários (inputs, selects, textarea, labels, help text)
- ✅ Alerts com border left colorida
- ✅ Badges e Labels
- ✅ Breadcrumbs
- ✅ Progress bars
- ✅ Modals
- ✅ Tooltips e Popovers
- ✅ Código e Pre tags

### Widget GeoBlocked Map
- ✅ Mapa Leaflet.js com tema dark
- ✅ Marcadores customizados com animação
- ✅ Popups com informações de país
- ✅ Estatísticas em tempo real
- ✅ Tabela de Top 5 países bloqueados
- ✅ Atualização via AJAX

## 🎨 Paleta de Cores

```css
/* Backgrounds */
--zid-bg-main: #030712;
--zid-bg-card: #0f172a;
--zid-bg-secondary: #1e293b;
--zid-bg-hover: #334155;

/* Cores da Marca */
--zid-primary: #b34849;
--zid-secondary: #b0c3bc;

/* Status */
--zid-success: #10b981;
--zid-warning: #f59e0b;
--zid-danger: #ef4444;
--zid-info: #3b82f6;
```

## 🔧 Scripts de Manutenção

### apply.sh
Copia arquivos de `/conf/zid-ui` para `/usr/local/www`.

```bash
sh /conf/zid-ui/apply.sh
```

### setup.sh
Instalação inicial do bundle local.

```bash
sh setup.sh [/caminho/do/bundle]
```

### update.sh
Atualização remota do S3.

```bash
sh /conf/zid-ui/update.sh
```

## 📊 Widget GeoBlocked Map - Integração

### Formato de Dados

O widget espera dados no formato JSON:

```json
{
  "success": true,
  "data": [
    {
      "country": "China",
      "code": "CN",
      "lat": 35.8617,
      "lon": 104.1954,
      "blocks": 1523
    }
  ],
  "total_blocks": 1523,
  "countries_blocked": 1,
  "last_update": "2026-01-26 15:30:00"
}
```

### Integração com pfBlockerNG

Edite `geomap.widget.php` na função `get_geoblocked_data()`:

```php
function get_geoblocked_data() {
    // Ler logs do pfBlockerNG
    if (file_exists('/var/log/pfblockerng/geoip.log')) {
        // Parse do log
        // ... seu código aqui ...
    }

    return $blocked_countries;
}
```

Veja [widgets/README.md](widgets/README.md) para mais exemplos de integração.

## 🐛 Troubleshooting

### Tema não aparece
```bash
# Verificar se o CSS foi copiado
ls -la /usr/local/www/css/zid-canvas.css

# Re-aplicar o tema
sh /conf/zid-ui/apply.sh
```

### Fontes não carregam
```bash
# Verificar assets
ls -la /usr/local/www/zid-assets/fonts/

# Recopiar assets
cp -R /conf/zid-ui/assets/* /usr/local/www/zid-assets/
```

### Widget não aparece
```bash
# Verificar permissões
chmod 644 /usr/local/www/widgets/widgets/geomap.widget.php
chown root:wheel /usr/local/www/widgets/widgets/geomap.widget.php

# Limpar cache do navegador
Ctrl + F5
```

### Mapa não carrega
1. Verificar conexão com CDN do Leaflet
2. Desabilitar bloqueadores de conteúdo
3. Verificar console do navegador (F12)

## 📝 Changelog

### v0.4 - 2026-01-26
- ✨ Adiciona widget GeoBlocked Map com Leaflet.js
- ✨ Marcadores animados e popups informativos
- ✨ Estatísticas em tempo real via AJAX
- ✨ Top 5 países bloqueados
- 🎨 Estilos customizados para Leaflet
- 📄 Documentação completa do widget

### v0.3 - 2026-01-26
- ♻️ Reescrita completa do CSS
- ✅ Compatibilidade total com pfSense CE 2.8.1
- 🎨 Componentes completos (panels, tabelas, botões, etc.)
- 📱 Responsividade funcional

### v0.2 - 2026-01-26
- 🚧 Tentativa de sidebar fixa (removida na v0.3)

### v0.1 - 2026-01-26
- 🎉 Versão inicial com tokens e tipografia local

## 🔗 Recursos

- [Leaflet.js](https://leafletjs.com/) - Biblioteca de mapas
- [pfSense Documentation](https://docs.netgate.com/pfsense/en/latest/)
- [pfBlockerNG](https://docs.netgate.com/pfsense/en/latest/packages/pfblocker.html)

## 📄 Licença

MIT License - Livre para uso pessoal e comercial.

## 🤝 Contribuição

Contribuições são bem-vindas! Para melhorias e sugestões:
1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📧 Suporte

Para questões e suporte, abra uma issue no repositório.

---

**Desenvolvido com ❤️ para a comunidade pfSense**
