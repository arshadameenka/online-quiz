<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

class QuizController extends Controller
{
    protected $api = "https://the-trivia-api.com/api";
    public function index(Request $request)
    {
        $apiUrl = $this->api."/categories";
        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $categories = collect($response->json())->keys(); // Get main category names

            $perPage = 6; // Categories per page
            $currentPage = request('page', 1);
            $pagedData = $categories->forPage($currentPage, $perPage);
            $total = $categories->count();

            return view('quiz.index', [
                'categories' => new LengthAwarePaginator($pagedData, $total, $perPage, $currentPage, [
                    'path' => route('quiz.index')
                ])
            ]);
        }

        return back()->with('error', 'Failed to fetch categories');
    }

    public function getQuestions($category)
    {
        $encodedCategory = urlencode($category); // Encode category name for API call
        $apiUrl = $this->api."/questions?categories={$encodedCategory}&limit=15";
        $response = Http::get($apiUrl);
    
        if ($response->successful()) {
            $questions = $response->json();
            logger($questions);
            $formattedQuestions = collect($questions)->map(function ($question) {
                $choices = collect(array_merge($question['incorrectAnswers'], [$question['correctAnswer']]))->shuffle();
               // we modify the response array. We only need the question, id, and shuffled choices. 
               // user can view correct_answer by inspecting the response.
               // So We removed the correct_answer key from the response array.
                return [
                    'id'       => $question['id'],
                    'question' => $question['question'],
                    'choices'  => $choices->values()->all(), // Only send shuffled choices
                ];
            });
    
            return response()->json($formattedQuestions);
        }
    
        return response()->json(['error' => 'Failed to fetch questions'], 500);
    }
    public function checkAnswer(Request $request)
{
    $questionId = $request->question_id;
   

    // Fetch question from API to verify correct answer
    $apiUrl = $this->api."/question/{$questionId}";
    $response = Http::get($apiUrl);

    if ($response->successful()) {
        $question = $response->json();
       

        return response()->json(['correctAnswer' => $question['correctAnswer']]);
    }

    return response()->json(['error' => 'Invalid question'], 500);
}

    
}
