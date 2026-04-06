CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(20) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    type_bien ENUM('appartement','maison','terrain','commerce','immeuble') NOT NULL,
    surface DECIMAL(10, 2) NOT NULL,
    adresse TEXT,
    ville VARCHAR(100),
    prix_estime DECIMAL(12, 2),

    unsubscribed TINYINT(1) DEFAULT 0,
    email_relance_j3 TINYINT(1) DEFAULT 0,
    email_relance_j7 TINYINT(1) DEFAULT 0,
    email_relance_j14 TINYINT(1) DEFAULT 0,

    INDEX idx_email (email),
    INDEX idx_created (created_at),
    INDEX idx_ville (ville),
    INDEX idx_type_bien (type_bien)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
