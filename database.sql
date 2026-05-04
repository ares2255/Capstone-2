-- Run this in Supabase SQL Editor

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255),
    role VARCHAR(10)
);

INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff')
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY,
    hourly_rate DECIMAL(10,2),
    minimum_charge DECIMAL(10,2),
    bw_rate DECIMAL(10,2),
    color_rate DECIMAL(10,2),
    billing_method VARCHAR(20),
    rate_3hr DECIMAL(10,2),
    rate_5hr DECIMAL(10,2),
    rate_7hr DECIMAL(10,2),
    rate_12hr DECIMAL(10,2)
);

INSERT INTO settings (id, hourly_rate, minimum_charge, bw_rate, color_rate, billing_method, rate_3hr, rate_5hr, rate_7hr, rate_12hr)
VALUES (1, 15.00, 5.00, 2.00, 10.00, 'per_minute', 40.00, 60.00, 80.00, 150.00)
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS pcs (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50),
    status VARCHAR(10) DEFAULT 'available'
);

INSERT INTO pcs (name) VALUES
('PC-01'),('PC-02'),('PC-03'),('PC-04'),
('PC-05'),('PC-06'),('PC-07'),('PC-08'),
('PC-09'),('PC-10'),('PC-11'),('PC-12'),('PC-13')
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS sessions (
    id SERIAL PRIMARY KEY,
    pc_id INT,
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    cost DECIMAL(10,2),
    time_limit INT
);

CREATE TABLE IF NOT EXISTS print_jobs (
    id SERIAL PRIMARY KEY,
    type VARCHAR(10),
    pages INT,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    type VARCHAR(20),
    description VARCHAR(100),
    amount DECIMAL(10,2),
    time TIMESTAMP
);
