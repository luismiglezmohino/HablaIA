-- V005: Add default "Sin categoría" category for ARASAAC search results
-- Date: 2026-02-07
-- When a user searches for a pictogram not in the local DB, SearchPictogram
-- fetches it from ARASAAC and saves it with this default category.

-- UP

INSERT INTO categories (id, name, icon, color_hex, display_order)
VALUES ('00000000-0000-4000-8000-000000000000', 'Sin categoría', 'help-circle', '#9CA3AF', 99)
ON CONFLICT (id) DO NOTHING;

-- DOWN
-- DELETE FROM categories WHERE id = '00000000-0000-4000-8000-000000000000';
