<?php


require 'config.php';
require 'check_login.php';
require_login();
require 'lib/weather.php';
require 'lib/analytics.php';

$usuario_id = get_user_id();
$usuario_nome = get_user_name();


$stmt = $mysqli->prepare("SELECT * FROM favoritos WHERE usuario_id = ? ORDER BY COALESCE(ordem, 999999), criado_em DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$favoritos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


$stmt = $mysqli->prepare("SELECT * FROM config_usuario WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$config = $result->fetch_assoc();
$stmt->close();

if (!$config) {
    
    $unidade_temp = 'C';
    $idioma = 'pt';
    $tema = 'light';
    
    $stmt = $mysqli->prepare("INSERT INTO config_usuario (usuario_id, unidade_temp, idioma, tema) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $usuario_id, $unidade_temp, $idioma, $tema);
    $stmt->execute();
    $stmt->close();
} else {
    $unidade_temp = $config['unidade_temp'] ?? 'C';
    $idioma = $config['idioma'] ?? 'pt';
    $tema = $config['tema'] ?? 'light';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUMULUS - Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="Home.css">
    
    <style>
        #map {
            height: 60vh;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .weather-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .weather-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #667eea;
        }
        .favorite-item {
            transition: all 0.3s ease;
        }
        .favorite-item:hover {
            background-color: #f0f0f0;
            transform: translateX(5px);
        }
    </style>
</head>
<body class="<?php echo $tema === 'dark' ? 'bg-dark text-white' : ''; ?>">
    <?php include 'partials/header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Mapa -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-map"></i> Mapa Interativo</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="map"></div>
                    </div>
                </div>
                
                <!-- Busca de Localização -->
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Digite o nome da cidade ou local...">
                            <button class="btn btn-success" id="localizar" type="button" onclick="searchLocation()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button class="btn btn-info" id="geolocate" type="button" onclick="useGeolocation()">
                                <i class="fas fa-location-arrow"></i> Minha Localização
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Painel Lateral -->
            <div class="col-lg-4">
                <!-- Clima Atual -->
                <div id="weatherDetails" class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-cloud-sun"></i> Clima</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Clique no mapa ou busque um local para ver o clima.</p>
                    </div>
                </div>
                
                <!-- Favoritos -->
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-heart"></i> Favoritos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($favoritos)): ?>
                            <p class="text-muted">Clique no mapa para adicionar favoritos.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($favoritos as $f): ?>
                                    <div class="list-group-item favorite-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($f['nome_local']); ?></h6>
                                                <small class="text-muted"><?php echo round($f['latitude'], 4); ?>, <?php echo round($f['longitude'], 4); ?></small>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-primary" onclick="showWeather(<?php echo $f['latitude']; ?>, <?php echo $f['longitude']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a class="btn btn-sm btn-danger" href="remover_favorito.php?id=<?php echo $f['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let currentMarker = null;
        let map;
        const unidadeTemp = '<?php echo $unidade_temp; ?>';
        
        // Inicializar mapa
        function initMap() {
            map = L.map('map').setView([-23.55, -46.63], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            map.on('click', function(e) {
                const { lat, lng } = e.latlng;
                handleLocationSelection(lat, lng);
            });
        }
        
        // Exibir clima
        function showWeather(lat, lon) {
            const detailsDiv = document.getElementById('weatherDetails');
            detailsDiv.innerHTML = '<div class="card-body"><p class="text-muted">Carregando...</p></div>';
            
            fetch(`api_clima.php?lat=${lat}&lon=${lon}`)
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        let html = '<div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-cloud-sun"></i> Clima</h5></div><div class="card-body">';
                        
                        const current = data.data.current;
                        if (current && current.main) {
                            html += '<div class="weather-info">';
                            html += '<p><strong>Temperatura:</strong> ' + current.main.temp + '°C</p>';
                            html += '<p><strong>Sensação:</strong> ' + current.main.feels_like + '°C</p>';
                            html += '<p><strong>Umidade:</strong> ' + current.main.humidity + '%</p>';
                            html += '<p><strong>Condição:</strong> ' + (current.weather[0]?.description || 'N/A') + '</p>';
                            html += '</div>';
                        }
                        
                        html += '</div>';
                        detailsDiv.innerHTML = html;
                    } else {
                        detailsDiv.innerHTML = '<div class="card-header bg-warning"><h5 class="mb-0">Erro</h5></div><div class="card-body"><p class="text-danger">Erro ao carregar clima</p></div>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    detailsDiv.innerHTML = '<div class="card-header bg-danger text-white"><h5 class="mb-0">Erro</h5></div><div class="card-body"><p class="text-danger">Erro ao conectar</p></div>';
                });
        }
        
        // Salvar favorito
        function saveFavorite(lat, lon) {
            const nome = prompt('Nome do local:');
            if (!nome) return;
            
            fetch('adicionar_favorito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    nome_local: nome,
                    lat: lat,
                    lon: lon
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    alert('Favorito adicionado!');
                    location.reload();
                } else {
                    alert('Erro: ' + (data.error || 'Desconhecido'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao salvar');
            });
        }
        
        // Selecionar localização
        function handleLocationSelection(lat, lon) {
            if (currentMarker) map.removeLayer(currentMarker);
            
            currentMarker = L.marker([lat, lon]).addTo(map);
            currentMarker.bindPopup(`
                <b>Localização</b><br>
                ${lat.toFixed(6)}, ${lon.toFixed(6)}<br>
                <button class="btn btn-sm btn-primary" onclick="saveFavorite(${lat}, ${lon})">Salvar</button>
            `).openPopup();
            
            showWeather(lat, lon);
        }
        
        // Buscar localização
        function searchLocation() {
            const query = document.getElementById('searchInput').value.trim();
            if (!query) {
                alert('Digite um local para buscar');
                return;
            }
            
            const btn = document.getElementById('localizar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            
            fetch(`search_proxy.php?query=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        map.setView([lat, lon], 14);
                        handleLocationSelection(lat, lon);
                    } else {
                        alert('Local não encontrado');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro na busca');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search"></i> Buscar';
                });
        }
        
        // Geolocalização
        function useGeolocation() {
            if (!navigator.geolocation) {
                alert('Geolocalização não suportada');
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                position => {
                    const { latitude, longitude } = position.coords;
                    map.setView([latitude, longitude], 14);
                    handleLocationSelection(latitude, longitude);
                },
                error => {
                    alert('Erro ao obter localização: ' + error.message);
                }
            );
        }
        
        // Inicializar ao carregar
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
