create table files_info
(
  file_id char(32) null,
  real_name varchar(255) null,
  extension varchar(255) not null,
  size int(12) null,
  url varchar(255),
  server_host varchar(255),
  uploaded_at timestamp DEFAULT CURRENT_TIMESTAMP,
  touched_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  constraint files_info_files_users_file_id_fk
    foreign key (file_id) references files_users (file_id)
);