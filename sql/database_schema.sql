-- Datenbank-Schema für die Zeiterfassungssoftware

-- Benutzer-Tabelle
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

-- Mitarbeiter-Tabelle
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

-- Feiertage-Tabelle
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    description VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

-- Arbeitszeit-Tabelle
CREATE TABLE IF NOT EXISTS work_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    arbetstid DECIMAL(4,1) DEFAULT 6.0, -- Standardarbeitszeit
    sjuk DECIMAL(4,1) DEFAULT 0.0,      -- Krankheit
    semester DECIMAL(4,1) DEFAULT 0.0,  -- Urlaub
    avtalad_procent DECIMAL(5,1) DEFAULT 100.0, -- Vereinbarte Arbeitszeit in Prozent
    arbete DECIMAL(4,1) DEFAULT 0.0,    -- Tatsächliche Arbeitszeit
    distansarbete DECIMAL(4,1) DEFAULT 0.0, -- Fernarbeit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (employee_id, date),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

-- Standardbenutzer einfügen
INSERT INTO users (username, password) VALUES 
('olofb', '$2y$10$YourHashedPasswordHere'); -- Passwort wird später korrekt gehasht 