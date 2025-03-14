<?php
declare(strict_types= 1);

namespace App\Controllers;
use Framework\Controller;
use Framework\Response;
use Framework\Exceptions\PageNotFoundException;
use Framework\Helpers\Session;
use Framework\Helpers\Redirect;
use Framework\Helpers\CSRF;
use Framework\Helpers\CSV;
use Framework\Helpers\Media;
use Framework\Helpers\Functions;
use App\Models\Instructor;

class Instructors extends Controller
{
    private $user;
    protected int $page = 1;
    protected int $limit = 1;

    public function __construct(private Instructor $instructorsModel){
        $this->user = $this->instructorsModel->findById($_SESSION['id'], 'user');
        if ($this->user->role !== 'instructor') {
            Session::set(['warning' =>  'You are not authorized to view that page']);
            Redirect::to('');
        }
        $code = Session::get('code');
        $rem_time = Session::get('rem_time');
        if(!empty($code) && !empty($rem_time)) {
            if($rem_time > 0){
                $this->redirect("paper/{$code}/test/sheet");
            }
        }
    }

    public function dashboard(): Response
    {
        return $this->view('instructors/dashboard', [
            'user' => $this->user,
            'alert' => Session::flash(['success'])
        ]);
    }

    public function newTest(): Response
    {
        return $this->view('instructors/new-test', [
            'user' => $this->user,
            'CSRF' => CSRF::generate(),
        ]); 
    }

    protected function offset(): int
    {
        return $this->page * $this->limit - $this->limit;
    }

    public function papersList(): Response
    {
        $offset = $this->offset();
        $papers = $this->instructorsModel->findAllByFieldAndLimit('user_id', $this->user->id, $this->limit, $offset, 'paper');
        $count = $this->instructorsModel->rowCountByField('user_id',$this->user->id, 'paper');
        $i = $offset + 1;
        $total_pages = ceil($count / $this->limit);
        if($this->page > $total_pages){
            return $this->redirect("/instructor/papers/page/{$total_pages}"); 
        }
        return $this->view('instructors/papers', [
            'user'=> $this->user,
            'CSRF' => CSRF::generate(),
            'papers'=> $papers,
            'count' => $count,
            'page' => $this->page,
            'total_pages' => $total_pages,
            'i' => $i
        ]);
    }

    public function papersListPage($page = 1): Response
    {
        $this->page = (int) $page;
        return $this->papersList();
    }

    public function insertNewTest(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $paper = [
            'name' => ucwords($this->request->post['name']),
            'time' => $this->request->post['time'],
            'poll' => $this->request->post['poll'],
            'description'=> $this->request->post['description'],
            'instruction'=> $this->request->post['instruction'],
        ];
        $paper['pass_mark'] =  !empty($this->request->post['pass_mark']) ? $this->request->post['pass_mark'] :  50;
        $settings['view_result'] =  isset($this->request->post['view_result']) ? 1 : 0;
        $settings['view_answers'] =  isset($this->request->post['view_answers']) ? 1 : 0;
        $settings['view_result'] = empty($settings['view_answers']) ? $settings['view_result'] : 1;
        $paper = (object)$paper;
        $paper->settings = json_encode($settings);
        $this->instructorsModel->validateNewTest($paper);
        $errors = (object) $this->instructorsModel->getErrors();
        if(empty((array) $errors)) {
            $uid = $this->instructorsModel->paperCode();
            $paper->user_id = $this->user->id;
            $paper->code = $uid;
            if($this->instructorsModel->insert($paper, 'paper')){
                Session::set('success','paper created successful. Add questions to the paper');
                return $this->redirect("instructor/paper/{$paper->code}/create/questions");
            }else{
                throw new PageNotFoundException("Paper creation failed");
            }
        }else{
            return $this->view('instructors/new-test', [
                'user'=> $this->user,
                'CSRF' => CSRF::generate(),
                'paper'=> $paper,
                'settings' => json_decode($paper->settings),
                'errors' => $errors
            ]);
        }
    }

