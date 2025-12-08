# Library Management System

---

## 📖 Overview

A simple **Library Management System** built for academic environments.  
It provides a **centralized platform** for students and administrators to manage book borrowing, returns, and inventory.

The system is designed to:

- Keep track of **users, books, and transactions** within a structured database  
- Simplify the process of **borrowing and returning books**  
- Give admins clear tools to **manage the catalog and monitor usage**

---

## ✨ Features

| Student                                              | Admin                                                      |
|------------------------------------------------------|------------------------------------------------------------|
| Register and log in                                  | Log in as admin                                            |
| Browse and search available books                    | Add, edit, and delete books                               |
| View book details and stock availability             | View all users and their borrowing history                |
| Request to borrow books                              | Approve / reject borrow requests                          |
| View request status (pending, borrowed, etc.)        | Issue books, set due dates, and mark returns              |
| See personal borrowing history                       | Mark transactions as returned or rejected                |
| Receive updates when requests are approved/rejected  | System automatically flags overdue transactions          |


---

## 🗃 Data Model (ERD Summary)

The system uses three main tables:

- **`users`** – stores students/admins with role-based access  
- **`books_db`** – stores book data and quantity  
- **`transactions`** – logs each borrow/return request with status and dates  

This structure keeps the borrowing workflow **traceable, auditable, and easy to extend**.
