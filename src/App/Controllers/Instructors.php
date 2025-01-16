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
    public function __construct(private Instructor $instructorsModel){
        $this->user = $this->instructorsModel->findById($_SESSION['id'], 'user');
        if ($this->user->role !== 'instructor') {
            Session::set(['warning' =>  'You are not authorized to view that page']);
            Redirect::to('');
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

    public function papersList(): Response
    {
        $papers = $this->instructorsModel->findAllByField('user_id', $this->user->id, 'paper');
        return $this->view('instructors/papers', [
            'user'=> $this->user,
            'CSRF' => CSRF::generate(),
            'papers'=> $papers,
        ]);
    }

    public function insertNewTest(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $paper = [
            'name' => $this->request->post['name'],
            'time' => $this->request->post['time'],
            'poll' => $this->request->post['poll'],
            'pass_mark' => $this->request->post['pass_mark'],
            'description'=> $this->request->post['description'],
            'instruction'=> $this->request->post['instruction'],
        ];
        $paper = (object)$paper;
        $this->instructorsModel->validateNewTest($paper);
        $errors = (object) $this->instructorsModel->getErrors();

        if(empty((array) $errors)) {
            $uid = $this->instructorsModel->paperCode();
            $paper->user_id = $this->user->id;
            $paper->csv = preg_replace('/[^A-Za-z0-9]/', '', $paper->name)."_{$uid}";
            $paper->code = $uid;
            if($this->instructorsModel->insert($paper, 'paper')){
                Session::set('success','paper created successful. Add questions to the paper');
                return $this->redirect("instructor/paper/{$paper->code}/add-questions");
            }else{
                throw new PageNotFoundException("Paper creation failed");
            }
        }else{
            return $this->view('instructors/new-test', [
                'user'=> $this->user,
                'CSRF' => CSRF::generate(),
                'paper'=> $paper,
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
            'pass_mark' => $this->request->post['pass_mark'],
            'description'=> $this->request->post['description'],
            'instruction'=> $this->request->post['instruction'],
        ];
        $paper = (object)$paper;
        $errors = $this->instructorsModel->validateUpdateTest($paper);;

        if(empty((array) $errors)) {
            unset($paper->name);
            $paper->user_id = $this->user->id;
            if($this->instructorsModel->updateRow($db_paper->id, $paper, 'paper')) {
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
                'errors' => $errors
            ]);
        }
    }

    public function QuestionsList($code): Response
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $csv = new CSV($code, 'papers');
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
                'question' => $post
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
        $post = [
            'question' => $this->request->post['question'],
            'options' => $this->request->post['options'],
            'corrects' => $this->request->post['corrects'],
        ];

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
            $post['image'] = $image;
            $post['id'] = $id;
            $this->instructorsModel->updateQuestion($post, $paper);
            Session::set('success', 'Question Updated successfully');
            return $this->redirect("instructor/paper/{$code}/{$id}/edit/question");
        }else{
            else_part:
            return $this->view('instructors/create-questions', [
                'user'=> $this->user,
                'CSRF' => CSRF::generate(),
                'paper'=> $paper,
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
        $csv = new CSV($paper->csv,"papers");
        if (empty($csv) || $csv->countAllRow() < $paper->poll) {
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
        $this->instructorsModel->updateRow($paper->id, ["status"=> $val], "paper");
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
            'students' => $students
        ]);
    }

    public function showParticipants($code): Response 
    {
        $paper = $this->instructorsModel->instructorAuth( $code);
        $students = $this->instructorsModel->getAllTestStudents($paper->id);
        return $this->view('instructors/show-participants', [
            'user'=> $this->user,
            'students'=> $students,
            'paper'=> $paper,
        ]);
    }

}