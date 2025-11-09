-- ============================================
-- BANCO DE DADOS CUMULUS
-- ============================================

CREATE DATABASE IF NOT EXISTS cumulus;
USE cumulus;

-- ====== TABELAS SEM DEPENDÊNCIAS DE CHAVE ESTRANGEIRA ======

-- Tabela de Usuários (agora com 'role')
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_conta VARCHAR(20) NOT NULL DEFAULT 'usuario', -- Renomeado para tipo_conta para consistência
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Localizações (necessária para FK em alertas e previsoes)
DROP TABLE IF EXISTS localizacoes;
CREATE TABLE localizacoes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome_local VARCHAR(100),
  latitude   DECIMAL(10,8) NOT NULL,
  longitude  DECIMAL(11,8) NOT NULL,
  UNIQUE KEY uq_lat_lon (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====== TABELAS COM DEPENDÊNCIAS DE CHAVE ESTRANGEIRA ======

-- Tabela de Localizações Favoritas
DROP TABLE IF EXISTS favoritos;
CREATE TABLE favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_local VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    apelido VARCHAR(100) NULL,
    ordem INT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fav_user FOREIGN KEY (usuario_id)
      REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uq_fav_user_lat_lon (usuario_id, latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Alertas
DROP TABLE IF EXISTS alertas;
CREATE TABLE alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    mensagem VARCHAR(200) NULL,
    localidade VARCHAR(100) NULL,
    regra JSON NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    local_id INT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alert_user  FOREIGN KEY (usuario_id)
      REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_alert_local FOREIGN KEY (local_id)
      REFERENCES localizacoes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Configurações do Usuário
DROP TABLE IF EXISTS config_usuario;
CREATE TABLE config_usuario (
  usuario_id   INT PRIMARY KEY,
  unidade_temp VARCHAR(5)  NOT NULL DEFAULT 'C',
  idioma       VARCHAR(5)  NOT NULL DEFAULT 'pt',
  tema         VARCHAR(10) NOT NULL DEFAULT 'light',
  CONSTRAINT fk_cfg_user FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Previsões
DROP TABLE IF EXISTS previsoes;
CREATE TABLE previsoes (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  local_id  INT NOT NULL,
  data_hora DATETIME NOT NULL,
  temp_c    DECIMAL(5,2) NULL,
  umidade   INT NULL,
  condicao  VARCHAR(60) NULL,
  raw       JSON NULL,
  CONSTRAINT fk_prev_local FOREIGN KEY (local_id)
    REFERENCES localizacoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Sessões
DROP TABLE IF EXISTS sessoes;
CREATE TABLE sessoes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  jwt_id     VARCHAR(64) NULL,
  criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expira_em  TIMESTAMP NULL,
  CONSTRAINT fk_sess_user FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Logs de Consulta
DROP TABLE IF EXISTS logs_consulta;
CREATE TABLE logs_consulta (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  local_id   INT NULL,
  duracao_ms INT NULL,
  sucesso    TINYINT(1) NOT NULL DEFAULT 1,
  criado_em  TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user  FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_log_local FOREIGN KEY (local_id)
    REFERENCES localizacoes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Histórico de Pesquisas
DROP TABLE IF EXISTS historico_pesquisas;
CREATE TABLE historico_pesquisas (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT NULL,
  termo       VARCHAR(120) NOT NULL,
  origem      VARCHAR(20)  NOT NULL DEFAULT 'manual',
  latitude    DECIMAL(10,8) NULL,
  longitude   DECIMAL(11,8) NULL,
  resultados  INT NULL,
  sucesso     TINYINT(1) NOT NULL DEFAULT 1,
  duracao_ms  INT NULL,
  ip          VARCHAR(45) NULL,
  user_agent  VARCHAR(255) NULL,
  criado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hist_user FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_hist_user_time (usuario_id, criado_em),
  INDEX idx_hist_time (criado_em),
  INDEX idx_hist_termo (termo(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====== DADOS DE EXEMPLO ======

-- Senha 'password' com hash bcrypt: $2y$10$xzQMzZEXvKw0s24j9vScaO7vStiv1t3lQrdrvNT1DAHB8gdeJf2Om

INSERT INTO usuarios (nome, email, senha, tipo_conta) VALUES
('Admin User', 'admin@cumulus.app', '$2y$10$xzQMzZEXvKw0s24j9vScaO7vStiv1t3lQrdrvNT1DAHB8gdeJf2Om', 'admin'),
('Test User', 'user@cumulus.app', '$2y$10$xzQMzZEXvKw0s24j9vScaO7vStiv1t3lQrdrvNT1DAHB8gdeJf2Om', 'usuario');

-- Localizações de Exemplo
INSERT INTO localizacoes (nome_local, latitude, longitude) VALUES
('São Paulo, Brasil', -23.550520, -46.633308),
('Rio de Janeiro, Brasil', -22.906847, -43.172897),
('Belo Horizonte, Brasil', -19.916667, -43.933333),
('Curitiba, Brasil', -25.428916, -49.267136),
('Porto Alegre, Brasil', -30.034647, -51.217658);

-- Favoritos de Exemplo para o Test User (ID 2)
INSERT INTO favoritos (usuario_id, nome_local, latitude, longitude, apelido, ordem) VALUES
(2, 'São Paulo, Brasil', -23.550520, -46.633308, 'Casa', 1),
(2, 'Rio de Janeiro, Brasil', -22.906847, -43.172897, 'Trabalho', 2);

-- Alertas de Exemplo para o Test User (ID 2)
INSERT INTO alertas (usuario_id, tipo, mensagem, localidade, regra, ativo) VALUES
(2, 'Temperatura', 'Alerta de calor extremo! Acima de 30°C.', 'São Paulo', '{"tipo":"temperatura", "condicao":">", "valor":30, "unidade":"C"}', 1),
(2, 'Chuva', 'Alerta de chuva forte! Mais de 10mm.', 'Rio de Janeiro', '{"tipo":"chuva", "condicao":">=", "valor":10, "unidade":"mm"}', 1);

-- Configurações de Exemplo para o Test User (ID 2)
INSERT INTO config_usuario (usuario_id, unidade_temp, idioma, tema) VALUES
(2, 'C', 'pt', 'light');
