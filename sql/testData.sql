--
-- Dumping data for table `Users`
--
INSERT INTO `Users` (`user_pk`, `user_name`, `user_password`, `user_email_address`, `group_fk`) VALUES
(1, 'Admin', '123', 'admin@test.nl', 10),
(2, 'Contributor', '123', 'contrib@test.nl', 5);

--
-- Dumping data for table `Maps`
--
INSERT INTO `Maps` (`map_pk`, `map_name`, `map_visible`, `map_created_date`, `user_fk`, `map_downloads`, `map_Type_fk`) VALUES
(1, 'Map 1', 1, '2017-03-13 16:28:22', 1, 35, 0),
(2, 'Map 2', 1, '2017-03-13 16:28:22', 1, 23, 0),
(3, 'Map 3', 1, '2017-03-13 16:28:22', 2, 1, 3),
(4, 'Map 4', 1, '2017-03-13 16:28:22', 1, 0, 0),
(5, 'Map 5', 1, '2017-03-13 16:28:22', 2, 75, 4),
(6, 'Map 6', 1, '2017-03-13 16:28:22', 1, 4, 0),
(7, 'Map 7', 1, '2017-03-13 16:28:22', 1, 9, 0),
(8, 'Map 8', 1, '2017-03-13 16:28:22', 2, 23, 0),
(9, 'Map 9', 1, '2017-03-13 16:28:22', 2, 435, 5),
(10, 'Map 10', 1, '2017-03-13 16:28:22', 2, 9, 0),
(11, 'Map 11', 1, '2017-03-13 16:28:22', 2, 62, 1),
(12, 'Map 12', 1, '2017-03-13 16:28:22', 2, 5, 0),
(13, 'Map 13', 1, '2017-03-13 16:28:22', 1, 1, 0),
(14, 'Map 14', 1, '2017-03-13 16:28:22', 2, 346, 5),
(15, 'Map 15', 1, '2017-03-13 16:28:22', 2, 2, 0);

--
-- Dumping data for table `Revisions`
--
INSERT INTO `Revisions` (`rev_pk`, `map_fk`, `rev_map_file_name`, `rev_map_file_path`, `rev_map_version`, `rev_map_description_short`, `rev_map_description`, `rev_upload_date`, `rev_status_fk`) VALUES
(1, 1, 'Test_1.zip', '/uploads/Test_1/0.0.1/', '0.0.1', 'Test mission 1', 'This is a longdescription for :\r\nTest mission 1', '2017-03-13 16:36:39', 1),
(2, 2, 'Test_2.zip', '/uploads/Test_2/0.0.1/', '0.0.1', 'Test mission 2', 'This is a longdescription for :\r\nTest mission 2', '2017-03-13 16:36:39', 1),
(3, 3, 'Test_3.zip', '/uploads/Test_3/0.0.1/', '0.0.1', 'Test mission 3', 'This is a longdescription for :\r\nTest mission 3', '2017-03-13 16:36:39', 1),
(4, 4, 'Test_4.zip', '/uploads/Test_4/0.0.1/', '0.0.1', 'Test mission 4', 'This is a longdescription for :\r\nTest mission 4', '2017-03-13 16:36:39', 1),
(5, 5, 'Test_5.zip', '/uploads/Test_5/0.0.1/', '0.0.1', 'Test mission 5', 'This is a longdescription for :\r\nTest mission 5', '2017-03-13 16:36:39', 1),
(6, 6, 'Test_6.zip', '/uploads/Test_6/0.0.1/', '0.0.1', 'Test mission 6', 'This is a longdescription for :\r\nTest mission 6', '2017-03-13 16:36:39', 1),
(7, 7, 'Test_7.zip', '/uploads/Test_7/0.0.1/', '0.0.1', 'Test mission 7', 'This is a longdescription for :\r\nTest mission 7', '2017-03-13 16:36:39', 1),
(8, 8, 'Test_8.zip', '/uploads/Test_8/0.0.1/', '0.0.1', 'Test mission 8', 'This is a longdescription for :\r\nTest mission 8', '2017-03-13 16:36:39', 1),
(9, 9, 'Test_9.zip', '/uploads/Test_9/0.0.1/', '0.0.1', 'Test mission 9', 'This is a longdescription for :\r\nTest mission 9', '2017-03-13 16:36:39', 1),
(10, 10, 'Test_10.zip', '/uploads/Test_10/0.0.1/', '0.0.1', 'Test mission 10', 'This is a longdescription for :\r\nTest mission 10', '2017-03-13 16:36:39', 1),
(11, 11, 'Test_11.zip', '/uploads/Test_11/0.0.1/', '0.0.1', 'Test mission 11', 'This is a longdescription for :\r\nTest mission 11', '2017-03-13 16:36:39', 1),
(12, 12, 'Test_12.zip', '/uploads/Test_12/0.0.1/', '0.0.1', 'Test mission 12', 'This is a longdescription for :\r\nTest mission 12', '2017-03-13 16:36:39', 1),
(13, 13, 'Test_13.zip', '/uploads/Test_13/0.0.1/', '0.0.1', 'Test mission 13', 'This is a longdescription for :\r\nTest mission 13', '2017-03-13 16:36:39', 1),
(14, 14, 'Test_14.zip', '/uploads/Test_14/0.0.1/', '0.0.1', 'Test mission 14', 'This is a longdescription for :\r\nTest mission 14', '2017-03-13 16:36:39', 1),
(15, 15, 'Test_15.zip', '/uploads/Test_15/0.0.1/', '0.0.1', 'Test mission 15', 'This is a longdescription for :\r\nTest mission 15', '2017-03-13 16:36:39', 1);

