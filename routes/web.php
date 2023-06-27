<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SpeechController;
use App\Http\Controllers\DocumentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ImageController::class, 'home'])->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/images', [ImageController::class, 'index'])->name('images');
    Route::post('/generate-image', [ImageController::class, 'store'])->name('generate-image');
    Route::get('/get-images', [ImageController::class, 'getImages'])->name('get-images');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('send-message');
    Route::get('/get-messages/{conversationId}', [ChatController::class, 'getMessagesForConversation']);
    Route::post('/new-conversation', [ChatController::class, 'newConversation'])->name('new-conversation');
    Route::get('/get-conversations', [ChatController::class, 'getConversations']);
    Route::get('/get-messages/{currentConversationId}/message-count', [ChatController::class, 'getMessageCount']);
    Route::get('/personalities', [ChatController::class, 'getPersonalities']);
    Route::get('/texttospeech', [SpeechController::class, 'index'])->name('texttospeech');
    Route::get('/texttospeech/voices', [SpeechController::class, 'getVoices']);
    Route::post('/texttospeech/generate', [SpeechController::class, 'generateAudio']);
    Route::get('/texttospeech/audiofiles', [SpeechController::class, 'fetchAudioFiles']);
    Route::get('/document', [DocumentController::class, 'index'])->name('document');
    Route::post('/document/upload', [DocumentController::class, 'uploadDocument'])->name('uploadDocument');
    Route::get('/document/list', [DocumentController::class, 'listDocuments'])->name('listDocuments');
    Route::post('/document/{documentId}/chat', [DocumentController::class, 'chatWithDocument']);
    Route::delete('/document/{id}', [DocumentController::class, 'deleteDocument'])->name('deleteDocument');
    Route::post('/document/ask', [DocumentController::class, 'askQuesion']);
    Route::post('/document/search', [DocumentController::class, 'search']);
    Route::get('/document/history/{documentId}', [DocumentController::class, 'getChatHistory']);
    Route::get('/speechtotext', [SpeechController::class, 'speechToText'])->name('speechtotext');
    Route::post('/speechtotext/transcribe', [SpeechController::class, 'transcribe']);
    Route::get('/speechtotext/transcriptions', [SpeechController::class, 'getTranscriptions']);

    Route::get('/csrf-token', function () {
        return response()->json(['csrfToken' => csrf_token()]);
    });
});