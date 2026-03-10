-- Woassi GLY — Supabase SQL Editor
CREATE TABLE IF NOT EXISTS inscriptions (
    id         SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    nom        TEXT NOT NULL,
    prenom     TEXT NOT NULL,
    telephone  TEXT NOT NULL,
    superficie TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_ins_date ON inscriptions(created_at DESC);
