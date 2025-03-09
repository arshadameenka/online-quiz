<?php
namespace App\Http\Controllers;

use Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Redirect;

class QuizController extends Controller
{
    protected $api = "https://the-trivia-api.com/api";
    public function index(Request $request)
    {
        
            $apiUrl = $this->api . "/categories";
            $response = Http::get($apiUrl);
           
            if ($response->successful() && !empty($response->json())) {
                $categories = collect($response->json())->keys(); // Get main category names

                $perPage = 6; // Categories per page
                $currentPage = max(1, (int) request('page', 1));
                $pagedData = $categories->forPage($currentPage, $perPage);
                $total = $categories->count();

                return view('quiz.index', [
                    'categories' => new LengthAwarePaginator($pagedData, $total, $perPage, $currentPage, [
                        'path' => route('quiz.index')
                    ])
                ]);
            }
            else
            {
                abort(response()->view('errors.404', ['message' => 'An error occured while fetching categories'], 404));
            }
    }

    public function getQuestions($category)
    {
        $encodedCategory = urlencode($category); // Encode category name for API call
        $apiUrl = $this->api . "/questions?categories={$encodedCategory}&limit=15";
        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $questions = $response->json();
            logger($questions);
            $formattedQuestions = collect($questions)->map(function ($question) {
                $choices = array_merge($question['incorrectAnswers'], [$question['correctAnswer']]);
                shuffle($choices); // Optimize shuffling

                // Store correct answers temporarily in session
                session()->put("quiz_answer_{$question['id']}", $question['correctAnswer']);
                // we modify the response array. We only need the question, id, and shuffled choices. 
                // user can view correct_answer by inspecting the response.
                // So We removed the correct_answer key from the response array.
                return [
                    'id' => Crypt::encryptString($question['id']), //hashed for security
                    'question' => $question['question'],
                    'choices' => $choices, // Shuffled choices
                ];
            });

            return response()->json($formattedQuestions);
        }

        return response()->json(['error' => 'Failed to fetch questions'], 500);
    }
    public function checkAnswer(Request $request)
    {
        try {
            $questionId = Crypt::decryptString($request->question_id); // No need for decryption as we use hash


            // Retrieve stored correct answer from session
            $correctAnswer = session("quiz_answer_{$questionId}");
            if (!$correctAnswer) {
                return response()->json(['error' => 'Question not found or expired'], 400);
            }

            return response()->json(['correctAnswer' => $correctAnswer]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    }


}
