IF NOT EXISTS (SELECT * FROM exp_content_types WHERE name = 'blocks') THEN
INSERT INTO exp_content_types (name) values ('blocks');
END IF;
