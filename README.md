Task Management System API
Overview
This Task Management System API is designed to streamline task management for teams, providing a flexible and scalable solution. The system features roles, permissions, task dependencies, and the ability to handle comments and attachments. It also supports advanced task tracking, soft deletes, task restoration, real-time notifications, and task status updates. The API is built using Laravel and focuses on efficient performance, security, and ease of use.

Features
1. Roles & Permissions
Admin Role: Only admins can create, update, and delete tasks.
User Role: A user assigned to a task can update its status.
Commenting: Users not assigned to a task can still leave comments on it.
Role and permission management is manually implemented with custom migrations and policies (no external packages like Spatie).
2. Task Management
CRUD Operations: Tasks can be created, updated, viewed, and deleted by authorized users.
Task Dependencies: Tasks can depend on other tasks and show related dependencies.
Task Status Updates: Keep track of task status changes, with a separate log of changes.
3. Soft Delete & Restore
Soft Delete: Tasks, attachments, and comments can be soft-deleted and later restored.
Task Restoration: Admins can restore soft-deleted tasks, preserving all associated data (comments, attachments).
4. File Attachments
Polymorphic Relationships: Attachments are linked to tasks using a polymorphic relationship.
Virus Scanning: Files uploaded to the system are scanned using the VirusTotal API to ensure they are safe.
MIME Type Validation: Only allowed file types can be uploaded (PDF, Word, Excel, Images, etc.).
5. Comments
Users can comment on tasks, even if they are not assigned to the task.
6. Daily Task Report Generation
A job runs periodically to generate daily reports for completed tasks, including related dependencies.
Reports are stored in the database, and admins can retrieve them.
7. Advanced Error Handling
All API requests are wrapped in try-catch blocks to handle errors gracefully.
Error logs are stored for future debugging and troubleshooting.
A global middleware ensures consistent logging for all requests.
8. Caching
Caching is implemented to improve performance for tasks listing and filtering.
Tasks are cached based on filters and pagination to minimize database queries.
9. Database Indexing
Indexing is implemented on key columns (like status, priority, and assigned_to) to improve the performance of search and filter queries.
Setup
-Requirements
-PHP 8.x
-Laravel 10.x
-MySQL
-Composer
-VirusTotal API Key (for file scanning)


Installation
1- Clone the repository:
git clone https://github.com/hamzaIssa254/Security-Task.git.

2-Navigate to the project directory:
cd task-management-api.

3-Install dependencies:
composer install

4-Set up the .env file:
cp .env.example .env

Configure the database and API keys (especially for VirusTotal) in the .env file.
5-php artisan key:generate

6-Run the migrations:
php artisan migrate

7-Start the application:
php artisan serve


Security
-Role-Based Access Control (RBAC): Policies are used to control user actions based on their roles.
-Virus Scanning: All file uploads are scanned using the VirusTotal API to ensure they are secure.

https://documenter.getpostman.com/view/34383133/2sAXxV7WJs

License
This project is licensed under the MIT License.
