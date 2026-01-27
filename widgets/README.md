# Widget GeoBlocked Map - pfSense CE 2.8.1

Widget de mapa interativo para visualização de bloqueios geográficos no Dashboard do pfSense.

## 🗺️ Características

- **Mapa interativo** usando Leaflet.js
- **Tema dark** integrado com zid-canvas
- **Marcadores animados** com efeito pulse nos países bloqueados
- **Atualização automática** a cada 30 segundos
- **Estatísticas em tempo real**: total de bloqueios, países bloqueados
- **Top 5 países** com mais bloqueios
- **Popups informativos** ao clicar nos marcadores
- **Responsivo** e otimizado para diferentes resoluções

## 📦 Instalação

### Opção 1: Instalação Manual

```bash
# SSH no pfSense
ssh root@<IP-PFSENSE>

# Copiar o widget
scp geomap.widget.php root@<IP-PFSENSE>:/usr/local/www/widgets/widgets/

# Ajustar permissões
chmod 644 /usr/local/www/widgets/widgets/geomap.widget.php
chown root:wheel /usr/local/www/widgets/widgets/geomap.widget.php
```

### Opção 2: Via Bundle

O widget será incluído automaticamente no próximo bundle `zid-cavas-latest.tar.gz`.

## 🔧 Configuração

### 1. Adicionar ao Dashboard

1. Acesse o pfSense Dashboard (página inicial)
2. Clique em **"Available Widgets"** (canto superior direito)
3. Marque a opção **"GeoBlocked Map"**
4. Clique em **"Save Settings"**
5. O widget aparecerá no dashboard
6. Arraste para posicionar onde desejar

### 2. Integração com pfBlockerNG (Opcional)

Para exibir dados reais de bloqueios, edite o arquivo `geomap.widget.php` na função `get_geoblocked_data()`:

```php
function get_geoblocked_data() {
    $blocked_countries = array();

    // Exemplo de integração com pfBlockerNG
    if (file_exists('/var/log/pfblockerng/geoip.log')) {
        $log_content = file_get_contents('/var/log/pfblockerng/geoip.log');
        // Parse do log e agregação por país
        // ... seu código de parsing aqui ...
    }

    return $blocked_countries;
}
```

### 3. Integração com Firewall Logs

Para ler logs do firewall:

```php
function get_geoblocked_data() {
    $blocked_countries = array();

    // Ler logs do firewall
    exec("pfctl -ss | grep block", $output);

    // Parse dos IPs e geolocalização
    // Usar biblioteca GeoIP ou API de geolocalização

    return $blocked_countries;
}
```

## 🎨 Personalização

### Alterar Cores

Edite o bloco `<style>` no arquivo do widget:

```css
/* Cor dos marcadores */
.block-marker div {
    background: rgba(239, 68, 68, 0.8); /* Vermelho padrão */
}

/* Cor do popup */
.leaflet-popup-content-wrapper {
    background: #1e293b; /* Dark background */
}
```

### Alterar Tema do Mapa

No código JavaScript, altere o `tileLayer`:

```javascript
// Tema Dark (padrão)
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '...',
}).addTo(map);

// Outros temas disponíveis:
// Light: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'
// Voyager: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png'
```

### Alterar Intervalo de Atualização

```javascript
// Atualizar a cada 30 segundos (padrão)
setInterval(updateGeomapData, 30000);

// Alterar para 60 segundos
setInterval(updateGeomapData, 60000);

// Alterar para 10 segundos
setInterval(updateGeomapData, 10000);
```

## 📊 Formato de Dados

O widget espera dados no seguinte formato JSON:

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
        },
        {
            "country": "Russia",
            "code": "RU",
            "lat": 61.5240,
            "lon": 105.3188,
            "blocks": 892
        }
    ],
    "total_blocks": 2415,
    "countries_blocked": 2,
    "last_update": "2026-01-26 15:30:00"
}
```

## 🔍 Exemplos de Integração

### Exemplo 1: Ler de Arquivo JSON

```php
function get_geoblocked_data() {
    $json_file = '/conf/zid-ui/data/geoblocks.json';

    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        $data = json_decode($json_data, true);
        return $data['countries'] ?? array();
    }

    return array();
}
```

### Exemplo 2: Consultar Banco de Dados

```php
function get_geoblocked_data() {
    require_once("config.inc");

    $countries = array();

    // Conectar ao SQLite (se disponível)
    $db = new SQLite3('/var/db/pfblockerng.db');
    $results = $db->query('SELECT country_code, COUNT(*) as blocks FROM blocked_ips GROUP BY country_code');

    while ($row = $results->fetchArray()) {
        $countries[] = array(
            'code' => $row['country_code'],
            'blocks' => $row['blocks'],
            // ... adicionar lat/lon de uma tabela de referência
        );
    }

    return $countries;
}
```

### Exemplo 3: API Externa de Geolocalização

```php
function get_geoblocked_data() {
    // Ler IPs bloqueados dos logs
    exec("pfctl -t blockedips -T show", $blocked_ips);

    $countries = array();
    $geoip_data = array();

    // Agrupar por país (você precisaria de uma API ou biblioteca GeoIP)
    foreach ($blocked_ips as $ip) {
        $country_code = geoip_country_code($ip); // Função personalizada
        if (!isset($geoip_data[$country_code])) {
            $geoip_data[$country_code] = 0;
        }
        $geoip_data[$country_code]++;
    }

    // Converter para formato do widget
    foreach ($geoip_data as $code => $blocks) {
        $countries[] = array(
            'country' => geoip_country_name($code),
            'code' => $code,
            'lat' => geoip_latitude($code),
            'lon' => geoip_longitude($code),
            'blocks' => $blocks
        );
    }

    return $countries;
}
```

## 🐛 Troubleshooting

### Widget não aparece no Dashboard

1. Verifique permissões do arquivo:
   ```bash
   ls -la /usr/local/www/widgets/widgets/geomap.widget.php
   # Deve ser: -rw-r--r-- root:wheel
   ```

2. Limpe cache do navegador (Ctrl + F5)

3. Verifique logs do PHP:
   ```bash
   tail -f /var/log/nginx/error.log
   ```

### Mapa não carrega

1. Verifique conexão com CDN do Leaflet:
   ```bash
   fetch https://unpkg.com/leaflet@1.9.4/dist/leaflet.js
   ```

2. Desabilite bloqueadores de conteúdo no navegador

3. Verifique console do navegador (F12)

### Dados não atualizam

1. Verifique requisições AJAX no Network tab (F12)
2. Confirme que `$_REQUEST['ajax']` está funcionando
3. Teste manualmente: `curl "http://pfsense-ip/?ajax=true"`

## 📝 Notas

- **CDN**: O widget usa CDN para Leaflet.js. Para uso offline, baixe os arquivos e referencie localmente.
- **Desempenho**: Para muitos países (>50), considere otimizar com clustering de marcadores.
- **Segurança**: Valide e sanitize todos os dados antes de exibir no widget.
- **Compatibilidade**: Testado no pfSense CE 2.8.1 com tema zid-canvas.

## 📚 Recursos

- [Leaflet.js Documentation](https://leafletjs.com/)
- [pfSense Widget Development](https://docs.netgate.com/pfsense/en/latest/development/widget-development.html)
- [CARTO Basemaps](https://carto.com/help/building-maps/basemap-list/)

## 📄 Licença

Este widget faz parte do tema **zid-canvas** e segue a mesma licença.

## 🤝 Contribuição

Para melhorias e sugestões, entre em contato.
