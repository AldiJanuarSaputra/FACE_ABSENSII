CREATE TABLE IF NOT EXISTS siswa (
    id SERIAL PRIMARY KEY,
    nis VARCHAR(20) UNIQUE,
    nama VARCHAR(100),
    kelas VARCHAR(20),
    wajah TEXT,
    descriptor TEXT,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS admin (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin' CHECK (role IN ('admin', 'guru'))
);

CREATE TABLE IF NOT EXISTS absensi (
    id SERIAL PRIMARY KEY,
    nis VARCHAR(20),
    nama VARCHAR(100),
    kelas VARCHAR(20),
    tanggal DATE,
    jam TIME,
    status VARCHAR(20)
);