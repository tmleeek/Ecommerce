IF EXISTS (SELECT * FROM information_schema.table_constraints WHERE constraint_name = 'fk_blocks_atomdefinition_blockdefinition' AND table_schema = database()) THEN
	ALTER TABLE `exp_blocks_atomdefinition` DROP FOREIGN KEY `fk_blocks_atomdefinition_blockdefinition`;
END IF;

IF EXISTS (SELECT * FROM information_schema.table_constraints WHERE constraint_name = 'fk_blocks_blockdefinition_block' AND table_schema = database()) THEN
	ALTER TABLE `exp_blocks_block` DROP FOREIGN KEY `fk_blocks_blockdefinition_block`;
END IF;

IF EXISTS (SELECT * FROM information_schema.table_constraints WHERE constraint_name = 'fk_blocks_atom_block' AND table_schema = database()) THEN
	ALTER TABLE `exp_blocks_atom` DROP FOREIGN KEY `fk_blocks_atom_block`;
END IF;

IF EXISTS (SELECT * FROM information_schema.table_constraints WHERE constraint_name = 'fk_blocks_atom_atomdefinition' AND table_schema = database()) THEN
	ALTER TABLE `exp_blocks_atom` DROP FOREIGN KEY `fk_blocks_atom_atomdefinition`;
END IF;

IF EXISTS (SELECT * FROM information_schema.table_constraints WHERE constraint_name = 'fk_blocks_blockfieldusage_blockdefinition' AND table_schema = database()) THEN
	ALTER TABLE `exp_blocks_blockfieldusage` DROP FOREIGN KEY `fk_blocks_blockfieldusage_blockdefinition`;
END IF;

IF EXISTS (SELECT * FROM information_schema.columns WHERE table_schema = database() and table_name = 'exp_blocks_atom' and column_name = 'data' and is_nullable = 'NO') THEN
	ALTER TABLE `exp_blocks_atom` MODIFY `data` longtext;
END IF;
