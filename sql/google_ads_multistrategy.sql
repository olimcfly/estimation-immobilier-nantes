SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS google_ads_strategies (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    color VARCHAR(7) DEFAULT '#2563eb',
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_google_ads_strategies_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS google_ads_campaigns (
    id INT(11) NOT NULL AUTO_INCREMENT,
    strategy_id INT(11) NOT NULL,
    city_id INT(11) NOT NULL,
    awareness_level ENUM('hot', 'warm', 'cold') NOT NULL,
    budget_percent TINYINT(3) DEFAULT 60,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_google_ads_campaigns_strategy (strategy_id),
    INDEX idx_google_ads_campaigns_city (city_id),
    INDEX idx_google_ads_campaigns_active (is_active),
    CONSTRAINT fk_google_ads_campaigns_strategy FOREIGN KEY (strategy_id) REFERENCES google_ads_strategies(id) ON DELETE CASCADE,
    CONSTRAINT fk_google_ads_campaigns_city FOREIGN KEY (city_id) REFERENCES villes_prix(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
    id INT(11) NOT NULL AUTO_INCREMENT,
    strategy_id INT(11) NULL,
    campaign_id INT(11) NULL,
    city_id INT(11) NULL,
    type ENUM('estimation', 'rdv', 'call', 'investissement') NOT NULL,
    source ENUM('google_ads', 'facebook', 'organic', 'referral') DEFAULT 'google_ads',
    name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    property_type ENUM('house', 'apartment', 'land', 'other') DEFAULT 'house',
    property_address TEXT NULL,
    message TEXT NULL,
    status ENUM('new', 'contacted', 'converted', 'lost') DEFAULT 'new',
    conversion_value DECIMAL(10, 2) DEFAULT 5.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_leads_created_at (created_at),
    INDEX idx_leads_strategy (strategy_id),
    INDEX idx_leads_campaign (campaign_id),
    INDEX idx_leads_city (city_id),
    INDEX idx_leads_status (status),
    INDEX idx_leads_source (source),
    CONSTRAINT fk_leads_strategy FOREIGN KEY (strategy_id) REFERENCES google_ads_strategies(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_campaign FOREIGN KEY (campaign_id) REFERENCES google_ads_campaigns(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_city FOREIGN KEY (city_id) REFERENCES villes_prix(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS google_ads_keywords (
    id INT(11) NOT NULL AUTO_INCREMENT,
    campaign_id INT(11) NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    match_type ENUM('exact', 'phrase', 'broad') DEFAULT 'exact',
    is_negative TINYINT(1) DEFAULT 0,
    max_cpc DECIMAL(5, 2) DEFAULT 2.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_google_ads_keywords_campaign (campaign_id),
    CONSTRAINT fk_google_ads_keywords_campaign FOREIGN KEY (campaign_id) REFERENCES google_ads_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS google_ads_ads (
    id INT(11) NOT NULL AUTO_INCREMENT,
    campaign_id INT(11) NOT NULL,
    headline_1 VARCHAR(30) NOT NULL,
    headline_2 VARCHAR(30) NULL,
    headline_3 VARCHAR(30) NULL,
    description_1 VARCHAR(90) NOT NULL,
    description_2 VARCHAR(90) NULL,
    final_url VARCHAR(255) NOT NULL,
    path_1 VARCHAR(15) NULL,
    path_2 VARCHAR(15) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_google_ads_ads_campaign (campaign_id),
    CONSTRAINT fk_google_ads_ads_campaign FOREIGN KEY (campaign_id) REFERENCES google_ads_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE leads
    ADD COLUMN IF NOT EXISTS strategy_id INT(11) NULL,
    ADD COLUMN IF NOT EXISTS campaign_id INT(11) NULL,
    ADD COLUMN IF NOT EXISTS city_id INT(11) NULL,
    ADD COLUMN IF NOT EXISTS type ENUM('estimation', 'rdv', 'call', 'investissement') NULL,
    ADD COLUMN IF NOT EXISTS source ENUM('google_ads', 'facebook', 'organic', 'referral') DEFAULT 'google_ads',
    ADD COLUMN IF NOT EXISTS name VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS property_type ENUM('house', 'apartment', 'land', 'other') DEFAULT 'house',
    ADD COLUMN IF NOT EXISTS property_address TEXT NULL,
    ADD COLUMN IF NOT EXISTS message TEXT NULL,
    ADD COLUMN IF NOT EXISTS status ENUM('new', 'contacted', 'converted', 'lost') DEFAULT 'new',
    ADD COLUMN IF NOT EXISTS conversion_value DECIMAL(10, 2) DEFAULT 5.00;
