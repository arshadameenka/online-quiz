// This file contains the JavaScript code for the quiz functionality.
// It uses AJAX to fetch categories, questions, and check answers.
$(function () {
    let questions = [];
    let currentQuestionIndex = 0;
    let timer;
    let countdown = 90;
    let userAnswers = [];
    let correctAnswer = '';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // Fetch categories when clicking a pagination dot
    $('.pagination-dot').click(function () {
        let page = $(this).data('page');
        $.ajax({
            url: `${indexRoute}?page=${page}`,
            method: 'GET',
            success: function (response) {
                let newCategories = $(response).find('#categories').html();
                $('#categories').html(newCategories);

                $('.pagination-dot').removeClass('text-black');
                $(`.pagination-dot[data-page="${page}"]`).addClass('text-black');
            }
        });
    });

    // Fetch questions when clicking a category
    $(document).on('click', '.category-btn', function () {
        let categoryName = $(this).data('name');
        $.ajax({
            url: `${getQustionsRoute}${encodeURIComponent(categoryName)}`,
            method: 'GET',
            success: function (response) {
               
                if (response.length > 0) {
                    questions = response;
                    currentQuestionIndex = 0;
                    userAnswers = []; // Reset user answers for new quiz
                    $('#quiz-container').show();
                    $('#category-container').hide();
                    showQuestion();
                }
            },
            error: function(xhr, status, error) {
               // console.log(xhr.responseJSON?.error);
                
                let errorMessage = xhr.responseJSON?.error || "Something went wrong!";
                showError(errorMessage);
            }
        });
    });
    function showError(message) {
        $("#error-message").text(message).removeClass('hidden');
        $("#error-block").removeClass('hidden');
    }

    //show qustions
    function showQuestion() {
        if (currentQuestionIndex >= questions.length) {
            showSummary();
            return;
        }
        let question = questions[currentQuestionIndex];
        $('#question-number').text(` ${currentQuestionIndex + 1}`);
        $('#question').text(question.question);
        $('#choices').empty();

        let choices = [...question.choices];
        choices.sort(() => Math.random() - 0.5); // Shuffle choices

        choices.forEach(choice => {
            $('#choices').append(
                `<button class="choice-btn bg-blue-900 text-white p-2 rounded-md m-6 text-lg " data-answer="${choice}">${choice}</button>`
            );
        });
        $('.choice-btn').prop('disabled', false);

        resetTimer();
    }

    //reset timer
    function resetTimer() {
        if (timer) clearInterval(timer);
        countdown = 90;
        updateTimerDisplay(countdown);

        timer = setInterval(() => {
            countdown--;
            updateTimerDisplay(countdown);

            if (countdown <= 0) {
                clearInterval(timer);
                saveAnswer(null); // go to next question if time expires
            }
        }, 1000);
    }

    function updateTimerColor() {
        let timerElement = $('#timer');
        timerElement.removeClass("bg-yellow-600 bg-white bg-red-500");

        if (countdown < 10) {
            timerElement.addClass("bg-red-500");
        } else if (countdown < 30) {
            timerElement.addClass("bg-yellow-600");
        } else {
            timerElement.addClass("bg-white");
        }
    }

    //timer update in each second
    function updateTimerDisplay(seconds) {
        let minutes = Math.floor(seconds / 60);
        let secs = seconds % 60;
        let formattedTime = `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
        $('#timer').text(` ${formattedTime}`);
        updateTimerColor();
    }
    //choice selected
    $(document).on('click', '.choice-btn', function () {
        let selectedAnswer = $(this).data('answer');
        $('.choice-btn').prop('disabled', true);
        saveAnswer(selectedAnswer);
    });

    //save user selected answer and correct answer to an array for result calculation
    function saveAnswer(selectedAnswer) {
        clearInterval(timer);

        let question = questions[currentQuestionIndex];
        let questionId = questions[currentQuestionIndex].id;

        $.ajax({
            url: checkAnswerRoute,
            method: "POST",
            data: {
                question_id: questionId,
                selected_answer: selectedAnswer,
            },
            success: function (response) {
                if (response && response.correctAnswer) {

                    correctAnswer = response.correctAnswer;
                    userAnswers.push({
                        question: question.question,
                        selected: selectedAnswer,
                        correct: correctAnswer,
                        isCorrect: selectedAnswer === correctAnswer
                    });

                    currentQuestionIndex++;
                    showQuestion();
                }
                else {
                    showError('Something went wrong!!');
                }
            },
            error: function(xhr, status, error) {
                // console.log(xhr.responseJSON?.error);
                 
                 let errorMessage = xhr.responseJSON?.error || "Something went wrong!";
                 showError(errorMessage);
             }
        });


    }

    function showSummary() {
        $('#quiz-container').hide();
        let correctAnswers = userAnswers.filter(entry => entry.isCorrect).length;
        let totalQuestions = userAnswers.length;
        let scorePercentage = (correctAnswers / totalQuestions) * 100;

        let resultText = "";
        let resultClass = "";

        if (scorePercentage >= 60) {
            resultText = "Winner";
            resultClass = "text-green-500";
        } else if (scorePercentage >= 40) {
            resultText = "Better";
            resultClass = "text-yellow-500";
        } else {
            resultText = "Failed";
            resultClass = "text-red-500";
        }
        let summaryContainer = $('<div>', { id: "quiz-summary" });
        summaryContainer.append(`<h2 class='text-center text-lg text-white font-bold mb-4'>Quiz Result</h2>`);
        summaryContainer.append(`<h3 class='text-center ${resultClass} text-2xl font-bold'>${resultText}</h3>`);

        userAnswers.forEach((entry, index) => {
            let resultClass = entry.isCorrect ? "text-green-500" : "text-red-500";
            summaryContainer.append(`
                <div class='flex flex-col justify-between p-4 bg-blue-900 text-white rounded-md mb-4'>
                    <div><span class="text-white text-lg"><strong>${index + 1}:</strong> ${entry.question}</span></div>
                    <span class="grid grid-cols-3 gap-3">
                        <span class="${resultClass}">Your Answer: <b>${entry.selected || "No Answer"}</b></span> 
                        <span class="text-green-500">Correct Answer: <b>${entry.correct}</b></span>
                    </span>
                </div>
            `);
        });

        summaryContainer.append("<button class='reset text-center text-lg text-white p-2 rounded-md font-bold bg-blue-900'>Reset</button>");
        $('#quiz-container').after(summaryContainer);

    }

    $(document).on('click', '.reset', function () {
        $('#quiz-summary').remove();
        $('#quiz-container').hide();
        $('#category-container').show();
        // Reset pagination to page 1
        $('.pagination-dot').removeClass('text-black');
        $('.pagination-dot[data-page="1"]').addClass('text-black');

        // Fetch categories for page 1
        $.ajax({
            url: `${indexRoute}?page=1`,
            method: 'GET',
            success: function (response) {
                let newCategories = $(response).find('#categories').html();
                $('#categories').html(newCategories);
            }
        });

    });
});
