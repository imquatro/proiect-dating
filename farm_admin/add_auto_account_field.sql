-- Script pentru adăugarea câmpului auto_account în tabelul users
-- Acest câmp va distinge între conturile create automat din admin panel și cele create prin înregistrare normală

-- Adaugă câmpul auto_account cu valoare default 0 (cont normal)
ALTER TABLE users ADD COLUMN auto_account TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = cont creat din admin panel, 0 = cont creat prin înregistrare normală';

-- Opțional: Adaugă un index pentru performanță mai bună la query-uri
CREATE INDEX idx_auto_account ON users(auto_account);

-- Opțional: Adaugă un comentariu la tabel
ALTER TABLE users COMMENT = 'Tabel utilizatori cu distincție între conturi auto și normale';
