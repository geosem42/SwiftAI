<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Speech;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Transcription;
use Illuminate\Support\Facades\Validator;
use Exception;

class SpeechController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		return Inertia::render('TextToSpeech');
	}

	public function getVoices()
	{
		$azureResourceRegion = env('AZURE_RESOURCE_REGION');
		$subscriptionKey = env('AZURE_RESOURCE_KEY');

		try {
			$response = Http::withHeaders([
				'Ocp-Apim-Subscription-Key' => $subscriptionKey,
			])->get("https://{$azureResourceRegion}.tts.speech.microsoft.com/cognitiveservices/voices/list");

			if ($response->successful()) {
				return response()->json($response->json());
			} else {
				// Log the response if it's not successful
				Log::error('Azure API error:', ['response' => $response->body()]);
				return response()->json(['error' => 'Failed to fetch data from Azure.'], 500);
			}
		} catch (\Exception $e) {
			// Log any exception
			Log::error('Exception occurred:', ['exception' => $e->getMessage()]);
			return response()->json(['error' => 'An error occurred while fetching data.'], 500);
		}
	}

	public function generateAudio(Request $request)
	{
		$resourceRegion = env('AZURE_RESOURCE_REGION'); // Update this with your resource region
		$subscriptionKey = env('AZURE_RESOURCE_KEY'); // Update this with your subscription key

		$validator = Validator::make($request->all(), [
			'message' => 'required',
			'voice' => 'required',
			'language' => 'required'
		]);

		if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
		}

		try {
			$language = $request->input('language');
			$voice = $request->input('voice');
			$message = $request->input('message');

			$url = "https://{$resourceRegion}.tts.speech.microsoft.com/cognitiveservices/v1";
			$ssml = "<speak version='1.0' xml:lang='{$language}'><voice xml:lang='{$language}' name='{$voice}'>{$message}</voice></speak>";

			// Ensure that the SSML string is UTF-8 encoded
			$ssml = mb_convert_encoding($ssml, 'UTF-8');

			$response = Http::withHeaders([
				'Content-Type' => 'application/ssml+xml',
				'Accept' => 'application/x-www-form-urlencoded',
				'X-Microsoft-OutputFormat' => 'audio-16khz-128kbitrate-mono-mp3',
				'User-Agent' => 'YourAppName',
				'Ocp-Apim-Subscription-Key' => $subscriptionKey,
			])->withBody($ssml, 'application/ssml+xml')->post($url);

			$audioContent = $response->body();

			// Defining the file name and path to save the audio file.
			$user_id = auth()->user()->id;
			$random_name = Str::random(12);
			$fileName = "{$user_id}/{$random_name}.mp3";
			$filePath = storage_path('app/public/audio/' . $fileName);

			// Make sure the directory exists
			if (!File::exists(dirname($filePath))) {
				File::makeDirectory(dirname($filePath), 0777, true);
			}

			// Saving the audio content as a file.
			file_put_contents($filePath, $audioContent);

			// Saving information to the database.
			$audio = new Speech();
			$audio->user_id = auth()->user()->id;
			$audio->voice = $voice;
			$audio->language = $language;
			$audio->message = $message;
			$audio->filename = $fileName;
			$audio->save();

			// Check if the request was successful
			if ($response->successful()) {
				// At this point the file should be saved at the specified path.
				// You can then send a response back to the client, for example:
				return response()->json(['message' => 'Speech generated successfully.', 'file' => $fileName]);
			} else {
				return response()->json(['error' => $response->body()], 500);
			}
		} catch (\Exception $e) {
			// Log any exception
			//Log::error('Exception occurred:', ['exception' => $e->getMessage()]);
			return response()->json(['errors' => ['message' => $e->getMessage()]], 500);
		}
	}

	public function fetchAudioFiles(Request $request)
	{
		try {

			$audioFiles = $request->user()->speeches()->orderBy('created_at', 'DESC')->paginate(10);

			return response()->json($audioFiles);

		} catch (Exception $e) {
			return response()->json(['errors' => ['message' => $e->getMessage()]], 500);
		}
	}

	public function speechToText()
	{
		return Inertia::render('SpeechToText');
	}

	public function transcribe(Request $request)
	{
		$validator = Validator::make($request->all(), [
				'file' => 'required|file|mimes:mp3|max:25000',
		]);

		if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
		}

		try {
			$file = $request->file('file');

			if (!$file) {
				return response()->json(['file' => ['No file was selected.']], 422);
			}

			$originalName = $file->getClientOriginalName();
			$uniqueName = substr(uniqid(rand(), true), 0, 12) . '.' . $file->getClientOriginalExtension();

			$filePath = storage_path("app/public/transcriptions/{$uniqueName}");
			$file->storeAs('transcriptions', $uniqueName);

			$openaiApiKey = env('OPENAI_API_KEY');
			$response = Http::asMultipart()->withHeaders([
				'Authorization' => "Bearer $openaiApiKey",
			])->post('https://api.openai.com/v1/audio/transcriptions', [
				[
					'name' => 'file',
					'contents' => fopen($filePath, 'r'),
				],
				[
					'name' => 'model',
					'contents' => 'whisper-1',
				]
			]);

			if ($response->successful()) {
				$transcription = new Transcription();
				$transcription->file = $file->getClientOriginalName();
				$transcription->unique_name = $uniqueName;
				$transcription->text = $response->json()['text'];
				$transcription->user_id = auth()->id();
				$transcription->save();

				return response()->json($transcription, 201);
			} else {
				return response()->json(['error' => 'No file was selected.'], 422);
			}
		} catch (Exception $e) {
			// Return a 500 error with the exception message
			return response()->json(['errors' => ['message' => $e->getMessage()]], 500);
		}
	}

	public function getTranscriptions(Request $request)
	{
		try {
			// Fetch the records from the database
			$transcriptions = $request->user()->transcriptions()->orderBy('created_at', 'DESC')->paginate(10);

			// Return the records as JSON
			return response()->json($transcriptions);

		} catch (Exception $e) {
			// Return a 500 error with the exception message
			return response()->json(['errors' => ['message' => $e->getMessage()]], 500);
		}
	}
}
