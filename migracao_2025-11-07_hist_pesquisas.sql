/* ==========================================================
   Migração: adicionar tabela de Histórico de Pesquisas
   Objetivo: registrar cada busca feita pelo usuário (termo,
   origem, lat/lon opcional, métricas e dados de contexto)
   ========================================================== */

USE cumulus;

START TRANSACTION;

CREATE TABLE IF NOT EXISTS historico_pesquisas (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT NULL,                         -- quem pesquisou (ou NULL para anônimo)
  termo       VARCHAR(120) NOT NULL,            -- texto digitado/buscado
  origem      VARCHAR(20)  NOT NULL DEFAULT 'manual', -- 'manual' | 'autocomplete' | 'favorito' | 'geo'
  latitude    DECIMAL(10,8) NULL,
  longitude   DECIMAL(11,8) NULL,

  resultados  INT NULL,                         -- qtd de resultados retornados (se aplicável)
  sucesso     TINYINT(1) NOT NULL DEFAULT 1,    -- 1=ok, 0=erro
  duracao_ms  INT NULL,                         -- tempo de resposta (ms)

  ip          VARCHAR(45) NULL,                 -- IPv4/IPv6
  user_agent  VARCHAR(255) NULL,                -- navegador/app
  criado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_hist_user FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE SET NULL,

  -- índices úteis
  INDEX idx_hist_user_time (usuario_id, criado_em),
  INDEX idx_hist_time (criado_em),
  INDEX idx_hist_termo (termo(50))              -- prefixo pra não pesar o índice
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

/* Verificações rápidas
SHOW CREATE TABLE historico_pesquisas\G
DESC historico_pesquisas;
*/
