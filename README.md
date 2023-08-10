# Swift AI
Swift AI is a web application built using Laravel, Inertia.js, and Vue.js. It serves as a centralized hub that seamlessly integrates various AI tools and services from companies like OpenAI, Microsoft, and Stability AI. The platform aims to streamline the usage of diverse AI solutions, providing users with a cohesive experience.

 # Features
 1. AI Chat (OpenAI)
 2. AI Image Generation (Stability AI)
 3. AI Documents (OpenAI)
 4. Text To Speech (Microsoft Azure)
 5. Speech To Text (OpenAI)

 # Installation
Grab a fresh `.env` file from [Laravel](https://github.com/laravel/laravel/blob/master/.env.example) and save the following:
``` 
APP_NAME=SwiftAI Example
APP_ENV=local
APP_KEY=
```
Generate a new App Key
```
php artisan key:generate 
```
Link the image directory
```
php artisan storage:link
```
Install PHP dependencies
```
composer install
```
Install node packages
```
npm install
```
Migrate the database
```
php artisan migrate
```
Seed the `personalities` table
```
php artisan db:seed --class=PersonalitiesTableSeeder
```
Add the following variables to your `.env` file and add your keys. You can obtain your keys from [OpenAI](https://platform.openai.com/account/api-keys), [Stability AI](https://beta.dreamstudio.ai/account), and [Microsoft Azure](https://portal.azure.com)
```
STABILITY_API_KEY=
STABILITY_MODEL=stable-diffusion-xl-beta-v2-2-2
STABILITY_MODEL_UPSCALE=esrgan-v1-x2plus

OPENAI_API_KEY=
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MODEL_EMBEDDING=text-embedding-ada-002
OPENAI_MODEL_QA=gpt-3.5-turbo-16k

AZURE_RESOURCE_REGION=eastus
AZURE_RESOURCE_KEY=
```
Run the server
```
php artisan serve
```
Build assets
```
npm run build

## OR

npm run watch
```

# Screenshots
![Create New Conversation in AI Chat](https://i.imgur.com/Gshd2Ii.png)
![AI Chat](https://i.imgur.com/NMpUcrI.png)
![AI Images](https://i.imgur.com/D27vnEs.png)
![AI Documents](https://i.imgur.com/dB8APxa.png)
![Q&A in AI Documents](https://i.imgur.com/CKiSCoK.png)
![Text To Speech](https://i.imgur.com/mYx497Q.png)
![Speech To Text](https://i.imgur.com/vyfm8Hc.png)

# Thanks
- [awesome-chatgpt-prompts](https://github.com/f/awesome-chatgpt-prompts)
- [StartBootstrap](https://github.com/StartBootstrap/startbootstrap-sb-admin)
