<?php
/*
 * geomap.widget.php
 *
 * Widget de Mapa GeoBlocked para pfSense CE 2.8.1
 * Exibe bloqueios geográficos em um mapa interativo usando Leaflet.js
 *
 * Instalação:
 * 1. Copiar para /usr/local/www/widgets/widgets/geomap.widget.php
 * 2. O widget aparecerá automaticamente no Dashboard
 */

// Configuração do widget
$nocsrf = true;

require_once("guiconfig.inc");
require_once("pfsense-utils.inc");
require_once("functions.inc");
require_once("/usr/local/www/widgets/include/widget-utils.inc");

// Informações do widget
$widget_name = "GeoBlocked Map";
$widget_title = "Mapa de Bloqueios Geográficos";
$widget_description = "Visualização em tempo real de bloqueios geográficos";

// Função para obter dados de bloqueio
function get_geoblocked_data() {
    // Aqui você pode integrar com o firewall para obter dados reais
    // Por enquanto, retornamos dados de exemplo

    $blocked_countries = array();

    // Exemplo: ler logs do pfBlockerNG ou do firewall
    // exec("pfctl -s rules | grep block", $output);

    // Dados de exemplo para demonstração
    $blocked_countries = array(
        array('country' => 'China', 'code' => 'CN', 'lat' => 35.8617, 'lon' => 104.1954, 'blocks' => 1523),
        array('country' => 'Russia', 'code' => 'RU', 'lat' => 61.5240, 'lon' => 105.3188, 'blocks' => 892),
        array('country' => 'North Korea', 'code' => 'KP', 'lat' => 40.3399, 'lon' => 127.5101, 'blocks' => 456),
        array('country' => 'Iran', 'code' => 'IR', 'lat' => 32.4279, 'lon' => 53.6880, 'blocks' => 234),
        array('country' => 'Ukraine', 'code' => 'UA', 'lat' => 48.3794, 'lon' => 31.1656, 'blocks' => 189),
    );

    return $blocked_countries;
}

// Processar requisições AJAX
if ($_REQUEST['ajax']) {
    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => true,
        'data' => get_geoblocked_data(),
        'total_blocks' => array_sum(array_column(get_geoblocked_data(), 'blocks')),
        'countries_blocked' => count(get_geoblocked_data()),
        'last_update' => date('Y-m-d H:i:s')
    ));
    exit;
}

// Obter dados para exibição inicial
$blocked_data = get_geoblocked_data();
$total_blocks = array_sum(array_column($blocked_data, 'blocks'));
$countries_count = count($blocked_data);

?>

