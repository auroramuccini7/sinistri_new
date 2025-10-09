CREATE TABLE sinistri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50),
    anno INT,
    numero INT,
    reparto VARCHAR(100),
    gestione VARCHAR(100),
    stato VARCHAR(50),
    data_evento DATE,
    tipo_danno VARCHAR(100),
    causa VARCHAR(255),
    strada VARCHAR(255),
    num_civ VARCHAR(50),
    annotazioni TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE fasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sinistro_id INT,
    cod_fase VARCHAR(50),
    des_fase VARCHAR(255),
    data_inizio DATE,
    data_fine DATE,
    esito VARCHAR(100),
    valore DECIMAL(10,2),
    annotazioni TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sinistro_id) REFERENCES sinistri(id) ON DELETE CASCADE
);