    public function editPaper($code): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        return $this->view('instructors/edit-test', [
            'user'=> $this->user,
            'alert' => Session::flash(['warning', 'danger', 'success']),
            'CSRF' => CSRF::generate(),
            'paper'=> $paper,
            'settings' => json_decode($paper->settings),
        ]);
    }

    public function updatePaper($code): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $db_paper = $this->instructorsModel->instructorAuth( $code);
        $paper = [
            'name' => $this->request->post['name'] ?? '',
            'time' => $this->request->post['time'],
            'poll' => $this->request->post['poll'],
            'description'=> $this->request->post['description'],
            'instruction'=> $this->request->post['instruction'],
        ];
        $paper['pass_mark'] =  !empty($this->request->post['pass_mark']) ? $this->request->post['pass_mark'] :  50;
        $settings['view_result'] =  isset($this->request->post['view_result']) ? 1 : 0;
        $settings['view_answers'] =  isset($this->request->post['view_answers']) ? 1 : 0;
        $settings['view_result'] = empty($settings['view_answers']) ? $settings['view_result'] : 1;
        $paper = (object)$paper;
        $paper->settings = json_encode($settings);
        $errors = $this->instructorsModel->validateUpdateTest($paper);;

        if(empty((array) $errors)) {
            unset($paper->name);
            $paper->user_id = $this->user->id;
            if($this->instructorsModel->updateRowById($db_paper->id, $paper, 'paper')) {
                Session::set('success','Test Updated successful.');
                return $this->redirect("instructor/paper/{$db_paper->code}/edit");
            }else{
                throw new PageNotFoundException("Paper Update failed");
            }
        }else{
            $paper->code = $db_paper->code;
            $paper->name = $db_paper->name;
            return $this->view('instructors/edit-test', [
                'user'=> $this->user,
                'CSRF' => CSRF::generate(),
                'paper'=> $paper,
                'settings' => json_decode($paper->settings),
                'errors' => $errors
            ]);
        }
    }

    public function QuestionsList($code): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $csv = new CSV($code, 'papers');
        if (empty($csv) || $csv->countAllRow() < $paper->poll) {
            $this->instructorsModel->updateRowById($paper->id, ["status"=> 0], "paper");
        }
        return $this->view('instructors/questions-list', [
            'user'=> $this->user,
            'CSRF' => CSRF::generate(),
            'paper'=> $paper,
            'csv' => (object) $csv->getRow(),
            'paper_code'=> $code
        ]);
    }

    public function createQuestions($code): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        return $this->view('instructors/create-questions', [
            'user'=> $this->user,
            'CSRF' => CSRF::generate(),
            'paper'=> $paper,
            'alert'=> Session::flash(['success', 'danger', 'warning']),
        ]);
    }

    public function addQuestion($code): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $paper = $this->instructorsModel->instructorAuth( $code);
        $post = [
            'question' => $this->request->post['question'],
            'options' => $this->request->post['options'],
            'corrects' => $this->request->post['corrects'],
        ];
        $answers = [];
        foreach ($post['options'] as $key => $option) {
            $ans				= [];
            $ans['answer']		= $option;
            $ans['correct']		= $post['corrects'][$key];
            array_push( $answers,  (object)$ans);
        }

        $errors = $this->instructorsModel->validateQuestion($post);
        $csv_path = "{$_ENV['CSV_PATH']}/papers";
        if(empty((array) $errors)) {
            $image = '';
            if ($_FILES['image']['name']) {	
                $image_type = getimagesize($_FILES["image"]["tmp_name"]);
                $image_size = $_FILES['image']['size'];
                if($image_type !== false && $image_size < 500 * KB ){
                    $location = "{$csv_path}/images";
                    $image_tmp = $_FILES['image']['tmp_name'];
                    $image = uniqid("{$code}_", true).".jpg";
                    Functions::createFolder($location);
                    move_uploaded_file($image_tmp, "{$location}/{$image}");
                }else{
                    $errors->image = "Kindly provide image file less than 500KB";
                    goto else_part;
                }
            }
            $post['image'] = $image;
            $this->instructorsModel->insertNewQuestion($post, $paper);
            Session::set(name: 'success', value: 'Question added successfully');
            return $this->redirect("instructor/paper/{$code}/create/questions");
        }else{
            else_part:
            return $this->view('instructors/create-questions', [
                'user'=> $this->user,
                'CSRF' => CSRF::generate(),
                'paper'=> $paper,
                'errors' => $errors,
                'question' => (object)$post,
                'answers' => (object)$answers
            ]);
        }
    }

    public function editQuestion($code, $id): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $csv = new CSV($code, 'papers');
        $question = (object) $csv->getRow((int)$id);
        $image = !empty($question->image) ? Media::questionImage( $question->image ) : null;
        
        return $this->view('instructors/edit-question', [
            'user'=> $this->user,
            'paper'=> $paper,
            'image'=> $image,
            'question' => $question,
            'answers' => json_decode($question->answers),
            'alert' => Session::flash(['success']),
            'CSRF' => CSRF::generate()
        ]);
    }

    public function updateQuestion($code, $id): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $paper = $this->instructorsModel->instructorAuth( $code);
        $csv = new CSV($code,'papers');
        $question = $csv->getRow((int)$id); 
        $csv->end();
        $post = [
            'question' => $this->request->post['question'],
            'options' => $this->request->post['options'],
            'corrects' => $this->request->post['corrects'],
            'id' => $id,
        ];
        $answers = [];
        foreach ($post['options'] as $key => $option) {
            $ans				= [];
            $ans['answer']		= $option;
            $ans['correct']		= $post['corrects'][$key];
            array_push( $answers,  (object)$ans);
        }

        $errors = $this->instructorsModel->validateQuestion($post);
        $csv_path = "{$_ENV['CSV_PATH']}/papers";
        if(empty((array) $errors)) {
            $image = '';
            if ($_FILES['image']['name']) {
                $old_image_path = "{$csv_path}/images/{$question->image}";
                if(!empty($question->image) && file_exists($old_image_path)){
                    unlink( $old_image_path );
                }
                $image_type = getimagesize($_FILES["image"]["tmp_name"]);
                $image_size = $_FILES['image']['size'];
                if($image_type !== false && $image_size < 500 * KB ){
                    $location = "{$csv_path}/images";
                    $image_tmp = $_FILES['image']['tmp_name'];
                    $image = uniqid("{$code}_", true).".jpg";
                    if (!file_exists($location)) {
                        mkdir($location, 0777, true);
                    }
                    move_uploaded_file($image_tmp, "{$location}/{$image}");
                }else{
                    $errors->image = "Kindly provide image file less than 500KB";
                    goto else_part;
                }	
            }
            $post['image'] = $_FILES['image']['name'] ? $image : $question->image;
            $post['id'] = $id;
            $this->instructorsModel->updateQuestion($post, $paper);
            Session::set('success', 'Question Updated successfully');
            return $this->redirect("instructor/paper/{$code}/{$id}/edit/question");
        }else{
            else_part:
            $errors->image ??= "Image was not added";
            return $this->view('instructors/edit-question', [
                'user'=> $this->user,
                'CSRF' => CSRF::generate(),
                'paper'=> $paper,
                'answers' => (object) $answers,
                'errors' => $errors,
                'question' => (object) $post
            ]);
        }
    }
 
    public function deleteQuestionImage($code, $id): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $csv = new CSV($code, 'papers');
        $question = (object) $csv->getRow((int)$id);
        $image = "{$_ENV['CSV_PATH']}/papers/images/$question->image";
        if(file_exists($image)){
            unlink($image);
        }
        $csv->updateRows( ["image"=> ''], (int)$id);
        Session::set('success','Image deleted successfully');
        return $this->redirect("instructor/paper/{$code}/{$id}/edit/question");
    }

    public function deleteQuestion($code, $id): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $csv = new CSV($code, 'papers');
        $csv->deleteRow($id);
        return $this->redirect("instructor/paper/{$code}/list");
    }

    public function changePaperStatus($code, $status): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $csv = new CSV($paper->code,"papers");
        if (empty($csv) || $csv->countAllRow() < $paper->poll) {
            $this->instructorsModel->updateRowById($paper->id, ["status"=> 0], "paper");
            Session::set('warning', 'Total questions added is not upto the total questions specified');
            return $this->redirect("instructor/paper/{$code}/edit");
        }
        switch ($status) {
            case 'activate': $val = 1;
                break;
            case 'deactivate' : $val = 0;
                break;
            default: $val = 0;
        }
        $this->instructorsModel->updateRowById($paper->id, ["status"=> $val], "paper");
        Session::set('success', 'Status change Successfully');
        return $this->redirect("instructor/paper/{$code}/edit");
    }

    public function deletePaper($code): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $this->instructorsModel->deleteRow($paper->id, "paper");
        return $this->redirect("instructor/papers");
    }

    public function myStudents(): Response
    {
        $students = $this->instructorsModel->getAllMyStudents($this->user->id);
        return $this->view('instructors/my-students', [
            'user'=> $this->user,
            'students' => $students,
            'alert' => Session::flash(['warning', 'danger', 'success']),
            'CSRF' => CSRF::generate()
        ]);
    }

    public function showParticipants($code): Response 
    {
        $offset = $this->offset();
        $paper = $this->instructorsModel->instructorAuth( $code);
        if(!empty($_POST['date'])){
            $students = $this->instructorsModel->getAllTestStudentByDate($paper->id, $_POST['date'], $this->limit, $offset);
        }else{
            $students = $this->instructorsModel->getAllTestStudentByDate($paper->id, null, $this->limit, $offset);
        }
        $count = $this->instructorsModel->rowCountByField('paper_id',$paper->id, 'result');
        $i = $offset + 1;
        $total_pages = ceil($count / $this->limit);
        if($this->page > $total_pages){
            return $this->redirect("/instructor/paper/{$code}/participants/show/page/{$total_pages}"); 
        }
        return $this->view('instructors/show-participants', [
            'user'=> $this->user,
            'students'=> $students,
            'page'=>'date_format',
            'paper'=> $paper,
            'date' => $_POST['date'] ?? null,  
            'count' => $count,
            'current_page' => $this->page,
            'total_pages' => $total_pages,
            'i' => $i 
        ]);
    }

    public function showParticipantsPage($code, $page): Response
    {
        $this->page = (int) $page;
        return $this->showParticipants($code);
    }

    public function studentTests($email): Response
    {
        $student = $this->instructorsModel->findByField('email', $email, 'user');
        if(empty($student)){
            Session::set('warning','student does not exist');
            return $this->redirect('instructor/my-students');
        }
        $results = $this->instructorsModel->myStudentResult($student->id);
        return $this->view('instructors/student-tests', [
            'user'=> $this->user,
            'student'=> $student,
            'results'=> $results,
        ]);
    }

    public function searchMyStudent(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $email =  $this->request->post['email'];
        $student = $this->instructorsModel->findByField('email', $email, 'user');
        if(empty($student)){
            Session::set('warning','student does not exist');
            return $this->redirect('instructor/my-students');
        }
        $results = $this->instructorsModel->myStudentResult($student->id);
        if(empty($results)){
            $students = $this->instructorsModel->getAllMyStudents($this->user->id);
            return $this->view('instructors/my-students', [
                'user'=> $this->user,
                'students' => $students,
                'email_errors' => 'No result found',
                'CSRF' => CSRF::generate()
            ]);
        }
        return $this->studentTests($email);
    }

}