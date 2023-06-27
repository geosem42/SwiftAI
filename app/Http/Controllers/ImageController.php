<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use CURLFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ImageController extends Controller
{
	public function home()
	{
		return Inertia::render('Home', [
			'canLogin' => Route::has('login'),
			'canRegister' => Route::has('register'),
			'laravelVersion' => Application::VERSION,
			'phpVersion' => PHP_VERSION,
		]);
	}

	public function index()
	{
		return Inertia::render('Images');
	}

	public function store(Request $request)
	{
		if (empty(env('STABILITY_API_KEY'))) {
			echo "STABILITY_API_KEY environment variable is not set";
			exit(1);
		}

		$validator = Validator::make($request->all(), [
			'prompt' => 'required|string'
		]);

		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		try {
			$baseUrl = env('API_HOST', 'https://api.stability.ai');
			$model = env('STABILITY_MODEL');
			$model_upscale = env('STABILITY_MODEL_UPSCALE');
			$url = "$baseUrl/v1/generation/{$model}/text-to-image";
			$upscale_url = "$baseUrl/v1/generation/{$model_upscale}/image-to-image/upscale";

			$prompt = $request->get('prompt');
			$style = $request->get('style');

			$userId = auth()->user()->id;
			$filename = Str::random(12);
			$filepath = "images/{$userId}/{$filename}.png";
			$filepath_upscaled = "images/{$userId}/{$filename}_upscaled.png";
			$width = $request->get('width');
			$height = $request->get('height');

			$this->generateImage($url, $filepath, $prompt, $style, $width, $height);

			$image = new Image();
			$image->user_id = $request->user()->id;
			$image->prompt = $request->prompt;
			$image->original = $filepath;

			// Check if the upscale option was selected by the user
			if ($request->input('upscale')) {
				$this->upscaleImage($filename, $upscale_url, $userId, $width, $height);
				$image->upscaled = $filepath_upscaled;
			}

			// Wait for file to be available
			sleep(1); // wait for a second or more if needed

			$fullPath = storage_path("app/public/{$filepath}");

			if (file_exists($fullPath)) {
				$resolution = getimagesize($fullPath);
				$image->width = $resolution[0];
				$image->height = $resolution[1];
			} else {
				//Log::error("File does not exist: {$fullPath}");
				return response()->json(['errors' => ['message' => "File does not exist: {$fullPath}"]], 500);
			}

			$image->save();

			// Return the image data as JSON
			return response()->json([
				'prompt' => $request->prompt,
				'original' => $filepath,
				'upscaled' => $request->input('upscale') ? $filepath_upscaled : null,
				'width' => $resolution[0],
				'height' => $resolution[1],
			]);
		} catch (Exception $e) {
			return response()->json(['errors' => ['message' => $e->getMessage()]], 500);
		}
	}

	public function generateImage($url, $outputFile, $prompt, $style, $width, $height)
	{
		try {
			$response = Http::withHeaders([
				'Content-Type' => 'application/json',
				'Accept' => 'image/png',
				'Authorization' => 'Bearer ' . env('STABILITY_API_KEY')
			])->post($url, [
				'text_prompts' => [
					[
						'text' => $prompt
					]
				],
				'cfg_scale' => 7,
				'clip_guidance_preset' => 'FAST_BLUE',
				'height' => $height,
				'width' => $width,
				'samples' => 1,
				'steps' => 50,
				'style_preset' => $style
			]);

			if ($response->successful()) {
				Storage::disk('public')->makeDirectory(dirname($outputFile));
				Storage::disk('public')->put($outputFile, $response->body());
				//Log::info('Image successfully generated and saved to ' . $outputFile);
			} else {
				//Log::error('Error in generating image: ' . $response->status() . ' - ' . $response->body());
				throw new Exception('Error in generating image: ' . $response->status());
			}
		} catch (Exception  $e) {
			//Log::error($e->getMessage());
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function upscaleImage($filename, $upscale_url, $userId, $width, $height)
	{
		try {
			$upscaled_file = storage_path("app/public/images/{$userId}/{$filename}_upscaled.png");

			$file = storage_path("app/public/images/{$userId}/{$filename}.png");

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $upscale_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: multipart/form-data',
				'Accept: image/png',
				'Authorization: Bearer ' . env('STABILITY_API_KEY')
			));

			$postFields = array(
				'image' => new CURLFile($file),
				'width' => $width == 512 && $height == 512 ? '2048' : ''
			);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

			$result = curl_exec($ch);

			file_put_contents($upscaled_file, $result);

			curl_close($ch);
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function getImages(Request $request)
	{
		try {
			$perPage = 10;

			$user = User::find(auth()->id());
			$images = $user->images()->orderBy('created_at', 'DESC')->paginate($perPage);

			return response()->json($images);
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}
}
