# Swift AI
 Swift AI is a web application built using Laravel, Inertia.js, and Vue.js. It serves as a centralized hub that seamlessly integrates various AI tools and services from companies like OpenAI, Microsoft, and Stability AI. The platform aims to streamline the usage of diverse AI solutions, providing users with a cohesive experience.

 # Installation
Run the following commands:

Install PHP dependencies
`composer install`

Install node packages
`npm install`

Migrate the database
`php artisan migrate`

Seed the `personalities` table
`php artisan db:seed --class=PersonalitiesTableSeeder`

Run the server
`php artisan serve`

Build assets
`npm run vite build` or `npm run watch` for development.
