create table controller_files
(
	file_id varchar(32) not null
		primary key,
	owner_id varchar(32) not null,
	created_at datetime not null,
	file_name varchar(255) not null,
	path varchar(255) not null,
	requests_count int null,
	constraint controller_files_file_id_uindex
		unique (file_id),
	constraint controller_files_path_uindex
		unique (path)
);

CREATE PROCEDURE createControllerFile(IN idFile   VARCHAR(32), IN fileName VARCHAR(100), IN idOwner VARCHAR(32),
                                      IN FilePath VARCHAR(255))
  BEGIN
    INSERT INTO controller_files (file_id, path, created_at, file_name, owner_id) VALUES (idFile,FilePath,NOW(),fileName,idOwner);
  END;

CREATE PROCEDURE deleteControllerFile(IN idFile VARCHAR(32))
  BEGIN
    DELETE FROM controller_files WHERE file_id=idFile;
  END;

CREATE PROCEDURE getFileData(IN idFile VARCHAR(32))
  BEGIN
    SELECT * FROM controller_files WHERE file_id = idFile;
  END;

  CREATE PROCEDURE `getAllFiles`(IN Columns VARCHAR(255))
  BEGIN
    START TRANSACTION;
    SET @sql = CONCAT('SELECT ', Columns, ' from controller_files;');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    COMMIT;
  END;

CREATE PROCEDURE `getSandboxFiles`(IN idUser VARCHAR(32))
  BEGIN
    SELECT * from sandbox_files WHERE owner_id = idUser;
  END;

CREATE PROCEDURE `createSandboxFile`(IN idFile   VARCHAR(32), IN fileName VARCHAR(100), IN idOwner VARCHAR(32),
                                     IN FilePath VARCHAR(255))
  BEGIN
    INSERT INTO sandbox_files (file_id, path, created_at, file_name, owner_id) VALUES (idFile,FilePath,NOW(),fileName,idOwner);
  END;

CREATE PROCEDURE `deleteSandboxFile`(IN idFile VARCHAR(32))
BEGIN
DELETE FROM sandbox_files WHERE file_id=idFile;
END;

CREATE PROCEDURE `getSandboxFileData`(IN idFile VARCHAR(32))
  BEGIN
    SELECT * FROM sandbox_files WHERE file_id = idFile;
  END;

CREATE PROCEDURE `updateFileData`(IN idFile  VARCHAR(32), IN filePath VARCHAR(255))
  BEGIN
    UPDATE controller_files SET path=filePath WHERE file_id=idFile;
  END
