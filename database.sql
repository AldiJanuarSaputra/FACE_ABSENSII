CREATE TABLE IF NOT EXISTS siswa (
    id SERIAL PRIMARY KEY,
    nis VARCHAR(20) UNIQUE,
    nama VARCHAR(100),
    kelas VARCHAR(20),
    wajah TEXT,
    descriptor TEXT
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