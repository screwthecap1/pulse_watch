CREATE TABLE IF NOT EXISTS monitor_results
(
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    monitor_id       BIGINT UNSIGNED                        NOT NULL,
    checked_at       TIMESTAMP                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status           ENUM ('OK', 'FAIL','ERROR', 'UNKNOWN') NOT NULL,
    response_time_ms INT                                    NULL,
    http_code        INT                                    NULL,
    message          TEXT                                   NULL,
    INDEX idx_monitor_time (monitor_id, checked_at),
    CONSTRAINT fk_result_monitor FOREIGN KEY (monitor_id) REFERENCES monitors (id) ON DELETE CASCADE
) ENGINE = InnoDB;