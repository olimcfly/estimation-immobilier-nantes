CREATE TABLE webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    url VARCHAR(500),
    payload TEXT,
    http_code INT,
    response TEXT,
    status ENUM('success','failed') DEFAULT 'failed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event),
    INDEX idx_created (created_at)
);
