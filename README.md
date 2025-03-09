# Online Quiz - Laravel Installation Guide

Follow these steps to set up and run the Online Quiz project locally.

## Prerequisites
Before you begin, ensure you have the following installed:
- Laravel 12 requires PHP 8.2.0 or greater
- Composer
- Laravel 12
- MySQL (or any supported database)
- Node.js & npm
- Git
- XAMPP or Laragon (if using Windows for local development)

## Installation Steps

### 1. Clone the Repository
Open a terminal and navigate to your project folder. If you are using **XAMPP**, run:

```sh
cd c:/xampp/htdocs
git clone https://github.com/arshadameenka/online-quiz.git quiz
cd quiz
```

### 2. Install PHP Dependencies
Run the following command to install all required PHP dependencies:

```sh
composer install
```

### 3. Configure Environment Variables
Copy the `.env.example` to .env file :

```sh
cp .env.example .env
```
add Database details in .env file

```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_quiz
DB_USERNAME=root
DB_PASSWORD=
```
update APP_NAME in .env file 
```sh
APP_NAME=Online_Quiz
```
### 4. Generate Application Key
Run the following command:

```sh
php artisan key:generate
```

### 5. Set Up the Database
Ensure **MySQL** is running. Run the migration command to create database tables:

```sh
php artisan migrate
```

If the database does not exist, Laravel will prompt:

```
WARN  The database 'online_quiz' does not exist on the 'mysql' connection.  
Would you like to create it? (yes/no) [yes]
```

Type `yes` and press **Enter** to create the database automatically.

### 6. Install Frontend Dependencies
Run the following command:

```sh
npm install
```

### 7. Build and Run the Project
Run the following command to build and start the development server:

```sh
composer run dev
```

Your project should now be running at:

```
http://127.0.0.1:8000
```

Open your browser and go to [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## Additional Commands

- **To clear cache**:
  ```sh
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```
- **To reset and seed the database**:
  ```sh
  php artisan migrate:fresh --seed
  ```
- **To serve Laravel manually**:
  ```sh
  php artisan serve
  ```
- **To build assets for production**:
  ```sh
  npm run build
  ```

## License
This project is open-source and available under the [MIT License](LICENSE).

## Contact
For any issues or support, feel free to reach out:

📩 Email: [arshad.ka5@gmail.com](mailto:arshad.ka5@gmail.com)  
🌐 GitHub: [GitHub Profile](https://github.com/arshadameenka)
