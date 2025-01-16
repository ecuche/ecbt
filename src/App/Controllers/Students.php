<?php
declare(strict_types=1);

namespace App\Controllers;

use Framework\Controller;
use Framework\Response;
use Framework\Helpers\Session;
use Framework\Helpers\Redirect;
use Framework\Helpers\CSRF;
use Framework\Helpers\CSV;
use Framework\Helpers\Media;
use App\Models\Student;

class Students extends Controller
{
    private $user;
    private $csrf_token = null;

    public function __construct(private Student $studentModel)
    {
        $this->user = $this->studentModel->findById($_SESSION['id'], 'user');
    }

    public function dashboard(): Response
    {
        return $this->view('students/dashboard', [
            'user' => $this->user,
            'success' => Session::flash('success')
        ]);
    }

    public function showAllResults(): Response
    {
        return $this->view('students/results-list', [
            'user' => $this->user,
            'results' => $this->studentModel->getUserResults($this->user->id)
        ]);
    }

    public function findTest(): Response
    {
        return $this->view('students/find-test', [
            'CSRF' => CSRF::generate(),
            'user' => $this->user,
        ]);
    }

    public function searchTest(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $post = [
            'code' => $this->request->post['code']
        ];
        $post = (object) $post;
        $post->code = strtoupper($post->code);
        $errors = $this->studentModel->validateCode($post);
        $paper = $this->studentModel->findByField('code', $post->code, 'paper');
        if (empty((array) $errors)) {
            return $this->redirect("test/{$paper->code}/search/result");
        } else {
            return $this->view('students/find-test', [
                'user' => $this->user,
                'CSRF' => CSRF::generate(),
                'paper' => $post->code,
                'errors' => $errors
            ]);
        }
    }

    public function searchResult($code): Response
    {
        if(!empty(Session::get('test_name'))) {
            $this->redirect("paper/{$code}/test/sheet");
        }
        $paper = $this->studentModel->findByField('code', $code, 'paper');
        $result = $this->studentModel->findByFields(['user_id' => $this->user->id, 'paper_id' => $paper->id], 'result');
        $instructor = $this->studentModel->findById($paper->user_id, 'user');
        return $this->view('students/search-result', [
            'user' => $this->user,
            'paper' => $paper,
            'instructor' => $instructor,
            'result' => $result,
            'CSRF' => CSRF::generate()
        ]);
    }

    public function showTestResult($code): Response
    {
        if(!empty(Session::get('test_name'))) {
            $this->redirect("paper/{$code}/test/sheet");
        }
        $paper = $this->studentModel->findByField('code', $code, 'paper');
        $result = $this->studentModel->resultAuth($this->user->id, $paper->id);
        $instructor = $this->studentModel->findById($paper->user_id, 'user');
        return $this->view('students/show-test-result', [
            'user' => $this->user,
            'paper' => $paper,
            'instructor' => $instructor,
            'result' => $result,
            'CSRF' => CSRF::generate(),
            'score' => $this->studentModel->grade($result->score, $result->poll)
        ]);
    }

    public function startTest($code): Response
    {
        if(!empty(Session::get('test_name'))) {
            $this->redirect("paper/{$code}/test/sheet");
        }
        CSRF::check($this->request->post['csrf_token']);
        $paper = $this->studentModel->paperAuth($code);
        $csv = new CSV($paper->code, 'papers');
        $questions = $csv->randRows($paper->poll);
        $this->studentModel->createTestSheet($questions, $paper->code);
        Session::set([
            'paper' => $paper,
            'end_time' => $paper->time * 60 + time(),
        ]);
        return $this->redirect("paper/{$code}/test/sheet");
    }

    public function testSheet($code): Response
    {
        $question_id = $this->request->post['q_id'] ?? 1;
        $rem_time = $this->studentModel->remTime();
        if (!$rem_time) {
            $this->csrf_token = Session::get('csrf_token');
            return $this->submitTest($code);
        }
        $paper = $this->studentModel->testAuth();
        $instructor = $this->studentModel->findById($paper->user_id, 'user');
        $csv = new CSV(Session::get('test_name'), 'results');
        $question = $csv->getRow($question_id);
        $questions = $csv->getRow();
        $question_count = count((array)$questions);
        $count_answered = round(100 - ($csv->countNullFields('chosen') / $question_count) *100, 2);
       
        return $this->view('students/test-sheet', [
            'paper' => $paper,
            'instructor' => $instructor,
            'rem_time' => $rem_time,
            'page' => 'test_sheet',
            'question' => $question,
            'questions' =>  (object) $questions,
            'count_answered' => $count_answered,
            'image' => $question->image ? Media::questionImage( $question->image ) : null,
            'CSRF' => CSRF::generate()
        ]);
    }