INSERT INTO `Ratings` (`rating_pk`, `map_fk`, `rating_amount`, `rating_ip`) VALUES
(1, 8, 2, '0'),
(2, 12, 2, '0'),
(3, 8, 3, '0'),
(4, 8, 1, '0'),
(5, 12, 5, '0'),
(6, 6, 5, '0'),
(7, 14, 3, '0'),
(8, 9, 1, '0'),
(9, 11, 3, '0'),
(10, 13, 1, '0'),
(11, 11, 3, '0'),
(12, 2, 4, '0'),
(13, 6, 3, '0'),
(14, 1, 1, '0'),
(15, 3, 3, '0'),
(16, 12, 3, '0'),
(17, 15, 1, '0'),
(18, 10, 1, '0'),
(19, 10, 1, '0'),
(20, 7, 3, '0'),
(21, 11, 2, '0'),
(22, 14, 1, '0'),
(23, 2, 1, '0'),
(24, 10, 5, '0'),
(25, 15, 5, '0'),
(26, 12, 5, '0'),
(27, 7, 3, '0'),
(28, 10, 2, '0'),
(29, 15, 4, '0'),
(30, 6, 3, '0'),
(31, 5, 2, '0'),
(32, 11, 4, '0'),
(33, 9, 5, '0'),
(34, 14, 1, '0'),
(35, 7, 1, '0'),
(36, 13, 4, '0'),
(37, 10, 3, '0'),
(38, 6, 1, '0'),
(39, 3, 4, '0'),
(40, 15,1, '0'),
(41, 5, 5, '0'),
(42, 8, 3, '0'),
(43, 15, 4, '0'),
(44, 3, 2, '0'),
(45, 5, 3, '0'),
(46, 12, 4, '0'),
(47, 5, 5, '0'),
(48, 2, 1, '0'),
(49, 15, 1, '0'),
(50, 2, 3, '0'),
(51, 7, 5, '0'),
(52, 11, 4, '0'),
(53, 5, 3, '0'),
(54, 3, 5, '0'),
(55, 5, 2, '0'),
(56, 11, 4, '0'),
(57, 15, 3, '0'),
(58, 12, 4, '0'),
(59, 10, 5, '0'),
(60, 5, 3, '0');
