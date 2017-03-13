--
-- Dumping data for table `Users`
--
INSERT INTO `Users` (`user_pk`, `user_name`, `user_password`, `user_salt`, `user_email_address`, `group_fk`) VALUES
(1, 'Admin', '123', '123', NULL, 10),
(2, 'Contributor', '123', '123', NULL, 5);

--
-- Dumping data for table `Files`
--
INSERT INTO `Files` (`file_pk`, `file_name`, `file_visible`, `file_created_date`, `user_fk`, `file_downloads`) VALUES
(1, 'File 1', 1, '2017-03-13 16:28:22', 1, 0),
(2, 'File 2', 1, '2017-03-13 16:28:22', 1, 0),
(3, 'File 3', 1, '2017-03-13 16:28:22', 2, 1),
(4, 'File 4', 1, '2017-03-13 16:28:22', 1, 0),
(5, 'File 5', 1, '2017-03-13 16:28:22', 2, 0),
(6, 'File 6', 1, '2017-03-13 16:28:22', 1, 0),
(7, 'File 7', 1, '2017-03-13 16:28:22', 1, 0),
(8, 'File 8', 1, '2017-03-13 16:28:22', 2, 0),
(9, 'File 9', 1, '2017-03-13 16:28:22', 2, 0),
(10, 'File 10', 1, '2017-03-13 16:28:22', 2, 0),
(11, 'File 11', 1, '2017-03-13 16:28:22', 2, 0),
(12, 'File 12', 1, '2017-03-13 16:28:22', 2, 0),
(13, 'File 13', 1, '2017-03-13 16:28:22', 1, 0),
(14, 'File 14', 1, '2017-03-13 16:28:22', 2, 0),
(15, 'File 15', 1, '2017-03-13 16:28:22', 2, 0);

--
-- Dumping data for table `Revisions`
--
INSERT INTO `Revisions` (`rev_pk`, `file_fk`, `rev_file_name`, `rev_file_path`, `rev_file_version`, `rev_upload_date`, `rev_status_fk`) VALUES
(1, 1, 'Test_1.zip', '/uploads/Test_1/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(2, 2, 'Test_1.zip', '/uploads/Test_2/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(3, 3, 'Test_1.zip', '/uploads/Test_3/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(4, 4, 'Test_1.zip', '/uploads/Test_4/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(5, 5, 'Test_1.zip', '/uploads/Test_5/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(6, 6, 'Test_1.zip', '/uploads/Test_6/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(7, 7, 'Test_1.zip', '/uploads/Test_7/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(8, 8, 'Test_1.zip', '/uploads/Test_8/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(9, 9, 'Test_1.zip', '/uploads/Test_9/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(10, 10, 'Test_1.zip', '/uploads/Test_10/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(11, 11, 'Test_1.zip', '/uploads/Test_11/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(12, 12, 'Test_1.zip', '/uploads/Test_12/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(13, 13, 'Test_1.zip', '/uploads/Test_13/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(14, 14, 'Test_1.zip', '/uploads/Test_14/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1),
(15, 15, 'Test_1.zip', '/uploads/Test_15/0.0.1/', '0.0.1', '2017-03-13 16:36:39', 1);