    public function prevNext(): never
    {
        $rem_time = $this->studentModel->remTime();
        $next = $this->request->post['next'] ?? '';
        $question_id = $this->request->post['q_id'] ?? '';
        $paper = Session::get('paper');
        $instructor = $this->studentModel->findById($paper->user_id, 'user');
        $test_name = Session::get('test_name');
        $csv = new CSV($test_name, 'results');
        $questions = $csv->getRow();
        $question_count = count((array)$questions);
        $count_answered = round(100 - ($csv->countNullFields('chosen') / $question_count) *100, 2);
        
        if ($next === 'next' && $question_id < count((array) $csv->getRow())) {
            $question_id++;
        } elseif ($next === 'previous' && $question_id > 1) {
            $question_id--;
        }elseif($question_id < 0 || $question_id > $paper->poll || !is_numeric($question_id)) {
            $question_id = 1;
        }
        $question = $csv->getRow($question_id);

        echo $this->raw('students/ajax/test-sheet', [
            'paper' => $paper,
            'instructor' => $instructor,
            'rem_time' => $rem_time,
            'question' => $question,
            'questions' =>  (object) $questions,
            'count_answered' => $count_answered,
            'image' => $question->image ? Media::questionImage( $question->image ) : null,
            'CSRF' => CSRF::generate()
        ]);
        exit;
    }

    public function submitOptionSelected(): never
    {
        $ans_code = $this->request->post['ans_code'];
        $question_id = $this->request->post['q_id'];
        $test_name = Session::get('test_name');
        $csv = new CSV($test_name, 'results');
        $question = $csv->getRow($question_id);
        $options = json_decode($question->options);
        foreach ($options as $option) {
            if ($option->hash == $ans_code) {
                $chosen = $option;
                break;
            }
        }
        $csv->updateRows(['chosen'=>$chosen->answer, 'correct'=>$chosen->correct, 'hash'=>$chosen->hash], $question_id);
        exit;
    }

    public function testPreview($code): Response
    {
        $rem_time = $this->studentModel->remTime();
        $paper = Session::get('paper');
        $test_name = Session::get('test_name');
        $csv = new CSV($test_name, 'results');
        $questions = $csv->getRow();
        $question_count = count((array)$questions);
        $count_answered = round(100 - ($csv->countNullFields('chosen') / $question_count) *100, 2);
        return $this->view('students/test-preview', [
            'paper' => $paper,
            'rem_time' => $rem_time,
            'questions' =>  (object) $questions,
            'page' => 'test_sheet',
            'count_answered' => $count_answered,
        ]);
    }

    public function submitTest($code): Response
    {
        // $this->csrf_token ??= $this->request->post['csrf_token'];
        // CSRF::check($this->csrf_token);
        $paper = $this->studentModel->paperAuth($code);

        $result = $this->studentModel->findByFields(['user_id'=>$this->user->id, 'paper_id'=> $paper->id], 'result');
        if(empty($result)){
            $test_name = Session::get('test_name');
            $csv = new CSV($test_name, 'results');
            $poll = $csv->countAllRow();
            $score = $csv->countNotNullFields('correct');
            $grade = $this->studentModel->grade($score, $poll);
            $this->studentModel->insert([
                'user_id'=> $this->user->id,
                'paper_id'=> $paper->id,
                'poll'=> $grade->total,
                'score'=> $grade->score,
                'percent'=> $grade->percent,
                'start_time'=> Session::get('start_time'),
                'grade'=> $grade->grade,
                'remark'=> $grade->remark,
                'csv'=> Session::get('test_name'),
            ], 'result');

            Session::delete(['test_name','start_time','paper','end_time']);
        }
        $result = $this->studentModel->resultAuth($this->user->id, $paper->id);
        return $this->redirect("paper/{$code}/result/show");
    }
}