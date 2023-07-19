<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Chat;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Personality;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		return Inertia::render('Chat');
	}

	public function sendMessage(Request $request)
	{
		try {

			// Validate request data
			$validator = Validator::make($request->all(), [
				'message' => 'required', // Ensures that the message field is not empty
				'conversation_id' => 'required' // conversation_id is also required
			]);

			// If validation fails, return the validation errors
			if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
			}

			$message = $request->get('message');
			$conversationId = $request->get('conversation_id');

			// Check if the message is empty or null
			if (empty($message)) {
				throw new \Exception('Empty message');
			}

			// Save the user's message
			$userChat = new Chat();
			$userChat->message = $message;
			$userChat->is_user = true;
			$userChat->conversation_id = $conversationId;
			Auth::user()->chats()->save($userChat);

			// Fetch personality from the conversation
			$personality = DB::table('conversations')
				->join('personalities', 'conversations.personality_id', '=', 'personalities.id')
				->where('conversations.id', $conversationId)
				->select('personalities.system_message')
				->first();

			$model = env('OPENAI_MODEL');

			// Make a request to the OpenAI model
			$response = Http::withOptions(['timeout' => 30])->withHeaders([
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
			])->post('https://api.openai.com/v1/chat/completions', [
				'stream' => false,
				'model' => $model,
				'messages' => [
					["role" => "system", "content" => $personality->system_message],
					["role" => "user", "content" => $message],
				],
			]);

			$responseData = $response->json();
			// Log::info('OpenAI API response:', ['responseData' => $responseData]); // Log the response data

			if (!isset($responseData['choices'][0]['message']['content'])) {
				throw new \Exception('Invalid response structure');
			}

			// Save the OpenAI's response
			$aiChat = new Chat();
			$aiChat->message = $responseData['choices'][0]['message']['content'];
			$aiChat->is_user = false;
			$aiChat->conversation_id = $conversationId;
			Auth::user()->chats()->save($aiChat);

			$messageCount = $this->getMessageCount($conversationId)->original['count'];

			return response()->json([
				'reply' => $responseData['choices'][0]['message']['content'],
				'messageCount' => $messageCount,
			]);
		} catch (\Exception $e) {
			// Log the exception
			// Log::error($e);

			// Return an error response
			return response()->json(['error' => 'An error occurred. Please try again.'], 500);
		}
	}

    /**
     * @param Request $request
     * @return JsonResponse
     */
	public function newConversation(Request $request): JsonResponse
    {

		$validator = Validator::make($request->all(), [
			'title' => 'required|max:35',
			'personality_id' => 'required|exists:personalities,id',
		]);

		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		try {
			$user = User::find(auth()->id());

			$conv = new Conversation();
			$conv->user_id = $user->id;
			$conv->title = $request->title;
			$conv->personality_id = $request->input('personality_id');
			$conv->save();

			$latest = $user->conversations()->latest()->first();

			return response()->json(['latestConversation' => $latest]);
		} catch (Exception $e) {
			return response()->json(['errors' => ['message' => $e->getMessage()]], 500);
		}
	}

    /**
     * @return JsonResponse
     */
	public function getConversations(): JsonResponse
    {
		$user = User::find(auth()->id());
		$conversations = Conversation::with('personality')
			->where('user_id', auth()->id())
			->withCount('chats as message_count')
			->orderBy('created_at', 'DESC')
			->get();

		return response()->json(['allConversations' => $conversations]);
	}

	public function getMessagesForConversation(Request $request, $conversationId): JsonResponse
    {
		try {
			$conversation = Conversation::with('personality')->find($conversationId);

			// Check if the conversation belongs to the authenticated user
			if ($conversation && $conversation->user_id === auth()->id()) {
				//$chats = $conversation->chats()->orderBy('created_at', 'ASC')->get();
				$chats = $conversation->chats()->with('conversation.personality')->orderBy('created_at', 'ASC')->get();

				$formattedChats = $chats->map(function ($chat) {
					return [
						'message' => $chat->message,
						'isUser' => (bool) $chat->is_user,
						'created_at' => $chat->created_at,
						'personality_name' => $chat->conversation->personality->name
					];
				});

				return response()->json(['chats' => $formattedChats]);
			} else {
				return response()->json(['error' => 'Conversation not found or not authorized'], 404);
			}
		} catch (\Exception $e) {
			// Log the exception
			// Log::error($e);

			// Return an error response
			return response()->json(['error' => 'An error occurred. Please try again.'], 500);
		}
	}

	public function getMessageCount(int $conversationId): JsonResponse
    {
		try {
			$count = Chat::where('conversation_id', $conversationId)->count();
			return response()->json(['count' => $count]);
		} catch (\Exception $e) {
			// Log the exception
			// Log::error($e);

			// Return an error response
			return response()->json(['error' => 'An error occurred. Please try again.'], 500);
		}
	}

    /**
     * @return JsonResponse
     */
	public function getPersonalities(): JsonResponse
    {
		$personalities = Personality::all();
		return response()->json(['personalities' => $personalities]);
	}
}
