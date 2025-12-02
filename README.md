# Smart Laptop Advisor (FYP)

## Project Overview
**Smart Laptop Advisor** is a web-based application designed to help users find the perfect laptop based on their specific needs and personas (e.g., Student, Gamer, Business Professional). It features an intelligent recommendation engine, a chatbot for interactive assistance, and a comprehensive admin panel for managing products, orders, and system analytics.

## Key Features

### User Frontend (`/LaptopAdvisor`)
- **AI Recommendation Engine**: Suggests laptops based on user personas and usage patterns.
- **Interactive Chatbot**: Provides real-time assistance and answers queries about laptops.
- **Product Catalog**: Browse and filter laptops by brand, category, and specifications.
- **Product Comparison**: Compare multiple laptops side-by-side.
- **User Accounts**: Manage profiles, view order history, and track shipping.
- **Shopping Cart & Checkout**: Secure checkout process with voucher support.

### Admin Panel (`/admin`)
- **Dashboard**: Real-time analytics on revenue, orders, users, and AI performance.
- **Product Management**: Add, edit, delete, and bulk upload products via CSV.
- **Order Management**: View, process, and update order statuses.
- **User Management**: Manage customer profiles and statuses.
- **System Logs**: Comprehensive activity logging for all admin actions.
- **Reports**: Generate and export PDF/CSV reports for sales, products, and AI performance.
- **AI Configuration**: Fine-tune AI weightage and manage chatbot intents.

## Technology Stack
- **Backend**: PHP (Native), MySQL
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Database**: MySQL
- **Libraries**:
    - **ApexCharts**: For interactive data visualization.
    - **jsPDF & html2canvas**: For generating PDF reports.
    - **PHPMailer**: For email notifications (password resets, etc.).

## Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/swiftscout98/smart-laptop-advisor.git
    ```

2.  **Database Setup**
    - Import the `laptop_advisor_db.sql` file into your MySQL database.
    - Update database credentials in:
        - `admin/includes/db_connect.php`
        - `LaptopAdvisor/includes/db_connect.php` (if applicable)

3.  **Run the Application**
    - Host the project on a local server (e.g., XAMPP, WAMP).
    - Access the frontend: `http://localhost/fyp/LaptopAdvisor`
    - Access the admin panel: `http://localhost/fyp/admin`

## Folder Structure
- `admin/`: Admin panel source code.
- `LaptopAdvisor/`: User-facing frontend application.
- `api/`: API endpoints for chatbot and other services.
- `source/`: Shared assets and libraries.

## License
This project is for educational purposes as part of a Final Year Project.