<div class="panel panel-default" id="geomap-widget">
    <div class="panel-heading">
        <h2 class="panel-title">
            <i class="fa fa-globe"></i> <?= $widget_title ?>
            <span class="badge badge-danger pull-right" id="total-blocks"><?= number_format($total_blocks) ?></span>
        </h2>
    </div>
    <div class="panel-body">
        <!-- Container do mapa -->
        <div id="geomap-container" style="height: 400px; border-radius: 12px; overflow: hidden; background: #0f172a;"></div>

        <!-- Estatísticas -->
        <div class="geomap-stats" style="margin-top: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
            <div class="stat-card">
                <div class="stat-label">Total de Bloqueios</div>
                <div class="stat-value text-danger" id="stat-total"><?= number_format($total_blocks) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Países Bloqueados</div>
                <div class="stat-value text-warning" id="stat-countries"><?= $countries_count ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Última Atualização</div>
                <div class="stat-value text-muted" style="font-size: 0.875rem;" id="stat-updated"><?= date('H:i:s') ?></div>
            </div>
        </div>

        <!-- Top 5 países bloqueados -->
        <div class="top-blocked-countries" style="margin-top: 20px;">
            <h4 style="margin-bottom: 12px; font-size: 0.875rem; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.05em;">
                Top 5 Países Bloqueados
            </h4>
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>País</th>
                            <th>Código</th>
                            <th class="text-right">Bloqueios</th>
                        </tr>
                    </thead>
                    <tbody id="top-countries-tbody">
                        <?php foreach (array_slice($blocked_data, 0, 5) as $country): ?>
                        <tr>
                            <td><i class="fa fa-circle text-danger" style="font-size: 8px; margin-right: 6px;"></i> <?= htmlspecialchars($country['country']) ?></td>
                            <td><code><?= htmlspecialchars($country['code']) ?></code></td>
                            <td class="text-right"><strong><?= number_format($country['blocks']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script type="text/javascript">
//<![CDATA[
(function() {
    'use strict';

    // Configuração do mapa
    var mapConfig = {
        center: [20, 0],
        zoom: 2,
        minZoom: 2,
        maxZoom: 8,
        scrollWheelZoom: false,
        dragging: true,
        zoomControl: true
    };

    // Inicializar o mapa
    var map = L.map('geomap-container', mapConfig);

    // Adicionar tile layer (dark theme)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    // Dados iniciais do PHP
    var initialData = <?= json_encode($blocked_data) ?>;

    // Array para armazenar marcadores
    var markers = [];

    // Função para criar ícone customizado
    function createBlockIcon(blocks) {
        var size = Math.min(40, Math.max(20, Math.log(blocks + 1) * 8));
        var opacity = Math.min(1, Math.max(0.6, blocks / 2000));

        return L.divIcon({
            className: 'block-marker',
            html: '<div style="background: rgba(239, 68, 68, ' + opacity + '); width: ' + size + 'px; height: ' + size + 'px; border-radius: 50%; border: 2px solid #ef4444; box-shadow: 0 0 10px rgba(239, 68, 68, 0.6); animation: pulse-glow 2s infinite;"></div>',
            iconSize: [size, size],
            iconAnchor: [size/2, size/2]
        });
    }

    // Função para adicionar marcadores
    function addMarkers(data) {
        // Limpar marcadores existentes
        markers.forEach(function(marker) {
            map.removeLayer(marker);
        });
        markers = [];

        // Adicionar novos marcadores
        data.forEach(function(country) {
            var marker = L.marker([country.lat, country.lon], {
                icon: createBlockIcon(country.blocks)
            }).addTo(map);

            // Popup com informações
            var popupContent = '<div style="font-family: \'Plus Jakarta Sans\', sans-serif;">' +
                '<strong style="color: #ef4444; font-size: 14px;">' + country.country + '</strong><br>' +
                '<span style="color: #cbd5e1; font-size: 12px;">Código: <code>' + country.code + '</code></span><br>' +
                '<span style="color: #f8fafc; font-size: 13px; font-weight: 600;">' +
                country.blocks.toLocaleString() + ' bloqueios</span>' +
                '</div>';

            marker.bindPopup(popupContent, {
                className: 'custom-popup'
            });

            markers.push(marker);
        });
    }

    // Adicionar marcadores iniciais
    addMarkers(initialData);

    // Função para atualizar dados
    function updateGeomapData() {
        $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: { ajax: true },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Atualizar marcadores
                    addMarkers(response.data);

                    // Atualizar estatísticas
                    $('#stat-total').text(response.total_blocks.toLocaleString());
                    $('#stat-countries').text(response.countries_blocked);
                    $('#stat-updated').text(new Date(response.last_update).toLocaleTimeString());
                    $('#total-blocks').text(response.total_blocks.toLocaleString());

                    // Atualizar tabela
                    updateTopCountriesTable(response.data.slice(0, 5));
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao atualizar dados do mapa:', error);
            }
        });
    }

    // Função para atualizar tabela de top países
    function updateTopCountriesTable(data) {
        var tbody = $('#top-countries-tbody');
        tbody.empty();

        data.forEach(function(country) {
            var row = '<tr>' +
                '<td><i class="fa fa-circle text-danger" style="font-size: 8px; margin-right: 6px;"></i> ' +
                country.country + '</td>' +
                '<td><code>' + country.code + '</code></td>' +
                '<td class="text-right"><strong>' + country.blocks.toLocaleString() + '</strong></td>' +
                '</tr>';
            tbody.append(row);
        });
    }

    // Atualizar a cada 30 segundos
    setInterval(updateGeomapData, 30000);

    // Ajustar mapa quando o widget é redimensionado
    var resizeObserver = new ResizeObserver(function() {
        map.invalidateSize();
    });
    resizeObserver.observe(document.getElementById('geomap-container'));

})();
//]]>
</script>

<style>
/* Estilos customizados para o mapa */
.leaflet-popup-content-wrapper {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
}

.leaflet-popup-tip {
    background: #1e293b;
}

.leaflet-popup-content {
    margin: 12px;
    color: #f8fafc;
}

.leaflet-control-zoom a {
    background: #1e293b !important;
    border: 1px solid #334155 !important;
    color: #f8fafc !important;
}

.leaflet-control-zoom a:hover {
    background: #334155 !important;
}

.stat-card {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 8px;
    padding: 12px;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f8fafc;
}

@keyframes pulse-glow {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
}

.block-marker {
    background: transparent;
    border: none;
}

#geomap-widget .table {
    margin-bottom: 0;
}

#geomap-widget .table code {
    background: #1e293b;
    color: #b34849;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem;
}
</style>
