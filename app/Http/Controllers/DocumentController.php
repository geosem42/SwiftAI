<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Document;
use App\Models\DocumentEmbedding;
use App\Models\Question;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Smalot\PdfParser\Parser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Rubix\ML\Kernels\Distance\Cosine;
use Exception;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{

	public function index()
	{
		return Inertia::render('Document');
	}

	public function uploadDocument(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'file' => 'required|file|mimes:pdf|max:25000',
		]);

		if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
		}

		try {
			if ($request->hasFile('file')) {
				$file = $request->file('file');

				// Getting the user ID
				$userId = auth()->user()->id;

				// Creating custom file path
				$customFilePath = "documents/{$userId}";

				// Storing the file
				$storedFilePath = $file->store($customFilePath, 'public');

				// Extract text from the PDF
				$parser = new Parser();
				$pdfAbsolutePath = Storage::disk('public')->path($storedFilePath);
				$pdf = $parser->parseFile($pdfAbsolutePath);
				$text = $pdf->getText();

				// Logging the extracted text to ensure it works
				// Log::info('Extracted text from PDF:', ['text' => $text]);

				$httpClient = new Client(['base_uri' => 'https://api.openai.com']);

				$chunksForEmbeddings = $this->splitTextForEmbeddings($text);

				$combinedEmbeddings = [];
				foreach ($chunksForEmbeddings as $chunk) {
					$response = $httpClient->post('/v1/embeddings', [
						'headers' => [
							'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
							'Content-Type' => 'application/json',
						],
						'json' => [
							'input' => $chunk,
							'model' => env('OPENAI_MODEL_EMBEDDING'),
						],
					]);

					$embeddings = json_decode($response->getBody(), true);
					$combinedEmbeddings[] = $embeddings;
				}

				// Logging the embeddings to ensure it works
				//Log::info('Combined Embeddings from OpenAI:', $combinedEmbeddings);

				// Saving file information in the database
				$document = new Document(); // Assuming your model name is Document
				$document->user_id = $userId;
				$document->file_name = $file->getClientOriginalName();
				$document->parsed_text = $text;
				$document->save();

				// Store the embeddings in the database
				$embeddingsRecord = new DocumentEmbedding();
				$embeddingsRecord->document_id = $document->id; // ID of the document
				$embeddingsRecord->embeddings = json_encode($combinedEmbeddings); // Embeddings array
				$embeddingsRecord->save();

				return response()->json([
					'message' => 'File successfully uploaded and saved in database.',
					'document' => [
						'id' => $document->id,
						'user_id' => $document->user_id,
						'file_name' => $file->getClientOriginalName(),
						'created_at' => $document->created_at
					]
				]);
			} else {
				return response()->json(['message' => 'No file was selected.'], 400);
			}
		} catch (\Exception $e) {
			// Log any exception
			//Log::error('Exception occurred:', ['exception' => $e->getMessage()]);
			return response()->json(['errors' => ['message' => $e->getMessage()]], 500);
		}
	}

	public function listDocuments(Request $request)
	{
		try {
			$userId = auth()->user()->id;
			$documents = Document::where('user_id', $userId)->orderBy('created_at', 'DESC')->paginate(10);

			return response()->json($documents);
		} catch (\Exception $e) {
			Log::error('Exception occurred:', ['exception' => $e->getMessage()]);
			return response()->json(['error' => 'An error occurred while fetching documents.'], 500);
		}
	}

	public function chatWithDocument(Request $request, $documentId)
	{
		try {
			// Retrieve the embeddings associated with the document
			$embeddings = DocumentEmbedding::where('document_id', $documentId)->get();

			// TODO: Send message to OpenAI with the embeddings and return the response

		} catch (\Exception $e) {
			Log::error('Exception occurred:', ['exception' => $e->getMessage()]);
			return response()->json(['error' => 'An error occurred while chatting with the document.'], 500);
		}
	}

	public function deleteDocument($id)
	{
		try {
			$document = Document::find($id);
			$document->delete();
			return response()->json([
				'success' => 'Document deleted successfully.',
			], Response::HTTP_OK);
		} catch (Exception $e) {
			// Log the exception message
			Log::error($e->getMessage());

			return response()->json([
				'error' => 'An error occurred while trying to delete the document.',
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	public function search(Request $request)
	{
		$userQuery = $request->input('query');
		$documentId = $request->input('document_id');
		$userId = auth()->user()->id;
		//Log::info("Received document ID: $documentId");

		// Step 1: Convert user query to embeddings using OpenAI API
		$userQueryEmbedding = $this->convertToEmbedding($userQuery);

		// Step 2: Fetch document embeddings from the database
		$document = Document::with('documentEmbeddings')->where('id', $documentId)->first();

		if ($document === null) {
			// Handle the case when the document is not found
			return response()->json(['error' => 'Document not found']);
		}

		// Step 3: Calculate cosine similarity
		$cosine = new Cosine();
		$scores = [];
		foreach ($document->documentEmbeddings as $embeddingModel) {
			// Retrieve the embedding from the related document_embedding model
			$documentEmbedding = json_decode($embeddingModel->embeddings, true);
			if (is_array($documentEmbedding)) {
				$documentEmbedding = array_map('floatval', $documentEmbedding);
			} else {
				continue; // Skip if the embedding format is invalid
			}

			// Also ensure that userQueryEmbedding is an array of numbers
			if (is_array($userQueryEmbedding)) {
				$userQueryEmbedding = array_map('floatval', $userQueryEmbedding);
			} else {
				// Handle error - userQueryEmbedding format is invalid
				return response()->json(['error' => 'Invalid user query embedding format']);
			}

			$similarity = $cosine->compute($documentEmbedding, $userQueryEmbedding);
			$scores[] = ['document' => $document, 'similarity' => $similarity];
		}

		// Sort documents based on similarity scores
		usort($scores, function ($a, $b) {
			return $b['similarity'] <=> $a['similarity'];
		});

		// Step 4: Send the most relevant document and user query to OpenAI for question answering
		$mostRelevantDocument = $scores[0]['document']->parsed_text;
		$mostRelevantDocumentId = $scores[0]['document']->id;
		$answer = $this->getAnswerFromGPT($mostRelevantDocument, $userQuery, $mostRelevantDocumentId);

		//Log::info('OpenAI Response:', ['response' => $answer]);
		$question = new Question();
		$question->document_id = $documentId;
		$question->user_id = $userId;
		$question->message = $userQuery;
		$question->isUser = true;
		$question->save();

		// Save AI response
		$response = new Question();
		$response->document_id = $documentId;
		$response->user_id = $userId;
		$response->message = $answer;
		$response->isUser = false;
		$response->save();

		// Step 5: Return the answer to the frontend
		return response()->json(['answer' => $answer]);
	}

	public function getChatHistory($documentId)
	{
		// Fetch chat history for the given documentId from the questions table
		$chatHistory = Question::where('document_id', $documentId)
			->orderBy('created_at', 'asc')
			->get(['message', 'isUser']);

		// Return the chat history as JSON
		return response()->json($chatHistory);
	}

	private function convertToEmbedding($text)
	{
		// Here, you should call OpenAI's API to convert the text to an embedding.

		$api_key = env('OPENAI_API_KEY'); // Assuming you've stored your API key in the .env file
		$api_url = "https://api.openai.com/v1/embeddings";
		$model_name = env('OPENAI_MODEL_EMBEDDING'); // Change this if you're using a different model

		try {
			$response = Http::withHeaders([
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $api_key
			])->post($api_url, [
				'input' => $text,
				'model' => $model_name
			]);

			// Log the response
			//Log::info('OpenAI Response:', $response->json());

			// Check if the response contains embeddings data
			if (isset($response['data']) && isset($response['data'][0]['embedding'])) {
				// Extract the embedding from the response data
				$embedding = $response['data'][0]['embedding'];
				return $embedding;
			} else {
				Log::warning('OpenAI Response does not contain embeddings data');
			}
		} catch (\Exception $e) {
			// Log the error
			Log::error('Error in convertToEmbedding:', ['error' => $e->getMessage()]);
		}

		return null;
	}

	private function getAnswerFromGPT($documentText, $query, $documentId)
	{
		// Split the text into chunks
		$chunks = $this->splitTextIntoChunks($documentText);

		// Loop through the chunks
		foreach ($chunks as $chunk) {
			// Get the answer using this chunk
			$answer = $this->getAnswerFromChunk($chunk, $query, $documentId);

			// If the answer is satisfactory, return it
			if ($this->isSatisfactoryAnswer($answer)) {
				return $answer;
			}
		}

		// Return a default answer if no satisfactory answer was found
		return "I'm sorry, but I couldn't find the information you are looking for in the document.";
	}

	private function getAnswerFromChunk($chunk, $query, $documentId)
	{
		// Set the OpenAI API key
		$apiKey = env('OPENAI_API_KEY');

		// Set the OpenAI API endpoint for GPT-3.5 Turbo
		$url = "https://api.openai.com/v1/chat/completions";

		// Prepare the messages to be sent to GPT-3.5 Turbo
		$messages = [
			[
				"role" => "system",
				"content" => "You are a helpful assistant."
			],
			[
				"role" => "assistant",
				"content" => "I am here to analyze text and answer questions based on it."
			],
			[
				"role" => "user",
				"content" => "Here is the text from the document: \"" . $chunk . "\". Can you answer this question based on the document text: \"" . $query . "\"?"
			]
		];

		// Data to be sent as the POST request body
		$postData = [
			'model' => env('OPENAI_MODEL_QA'),
			'messages' => $messages,
		];

		// Send a POST request to the OpenAI API
		$response = Http::withHeaders([
			'Authorization' => 'Bearer ' . $apiKey,
			'Content-Type' => 'application/json'
		])->post($url, $postData);

		// Decode the JSON response
		$responseData = $response->json();

		// Check if the response contains any errors
		if (isset($responseData['error'])) {
			Log::error('Error from OpenAI API:', ['error' => $responseData['error']]);
			return null;
		}

		// Extract the answer from the response data
		return $responseData['choices'][0]['message']['content'] ?? null;
	}

	private function isSatisfactoryAnswer($answer)
	{
		if ($answer && strpos($answer, 'not provided in the text') === false) {
			return true;
		}

		return false;
	}

	private function splitTextIntoChunks($text)
	{
		$MAX_TOKENS = 16384;
		// Assuming about 3 characters per token
		$MAX_CHAR_LENGTH = floor($MAX_TOKENS / 3);

		$chunks = [];
		$textLength = strlen($text);

		// Split the text into chunks based on character count, ensuring that
		// each chunk does not exceed the maximum number of tokens allowed.
		for ($i = 0; $i < $textLength; $i += $MAX_CHAR_LENGTH) {
			$chunks[] = substr($text, $i, $MAX_CHAR_LENGTH);
		}

		return $chunks;
	}


	private function splitTextForEmbeddings($text)
	{
		$MAX_TOKENS = 4000; // Set the max tokens to a conservative value for embeddings endpoint
		$averageWordLength = 5; // Assume an average word length of 5 characters
		$approxCharactersPerChunk = $MAX_TOKENS * $averageWordLength; // Approx. characters per chunk

		$chunks = [];
		$textLength = strlen($text);

		// Split the text into chunks based on character count, ensuring that
		// each chunk does not exceed the maximum number of tokens allowed.
		for ($i = 0; $i < $textLength; $i += $approxCharactersPerChunk) {
			$chunks[] = substr($text, $i, $approxCharactersPerChunk);
		}

		return $chunks;
	}
}
