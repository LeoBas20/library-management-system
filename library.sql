-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 06:03 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `books_db`
--

CREATE TABLE `books_db` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(50) NOT NULL,
  `isbn` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books_db`
--

INSERT INTO `books_db` (`book_id`, `title`, `author`, `isbn`, `quantity`) VALUES
(17, 'Java for Beginners: Build Your Dream Tech Career with Engaging Lessons and Projects\r\n', 'Swift Learning Publication', '979-8337974477', 11),
(18, 'Learn Python Programming: A comprehensive, up-to-date, and definitive guide to learning Python', 'Fabrizio Romano, Heinrich Kruger', '978-1835882948', 2),
(19, 'Python Machine Learning By Example: Unlock machine learning best practices with real-world use cases 4th Edition', 'Yuxi (Hayden) Liu', '978-1835085622', 0),
(20, 'Mathematics of Machine Learning: Master linear algebra, calculus, and probability for machine learning', 'Tivadar Danka', '978-1837027873', 5),
(21, 'Coding Interview Patterns: Nail Your Next Coding Interview', 'Alex Xu, Shaun Gunawardane', '978-1736049136', 7),
(22, 'PHP & MySQL: Server-side Web Development', 'Jon Duckett', '978-1119149224', 7),
(23, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', '978-0590353403', 1),
(24, 'Harry Potter and the Chamber of Secrets', 'J.K. Rowling', '978-0439064866', 2),
(25, 'Harry Potter and the Prisoner of Azkaban', 'J.K. Rowling', '978-0439136358', 3),
(26, 'Harry Potter and the Goblet of Fire', 'J.K. Rowling', '978-0439139595', 1),
(27, 'Harry Potter and the Order of the Phoenix', 'J.K. Rowling', '978-0439358064', 0),
(28, 'Python Crash Course, 3rd Edition: A Hands-On, Project-Based Introduction to Programming', 'Eric Matthes', '978-1718502703', 0),
(29, 'Deep Learning with Python, Second Edition', 'Francois Chollet', '978-1617296864', 0),
(30, 'PHP: The Complete Reference', 'Steven Holzner', '978-0071508544', 2),
(31, 'HTML and CSS: Design and Build Websites', 'Jon Duckett', '978-1118008188', 2),
(32, 'The Clean Coder: A Code of Conduct for Professional Programmers', 'Robert Martin', '978-0137081073', 5),
(33, 'C++ Memory Management: Write leaner and safer C++ code using proven memory-management techniques', 'Patrice Roy', '978-1805129806', 2),
(34, 'AI Engineering: Building Applications with Foundation Models', 'Chip Huyen', '978-1098166304', 9),
(35, 'Building AI Agents with LLMs, RAG, and Knowledge Graphs: A practical guide to autonomous and modern AI agents', 'Salvatore Raieli, Gabriele Iuculano', '978-1835087060', 2),
(36, 'Azure AI-102 Certification Essentials: Master the AI Engineer Associate exam with real-world case studies and full-length mock tests', 'Peter T. Lee', '978-1836205272', 2),
(37, 'Co-Intelligence: Living and Working with AI', 'Ethan Mollick', '978-0753560778', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `request_date` date NOT NULL DEFAULT curdate(),
  `issue_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('pending','borrowed','returned','rejected','overdue') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `book_id`, `qty`, `request_date`, `issue_date`, `due_date`, `return_date`, `status`) VALUES
(1, '2023-001', 34, 1, '2025-12-08', NULL, NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `role` enum('student','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `reset_token_hash`, `reset_token_expires_at`, `role`) VALUES
('2023-001', 'John Doe', 'dummy@gmail.com', '$2y$10$LOLn26SUo5PeEQj3z.oRJenpEj7jqNNEgBwa7Tn1bEg2QeoEBvbSm', NULL, NULL, 'student'),
('FAC-001', 'Alfred', 'dummy@gmail.com', '$2y$10$XMhGqBlLzsRWAom9FCEfjuxrcaiRzEJIVYT2GsfNfFPMnOGfGCPDi', NULL, NULL, 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books_db`
--
ALTER TABLE `books_db`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transactions_user` (`user_id`),
  ADD KEY `fk_transactions_book` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books_db`
--
ALTER TABLE `books_db`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_book` FOREIGN KEY (`book_id`) REFERENCES `books_db` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
