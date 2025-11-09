/* 02_migracao_2025-11.sql
   Evolui o schema do Cumulus para atender 10F + 5NF.
   - Adiciona perfil em usuarios (role)
   - Completa favoritos (apelido, ordem, índice único)
   - Completa alertas (regra JSON, ativo, local_id)
   - Cria tabelas: config_usuario, localizacoes, previsoes, sessoes, logs_consulta
   Compatível com MySQL 5.7/8.x (sem depender de ADD COLUMN IF NOT EXISTS)
*/

START TRANSACTION;

-- Escolhe o schema do projeto
USE `cumulus`;

-- ==========================================================
-- 1) usuarios.role (perfil)
-- ==========================================================
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'role'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE usuarios ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT ''user''',
  'SELECT 1'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ==========================================================
-- 2) favoritos: apelido, ordem e índice único (usuario,lat,lon)
-- ==========================================================

-- 2.1 apelido
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'favoritos'
    AND COLUMN_NAME = 'apelido'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE favoritos ADD COLUMN apelido VARCHAR(100) NULL',
  'SELECT 1'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- 2.2 ordem
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'favoritos'
    AND COLUMN_NAME = 'ordem'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE favoritos ADD COLUMN ordem INT NULL',
  'SELECT 1'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- 2.3 índice único (usuario_id, latitude, longitude)
SET @idx_exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'favoritos'
    AND INDEX_NAME = 'uq_fav_user_lat_lon'
);
SET @sql := IF(@idx_exists = 0,
  'ALTER TABLE favoritos ADD UNIQUE KEY uq_fav_user_lat_lon (usuario_id, latitude, longitude)',
  'SELECT 1'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ==========================================================
-- 3) alertas: regra (JSON), ativo, local_id
-- ==========================================================

-- 3.1 regra (JSON)
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'alertas'
    AND COLUMN_NAME = 'regra'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE alertas ADD COLUMN regra JSON NULL',
  'SELECT 1'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- 3.2 ativo
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'alertas'
    AND COLUMN_NAME = 'ativo'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE alertas ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1',
  'SELECT 1'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- 3.3 local_id
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'alertas'
    AND COLUMN_NAME = 'local_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE alertas ADD COLUMN local_id INT NULL',
  'SELECT 1'
);
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ==========================================================
-- 4) Tabelas novas (criadas apenas se não existirem)
-- ==========================================================

CREATE TABLE IF NOT EXISTS config_usuario (
  usuario_id   INT PRIMARY KEY,
  unidade_temp VARCHAR(5)  NOT NULL DEFAULT 'C',   -- 'C' ou 'F'
  idioma       VARCHAR(5)  NOT NULL DEFAULT 'pt',  -- 'pt' ou 'en'
  tema         VARCHAR(10) NOT NULL DEFAULT 'light', -- 'light' ou 'dark'
  CONSTRAINT fk_cfg_user FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS localizacoes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome_local VARCHAR(100),
  latitude   DECIMAL(10,8) NOT NULL,
  longitude  DECIMAL(11,8) NOT NULL,
  UNIQUE KEY uq_lat_lon (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS previsoes (
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

CREATE TABLE IF NOT EXISTS sessoes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  jwt_id     VARCHAR(64) NULL,
  criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expira_em  TIMESTAMP NULL,
  CONSTRAINT fk_sess_user FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS logs_consulta (
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

COMMIT;

/* ==========================================================
   (Opcional) Promover um usuário a admin
   UPDATE usuarios SET role='admin' WHERE email='seu-admin@exemplo.com';
   ========================================================== */

/* ==========================================================
   Verificações rápidas (rode depois da migração)
   ==========================================================
   USE cumulus;
   DESC usuarios;
   SHOW INDEX FROM favoritos;
   SHOW CREATE TABLE alertas\G
   SHOW CREATE TABLE config_usuario\G
   SHOW CREATE TABLE localizacoes\G
   SHOW CREATE TABLE previsoes\G
   SHOW CREATE TABLE sessoes\G
   SHOW CREATE TABLE logs_consulta\G
*/

-- ainda no DB cumulus
SHOW TABLES;  -- deve ter 8: usuarios, localizacoes, favoritos, alertas, previsoes, config_usuario, sessoes, logs_consulta

DESC usuarios;              -- verifique a coluna role
SHOW INDEX FROM favoritos;  -- deve exibir uq_fav_user_lat_lon

SHOW CREATE TABLE alertas;
SHOW CREATE TABLE config_usuario;
SHOW CREATE TABLE localizacoes;
SHOW CREATE TABLE previsoes;
SHOW CREATE TABLE sessoes;
SHOW CREATE TABLE logs_consulta;

				


